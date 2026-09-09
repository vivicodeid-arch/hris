<?php

namespace App\Imports;

use App\Models\Tunjangan;
use App\Models\Detailtunjangan;
use App\Models\Karyawan;
use App\Models\Jenistunjangan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TunjanganImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $jenis_tunjangan = Jenistunjangan::orderBy('kode_jenis_tunjangan')->get();
        
        foreach ($rows as $row) {
            $nik = trim($row['nik'] ?? '');
            $tanggal_berlaku_raw = $row['tanggal_berlaku'] ?? null;

            if (empty($nik)) {
                continue;
            }

            $tanggal_berlaku = $this->convertDate($tanggal_berlaku_raw);
            if (!$tanggal_berlaku) {
                Log::warning('Tunjangan Import: Invalid date for NIK ' . $nik . ' - Value: ' . $tanggal_berlaku_raw);
                continue;
            }

            // Generate Kode Tunjangan
            $tahun = date('Y', strtotime($tanggal_berlaku));
            $last_tunjangan = Tunjangan::orderBy('kode_tunjangan', 'desc')
                ->whereRaw('YEAR(tanggal_berlaku) = ' . $tahun)
                ->first();
            
            $last_kode = $last_tunjangan != null ? $last_tunjangan->kode_tunjangan : '';
            $kode_tunjangan = buatkode($last_kode, "T" . substr($tahun, 2, 2), 4);

            // Check if employee exists
            $karyawan = Karyawan::where('nik', $nik)->first();
            if (!$karyawan) {
                Log::warning('Tunjangan Import: Employee not found with NIK ' . $nik);
                continue;
            }

            DB::beginTransaction();
            try {
                // Check if already exists for this NIK and date
                $exists = Tunjangan::where('nik', $nik)
                    ->where('tanggal_berlaku', $tanggal_berlaku)
                    ->first();

                if ($exists) {
                    $kode_tunjangan_active = $exists->kode_tunjangan;
                    Detailtunjangan::where('kode_tunjangan', $kode_tunjangan_active)->delete();
                } else {
                    Tunjangan::create([
                        'kode_tunjangan' => $kode_tunjangan,
                        'nik' => $nik,
                        'tanggal_berlaku' => $tanggal_berlaku
                    ]);
                    $kode_tunjangan_active = $kode_tunjangan;
                }

                // Import details
                foreach ($jenis_tunjangan as $jt) {
                    $slug = str_replace('-', '_', \Illuminate\Support\Str::slug($jt->jenis_tunjangan, '_'));
                    $jumlah = $row[$slug] ?? 0;
                    
                    if ($jumlah > 0) {
                        Detailtunjangan::create([
                            'kode_tunjangan' => $kode_tunjangan_active,
                            'kode_jenis_tunjangan' => $jt->kode_jenis_tunjangan,
                            'jumlah' => $jumlah
                        ]);
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Tunjangan Import Error: ' . $e->getMessage());
            }
        }
    }

    private function convertDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
            return $dateValue;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d', 'd.m.Y'];

        foreach ($formats as $format) {
            try {
                $carbon = Carbon::createFromFormat($format, $dateValue);
                if ($carbon) {
                    return $carbon->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            $carbon = Carbon::parse($dateValue);
            return $carbon->format('Y-m-d');
        } catch (\Exception $e) {
            if (is_numeric($dateValue)) {
                try {
                    $excelDate = Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($dateValue - 2);
                    return $excelDate->format('Y-m-d');
                } catch (\Exception $e2) {
                    Log::error('Failed to convert Excel serial date: ' . $dateValue);
                }
            }
            return null;
        }
    }
}
