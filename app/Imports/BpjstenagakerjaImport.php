<?php

namespace App\Imports;

use App\Models\Bpjstenagakerja;
use App\Models\Karyawan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BpjstenagakerjaImport implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 2; // Skip header row
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $nik = trim($row[0] ?? '');
            $jumlah = $row[2] ?? 0;
            $tanggal_berlaku_raw = $row[3] ?? null;

            if (empty($nik)) {
                continue;
            }

            $tanggal_berlaku = $this->convertDate($tanggal_berlaku_raw);
            if (!$tanggal_berlaku) {
                Log::warning('Bpjstenagakerja Import: Invalid date for NIK ' . $nik . ' - Value: ' . $tanggal_berlaku_raw);
                continue;
            }

            // Generate Kode BPJS Tenaga Kerja
            $tahun = date('Y', strtotime($tanggal_berlaku));
            $last_bpjs = Bpjstenagakerja::orderBy('kode_bpjs_tk', 'desc')
                ->whereRaw('YEAR(tanggal_berlaku) = ' . $tahun)
                ->first();
            
            $last_kode = $last_bpjs != null ? $last_bpjs->kode_bpjs_tk : '';
            $kode_bpjs = buatkode($last_kode, "K" . substr($tahun, 2, 2), 4);

            // Check if employee exists
            $karyawan = Karyawan::where('nik', $nik)->first();
            if (!$karyawan) {
                Log::warning('Bpjstenagakerja Import: Employee not found with NIK ' . $nik);
                continue;
            }

            // Check if already exists for this NIK and date
            $exists = Bpjstenagakerja::where('nik', $nik)
                ->where('tanggal_berlaku', $tanggal_berlaku)
                ->first();

            if ($exists) {
                $exists->update([
                    'jumlah' => $jumlah
                ]);
            } else {
                Bpjstenagakerja::create([
                    'kode_bpjs_tk' => $kode_bpjs,
                    'nik' => $nik,
                    'jumlah' => $jumlah,
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
