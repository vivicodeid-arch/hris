<?php

namespace App\Imports;

use App\Models\Gajipokok;
use App\Models\Karyawan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GajipokokImport implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 2; // Skip header row
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Row is indexed by column number: 0, 1, 2, ...
            $nik = trim($row[0] ?? '');
            $jumlah = $row[2] ?? 0;
            $jenis_upah = $row[3] ?? 'Bulanan';
            $tanggal_berlaku_raw = $row[4] ?? null;

            if (empty($nik)) {
                continue;
            }

            $tanggal_berlaku = $this->convertDate($tanggal_berlaku_raw);
            if (!$tanggal_berlaku) {
                Log::warning('Gajipokok Import: Invalid date for NIK ' . $nik . ' - Value: ' . $tanggal_berlaku_raw);
                continue;
            }

            // Generate Kode Gaji
            $tahun_gaji = date('Y', strtotime($tanggal_berlaku));
            $last_gaji = Gajipokok::orderBy('kode_gaji', 'desc')
                ->whereRaw('YEAR(tanggal_berlaku) = ' . $tahun_gaji)
                ->first();
            
            $last_kode_gaji = $last_gaji != null ? $last_gaji->kode_gaji : '';
            $kode_gaji = buatkode($last_kode_gaji, "G" . substr($tahun_gaji, 2, 2), 4);

            // Check if employee exists
            $karyawan = Karyawan::where('nik', $nik)->first();
            if (!$karyawan) {
                Log::warning('Gajipokok Import: Employee not found with NIK ' . $nik);
                continue;
            }

            // Check if already exists for this NIK and date
            $exists = Gajipokok::where('nik', $nik)
                ->where('tanggal_berlaku', $tanggal_berlaku)
                ->first();

            if ($exists) {
                $exists->update([
                    'jumlah' => $jumlah,
                    'jenis_upah' => $jenis_upah
                ]);
            } else {
                Gajipokok::create([
                    'kode_gaji' => $kode_gaji,
                    'nik' => $nik,
                    'jumlah' => $jumlah,
                    'jenis_upah' => $jenis_upah,
                    'tanggal_berlaku' => $tanggal_berlaku
                ]);
            }
        }
    }

    private function convertDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        // If it's already Y-m-d
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
                    // Excel serial number
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
