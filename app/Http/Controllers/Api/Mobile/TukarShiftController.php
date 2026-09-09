<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AjuanJadwal;
use App\Models\Jamkerja;
use App\Models\Karyawan;
use App\Models\Userkaryawan;
use App\Models\Setjamkerjabydate;
use App\Models\GrupDetail;
use App\Models\GrupJamkerjaBydate;
use App\Models\Setjamkerjabyday;
use App\Models\Detailsetjamkerjabydept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TukarShiftController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userkaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Profil data karyawan tidak ditemukan'
            ], 404);
        }
        $nik = $userkaryawan->nik;

        $requests = AjuanJadwal::with(['jamKerjaAwal', 'jamKerjaTujuan'])
            ->where('nik', $nik)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal' => $item->tanggal,
                    'kode_jam_kerja_awal' => $item->kode_jam_kerja_awal,
                    'nama_jam_kerja_awal' => $item->jamKerjaAwal ? $item->jamKerjaAwal->nama_jam_kerja : 'OFF / Libur',
                    'jam_masuk_awal' => $item->jamKerjaAwal ? date('H:i', strtotime($item->jamKerjaAwal->jam_masuk)) : null,
                    'jam_pulang_awal' => $item->jamKerjaAwal ? date('H:i', strtotime($item->jamKerjaAwal->jam_pulang)) : null,
                    'kode_jam_kerja_tujuan' => $item->kode_jam_kerja_tujuan,
                    'nama_jam_kerja_tujuan' => $item->jamKerjaTujuan ? $item->jamKerjaTujuan->nama_jam_kerja : 'OFF / Libur',
                    'jam_masuk_tujuan' => $item->jamKerjaTujuan ? date('H:i', strtotime($item->jamKerjaTujuan->jam_masuk)) : null,
                    'jam_pulang_tujuan' => $item->jamKerjaTujuan ? date('H:i', strtotime($item->jamKerjaTujuan->jam_pulang)) : null,
                    'keterangan' => $item->keterangan,
                    'status' => $item->status,
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            });

        $shifts = Jamkerja::orderBy('nama_jam_kerja')->get()->map(function ($shift) {
            return [
                'kode_jam_kerja' => $shift->kode_jam_kerja,
                'nama_jam_kerja' => $shift->nama_jam_kerja,
                'jam_masuk' => $shift->jam_masuk ? date('H:i', strtotime($shift->jam_masuk)) : null,
                'jam_pulang' => $shift->jam_pulang ? date('H:i', strtotime($shift->jam_pulang)) : null,
                'color' => $shift->color,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'requests' => $requests,
                'shifts' => $shifts,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userkaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Profil data karyawan tidak ditemukan'
            ], 404);
        }
        $nik = $userkaryawan->nik;

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'kode_jam_kerja_tujuan' => 'required|string',
            'keterangan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.'
            ], 404);
        }

        $kode_cabang = $karyawan->kode_cabang;
        $kode_dept = $karyawan->kode_dept;
        $tanggal = $request->tanggal;
        $namahari = getnamaHari(date('D', strtotime($tanggal)));

        // Calculate Original Schedule
        $kode_jam_kerja_awal = null;

        // 1. Cek Jam Kerja By Date
        $jamkerja_by_date = Setjamkerjabydate::where('nik', $nik)->where('tanggal', $tanggal)->first();
        if ($jamkerja_by_date) {
            $kode_jam_kerja_awal = $jamkerja_by_date->kode_jam_kerja;
        }

        // 2. Cek Jam Kerja Group
        if ($kode_jam_kerja_awal == null) {
            $cek_group = GrupDetail::where('nik', $nik)->first();
            if ($cek_group) {
                $jamkerja_group = GrupJamkerjaBydate::where('kode_grup', $cek_group->kode_grup)
                    ->where('tanggal', $tanggal)
                    ->first();
                if ($jamkerja_group) {
                    $kode_jam_kerja_awal = $jamkerja_group->kode_jam_kerja;
                }
            }
        }

        // 3. Cek Jam Kerja Harian (Per Orang)
        if ($kode_jam_kerja_awal == null) {
            $jamkerja_harian = Setjamkerjabyday::where('nik', $nik)->where('hari', $namahari)->first();
            if ($jamkerja_harian) {
                $kode_jam_kerja_awal = $jamkerja_harian->kode_jam_kerja;
            }
        }

        // 4. Cek Jam Kerja Departemen
        if ($kode_jam_kerja_awal == null) {
            $jamkerja_dept = Detailsetjamkerjabydept::join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
                ->where('kode_dept', $kode_dept)
                ->where('kode_cabang', $kode_cabang)
                ->where('hari', $namahari)
                ->first();
            if ($jamkerja_dept) {
                $kode_jam_kerja_awal = $jamkerja_dept->kode_jam_kerja;
            }
        }

        // Check if there is an existing pending request for this date
        $exists = AjuanJadwal::where('nik', $nik)
            ->where('tanggal', $tanggal)
            ->where('status', 'p')
            ->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mengajukan perubahan jadwal untuk tanggal ini dan masih pending.'
            ], 400);
        }

        try {
            $ajuan = AjuanJadwal::create([
                'nik' => $nik,
                'tanggal' => $tanggal,
                'kode_jam_kerja_awal' => $kode_jam_kerja_awal,
                'kode_jam_kerja_tujuan' => $request->kode_jam_kerja_tujuan,
                'keterangan' => $request->keterangan,
                'status' => 'p'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan tukar shift berhasil dikirim',
                'data' => $ajuan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengajuan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userkaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Profil data karyawan tidak ditemukan'
            ], 404);
        }
        $nik = $userkaryawan->nik;

        $ajuan = AjuanJadwal::where('id', $id)->where('nik', $nik)->first();
        if (!$ajuan) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        if ($ajuan->status != 'p') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan yang sudah diproses tidak dapat dibatalkan'
            ], 400);
        }

        try {
            $ajuan->delete();
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil dibatalkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pengajuan: ' . $e->getMessage()
            ], 500);
        }
    }
}
