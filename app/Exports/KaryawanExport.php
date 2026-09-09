<?php

namespace App\Exports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KaryawanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Karyawan::query()
            ->select(
                'karyawan.*',
                'departemen.nama_dept',
                'jabatan.nama_jabatan',
                'cabang.nama_cabang',
                'status_kawin.status_kawin as nama_status_kawin',
                'status_karyawan.nama_status_karyawan'
            )
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('status_kawin', 'karyawan.kode_status_kawin', '=', 'status_kawin.kode_status_kawin')
            ->leftJoin('status_karyawan', 'karyawan.status_karyawan', '=', 'status_karyawan.kode_status_karyawan')
            ->orderBy('nama_karyawan', 'asc');

        if (!empty($this->filters['nama_karyawan'])) {
            $query->where('nama_karyawan', 'like', '%' . $this->filters['nama_karyawan'] . '%');
        }

        if (!empty($this->filters['kode_cabang'])) {
            $query->where('karyawan.kode_cabang', $this->filters['kode_cabang']);
        }

        if (!empty($this->filters['kode_dept'])) {
            $query->where('karyawan.kode_dept', $this->filters['kode_dept']);
        }

        if (!empty($this->filters['kode_jabatan'])) {
            $query->where('karyawan.kode_jabatan', $this->filters['kode_jabatan']);
        }

        if (!empty($this->filters['kode_group'])) {
            $query->where('karyawan.kode_group', $this->filters['kode_group']);
        }

        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();

            if (!empty($userCabangs)) {
                $query->whereIn('karyawan.kode_cabang', $userCabangs);
            } else {
                $query->whereRaw('1 = 0');
            }

            if (!empty($userDepartemens)) {
                $query->whereIn('karyawan.kode_dept', $userDepartemens);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'NIK',
            'NIK Perusahaan',
            'No. KTP',
            'Nama Karyawan',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'Alamat Sesuai KTP',
            'No. HP',
            'Email',
            'Jenis Kelamin',
            'Status Pernikahan',
            'Pendidikan Terakhir',
            'Jurusan',
            'Cabang',
            'Departemen',
            'Jabatan',
            'Tanggal Masuk',
            'Status Kerja',
            'NPWP',
            'Kontak Darurat',
            'Hubungan Kontak Darurat',
            'Nama Bank',
            'No. Rekening',
            'Nama Rekening',
            'Hitung PPh21',
            'RFID UID',
            'Status Keaktifan',
            'Tanggal Nonaktif',
            'Tanggal Off Gaji',
            'Lock Lokasi'
        ];
    }

    public function map($karyawan): array
    {
        return [
            "'" . $karyawan->nik,
            $karyawan->nik_show,
            "'" . $karyawan->no_ktp,
            $karyawan->nama_karyawan,
            $karyawan->tempat_lahir,
            $karyawan->tanggal_lahir,
            $karyawan->alamat,
            $karyawan->alamat_sesuai_ktp,
            $karyawan->no_hp,
            $karyawan->email,
            $karyawan->jenis_kelamin == 'L' ? 'Laki-laki' : ($karyawan->jenis_kelamin == 'P' ? 'Perempuan' : $karyawan->jenis_kelamin),
            $karyawan->nama_status_kawin,
            $karyawan->pendidikan_terakhir,
            $karyawan->jurusan,
            $karyawan->nama_cabang,
            $karyawan->nama_dept,
            $karyawan->nama_jabatan,
            $karyawan->tanggal_masuk,
            $karyawan->nama_status_karyawan,
            $karyawan->npwp,
            $karyawan->kontak_darurat,
            $karyawan->hubungan_kontak_darurat,
            $karyawan->nama_bank,
            $karyawan->no_rekening,
            $karyawan->nama_rekening,
            $karyawan->hitung_pph21 == 1 ? 'Ya' : 'Tidak',
            $karyawan->rfid_uid,
            $karyawan->status_aktif_karyawan == 1 ? 'Aktif' : 'Non Aktif',
            $karyawan->tanggal_nonaktif,
            $karyawan->tanggal_off_gaji,
            $karyawan->lock_location == 1 ? 'Ya' : 'Tidak'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Style the headers (Row 1)
        $sheet->getRowDimension(1)->setRowHeight(25);
        $headerStyle = $sheet->getStyle('A1:' . $highestColumn . '1');
        
        $headerStyle->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F4E78');
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Apply borders and vertical alignment to all cells
        $bodyStyle = $sheet->getStyle('A1:' . $highestColumn . $highestRow);
        $bodyStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $bodyStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        return [];
    }
}
