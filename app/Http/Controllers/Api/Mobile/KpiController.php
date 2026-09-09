<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\KpiEmployee;
use App\Models\KpiPeriod;
use App\Models\KpiIndicator;
use App\Models\KpiIndicatorDetail;
use App\Models\Karyawan;
use App\Models\Userkaryawan;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    public function myScore(Request $request)
    {
        $user = $request->user();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();
        
        if (!$user_karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan data Karyawan.'
            ], 404);
        }
        
        $nik = $user_karyawan->nik;
        $karyawan = Karyawan::where('nik', $nik)->first();
        
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Karyawan tidak ditemukan untuk NIK: ' . $nik
            ], 404);
        }
        
        // Get Active Period
        $period = KpiPeriod::where('is_active', 1)->first();
        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada Periode KPI yang Aktif'
            ], 404);
        }

        // Get or calculate kpi employee targets
        $kpi_employee = KpiEmployee::with(['details.indicator', 'karyawan', 'period'])
                                    ->where('nik', $nik)
                                    ->where('kpi_period_id', $period->id)
                                    ->first();

        if ($kpi_employee && $kpi_employee->details->isEmpty()) {
            $kpi_employee->delete();
            $kpi_employee = null;
        }

        if ($kpi_employee) {
            // Recalculate auto metrics
            foreach ($kpi_employee->details as $detail) {
                if ($detail->indicator && $detail->indicator->mode == 'auto' && !empty($detail->indicator->metric_source)) {
                    $realisasi = $this->calculateAutomatedRealization($kpi_employee, $detail->indicator->metric_source);
                    
                    if ($detail->realisasi != $realisasi) {
                        $detail->update(['realisasi' => $realisasi]);
                        $detail->realisasi = $realisasi;
                    }
                    
                    $this->calculateScore($detail, $realisasi);
                }
            }
            $kpi_employee->refresh();
        }

        // Prepare response data
        if ($kpi_employee) {
            $details = $kpi_employee->details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'indicator_name' => $detail->indicator ? $detail->indicator->nama_indikator : 'Indikator Tidak Diketahui',
                    'indicator_description' => $detail->indicator ? $detail->indicator->deskripsi : '',
                    'target' => (float)$detail->target,
                    'realisasi' => (float)$detail->realisasi,
                    'bobot' => (float)$detail->bobot,
                    'skor' => (float)$detail->skor,
                    'jenis_target' => $detail->indicator ? $detail->indicator->jenis_target : 'max',
                    'mode' => $detail->indicator ? $detail->indicator->mode : 'manual',
                ];
            });

            $total_score = $kpi_employee->details->sum('skor');
            $total_bobot = $kpi_employee->details->sum('bobot');

            return response()->json([
                'success' => true,
                'data' => [
                    'has_kpi' => true,
                    'kpi_id' => $kpi_employee->id,
                    'period' => [
                        'id' => $period->id,
                        'name' => $period->nama_periode,
                        'start_date' => $period->start_date,
                        'end_date' => $period->end_date,
                    ],
                    'status' => $kpi_employee->status,
                    'total_score' => (float)round($total_score, 2),
                    'total_bobot' => (float)round($total_bobot, 2),
                    'details' => $details,
                ]
            ]);
        } else {
            // Get Indicators specific to Jabatan AND Departemen
            $kpi_indicator_header = KpiIndicator::where('kode_jabatan', $karyawan->kode_jabatan)
                                                ->where('kode_dept', $karyawan->kode_dept)
                                                ->first();
            
            $indicators = collect([]);
            if ($kpi_indicator_header) {
                $indicators = KpiIndicatorDetail::where('kpi_indicator_id', $kpi_indicator_header->id)->get();
            }

            // Get Global Indicators
            $global_indicator_header = KpiIndicator::whereNull('kode_jabatan')
                                                    ->whereNull('kode_dept')
                                                    ->first();
            
            if ($global_indicator_header) {
                $global_details = KpiIndicatorDetail::where('kpi_indicator_id', $global_indicator_header->id)->get();
                $indicators = $indicators->merge($global_details);
            }

            $formatted_indicators = $indicators->map(function ($detail) {
                return [
                    'indicator_name' => $detail->nama_indikator,
                    'indicator_description' => $detail->deskripsi ?? '',
                    'target' => (float)$detail->target,
                    'bobot' => (float)$detail->bobot,
                    'jenis_target' => $detail->jenis_target,
                    'mode' => $detail->mode,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'has_kpi' => false,
                    'period' => [
                        'id' => $period->id,
                        'name' => $period->nama_periode,
                        'start_date' => $period->start_date,
                        'end_date' => $period->end_date,
                    ],
                    'status' => 'target_not_set',
                    'total_score' => 0.0,
                    'total_bobot' => (float)$indicators->sum('bobot'),
                    'details' => $formatted_indicators,
                ]
            ]);
        }
    }

    public function inputRealisasi(Request $request)
    {
        $user = $request->user();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$user_karyawan) {
            return response()->json(['success' => false, 'message' => 'Akun Anda tidak terhubung dengan data Karyawan.'], 404);
        }
        $nik = $user_karyawan->nik;

        $request->validate([
            'kpi_id' => 'required|integer',
            'realisasi' => 'required|array',
        ]);

        $kpi_employee = KpiEmployee::where('id', $request->kpi_id)->where('nik', $nik)->first();
        if (!$kpi_employee) {
            return response()->json(['success' => false, 'message' => 'Data KPI tidak ditemukan.'], 404);
        }

        if ($kpi_employee->status == 'approved') {
            return response()->json(['success' => false, 'message' => 'KPI yang sudah disetujui tidak dapat diubah.'], 400);
        }

        DB::beginTransaction();
        try {
            $total_score = 0;

            // Map request values by detail_id
            $inputRealisasi = [];
            foreach ($request->realisasi as $item) {
                if (isset($item['detail_id'])) {
                    $inputRealisasi[$item['detail_id']] = $item['value'];
                }
            }

            foreach ($kpi_employee->details as $detail) {
                $indicator = $detail->indicator;
                if (!$indicator) continue;

                if ($indicator->mode == 'auto' && !empty($indicator->metric_source)) {
                    $realisasi = $this->calculateAutomatedRealization($kpi_employee, $indicator->metric_source);
                } else {
                    $realisasi = isset($inputRealisasi[$detail->id]) ? (float)$inputRealisasi[$detail->id] : (float)$detail->realisasi;
                }

                $score = $this->calculateScore($detail, $realisasi);
                $total_score += $score;
            }

            $grade = '';
            if ($total_score >= 90) $grade = 'A';
            elseif ($total_score >= 80) $grade = 'B';
            elseif ($total_score >= 70) $grade = 'C';
            elseif ($total_score >= 60) $grade = 'D';
            else $grade = 'E';

            $kpi_employee->update([
                'total_nilai' => $total_score,
                'grade' => $grade,
                'status' => 'submitted'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Realisasi KPI berhasil disimpan',
                'data' => [
                    'total_score' => (float)round($total_score, 2),
                    'grade' => $grade,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan realisasi: ' . $e->getMessage()], 500);
        }
    }

    private function calculateAutomatedRealization($kpi_employee, $metric_source) {
        $nik = $kpi_employee->nik;
        $start = $kpi_employee->period->start_date;
        $end = $kpi_employee->period->end_date;
        
        switch ($metric_source) {
            case 'attendance_sakit':
                return Presensi::where('nik', $nik)
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('status', 's')
                    ->count();
            case 'attendance_izin':
                return Presensi::where('nik', $nik)
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('status', 'i')
                    ->count();
            case 'attendance_alpa':
                return Presensi::where('nik', $nik)
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('status', 'a')
                    ->count();
            case 'attendance_cuti':
                return Presensi::where('nik', $nik)
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('status', 'c')
                    ->count();
            case 'attendance_hadir':
                return Presensi::where('nik', $nik)
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('status', 'h')
                    ->count();
            case 'attendance_terlambat':
                $presensi = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                    ->where('nik', $nik)
                    ->whereBetween('tanggal', [$start, $end])
                    ->where('status', 'h')
                    ->select('presensi.*', 'presensi_jamkerja.jam_masuk')
                    ->get();
                
                $total_late_days = 0;
                foreach ($presensi as $p) {
                    $jam_masuk = $p->tanggal . ' ' . $p->jam_masuk;
                    $terlambat = hitungjamterlambat($p->jam_in, $jam_masuk);
                    
                    if ($terlambat && isset($terlambat['jamterlambat'])) {
                        $late_minutes = ($terlambat['jamterlambat'] * 60) + $terlambat['menitterlambat'];
                        if ($late_minutes > 0) {
                            $total_late_days++;
                        }
                    }
                }
                return $total_late_days;
            default:
                return 0;
        }
    }

    private function calculateScore($detail, $realisasi) {
        $indicator = $detail->indicator;
        if (!$indicator) return 0;
        
        $score = 0;
        
        if ($indicator->jenis_target == 'max') {
            if ($detail->target > 0) {
                $score = ($realisasi / $detail->target) * $detail->bobot;
            }
        } else {
            if ($realisasi == 0) {
                $score = $detail->bobot;
            } else {
                $score = ($detail->target / $realisasi) * $detail->bobot;
            }
        }

        if ($score > $detail->bobot) {
            $score = $detail->bobot;
        }
        
        $detail->update([
            'realisasi' => $realisasi,
            'skor' => $score
        ]);
         
        return $score;
    }
}
