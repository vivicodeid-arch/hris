<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Formulir Pengajuan Cuti - {{ $izincuti->nama_karyawan }} - {{ $izincuti->kode_izin_cuti }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }

        body {
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.5;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Container styling */
        .wrapper {
            width: 100%;
        }

        /* Header Logo & Info */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-logo {
            width: 80px;
            padding-right: 15px;
            vertical-align: middle;
        }

        .header-logo img {
            max-width: 80px;
            height: auto;
            display: block;
        }

        .header-info {
            vertical-align: middle;
            text-align: left;
        }

        .company-name {
            font-size: 16px;
            font-weight: 800;
            margin: 0 0 4px 0;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        .company-address {
            font-size: 11px;
            color: #475569;
            margin: 0;
            line-height: 1.4;
        }

        /* Document Title */
        .doc-title {
            text-align: center;
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 25px 0 10px 0;
            color: #0f172a;
        }

        .doc-number {
            text-align: center;
            font-size: 11px;
            color: #64748b;
            margin-bottom: 30px;
        }

        /* Section Title */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1.5px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 25px;
            margin-bottom: 12px;
            color: #334155;
            letter-spacing: 0.5px;
        }

        /* Details Table styling */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .details-table td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .details-table td.label {
            width: 30%;
            font-weight: 600;
            color: #475569;
        }

        .details-table td.separator {
            width: 3%;
            color: #64748b;
        }

        .details-table td.value {
            width: 67%;
            color: #1e293b;
        }

        /* Leave Balance Summary Table */
        .balance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        .balance-table th, .balance-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            text-align: center;
        }

        .balance-table th {
            background-color: #f8fafc;
            font-weight: 700;
            color: #334155;
            font-size: 11px;
            text-transform: uppercase;
        }

        .balance-table td {
            font-size: 12px;
            color: #1e293b;
        }

        /* Signature block */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 45px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 10px;
        }

        .signature-title {
            font-weight: 600;
            color: #475569;
            font-size: 11px;
            margin-bottom: 70px; /* Space for physical signature */
        }

        .signature-name {
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .signature-role {
            font-size: 10px;
            color: #64748b;
            font-style: italic;
        }

        /* Print Media queries */
        @media print {
            body {
                background: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
            .balance-table th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Floating Back / Print buttons for screen mode */
        .no-print-toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            z-index: 9999;
        }

        .btn-action {
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-print {
            background: #0f172a;
            color: #ffffff;
        }

        .btn-print:hover {
            background: #1e293b;
        }

        .btn-close {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .btn-close:hover {
            background: #e2e8f0;
        }
    </style>
</head>

<body>
    <!-- Floating Toolbar (Not visible on print) -->
    <div class="no-print-toolbar no-print">
        <button onclick="window.print()" class="btn-action btn-print">
            Cetak Formulir
        </button>
        <button onclick="window.close()" class="btn-action btn-close">
            Tutup
        </button>
    </div>

    <div class="wrapper">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    @if ($generalsetting->logo && Storage::exists('public/logo/' . $generalsetting->logo))
                        <img src="{{ asset('storage/logo/' . $generalsetting->logo) }}" alt="Logo Perusahaan">
                    @else
                        <img src="https://placehold.co/150x150?text=LOGO" alt="Logo Perusahaan">
                    @endif
                </td>
                <td class="header-info">
                    <h1 class="company-name">{{ textUpperCase($generalsetting->nama_perusahaan) }}</h1>
                    <p class="company-address">
                        {{ $generalsetting->alamat }}<br>
                        Telepon: {{ $generalsetting->telepon }}
                    </p>
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div class="doc-title">Formulir Pengajuan Cuti Karyawan</div>
        <div class="doc-number">Nomor Dokumen: {{ $izincuti->kode_izin_cuti }}</div>

        <!-- Section 1: Data Karyawan -->
        <div class="section-title">Data Karyawan</div>
        <table class="details-table">
            <tr>
                <td class="label">Nomor Induk Karyawan (NIK)</td>
                <td class="separator">:</td>
                <td class="value">{{ $izincuti->nik_show ?? $izincuti->nik }}</td>
            </tr>
            <tr>
                <td class="label">Nama Lengkap Karyawan</td>
                <td class="separator">:</td>
                <td class="value">{{ $izincuti->nama_karyawan }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan / Posisi</td>
                <td class="separator">:</td>
                <td class="value">{{ $izincuti->nama_jabatan }}</td>
            </tr>
            <tr>
                <td class="label">Departemen / Divisi</td>
                <td class="separator">:</td>
                <td class="value">{{ $izincuti->nama_dept }}</td>
            </tr>
            <tr>
                <td class="label">Cabang Penempatan</td>
                <td class="separator">:</td>
                <td class="value">{{ $izincuti->nama_cabang }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Masuk Kerja</td>
                <td class="separator">:</td>
                <td class="value">
                    @if ($izincuti->tanggal_masuk)
                        {{ DateToIndo($izincuti->tanggal_masuk) }}
                        @php
                            $tgl_masuk = \Carbon\Carbon::parse($izincuti->tanggal_masuk);
                            $dari_cuti = \Carbon\Carbon::parse($izincuti->dari);
                            $diff = $tgl_masuk->diff($dari_cuti);
                            $masa_kerja = [];
                            if ($diff->y > 0) $masa_kerja[] = $diff->y . ' Tahun';
                            if ($diff->m > 0) $masa_kerja[] = $diff->m . ' Bulan';
                            if ($diff->d > 0 && $diff->y == 0) $masa_kerja[] = $diff->d . ' Hari';
                            $masa_kerja_str = count($masa_kerja) > 0 ? implode(' ', $masa_kerja) : '0 Hari';
                        @endphp
                        (Masa Kerja: {{ $masa_kerja_str }})
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        <!-- Section 2: Detail Cuti -->
        <div class="section-title">Detail Pengajuan Cuti</div>
        <table class="details-table">
            <tr>
                <td class="label">Jenis Cuti yang Diajukan</td>
                <td class="separator">:</td>
                <td class="value">
                    <strong>{{ $izincuti->jenis_cuti }}</strong>
                </td>
            </tr>
            <tr>
                <td class="label">Tanggal Pelaksanaan Cuti</td>
                <td class="separator">:</td>
                <td class="value">
                    @php
                        $lama = hitungHari($izincuti->dari, $izincuti->sampai);
                    @endphp
                    <strong>{{ DateToIndo($izincuti->dari) }}</strong> s.d <strong>{{ DateToIndo($izincuti->sampai) }}</strong> 
                    ({{ $lama }} Hari Kerja)
                </td>
            </tr>
            <tr>
                <td class="label">Pelimpahan Tugas</td>
                <td class="separator">:</td>
                <td class="value">{{ $izincuti->pelimpahan_tugas }}</td>
            </tr>
            <tr>
                <td class="label">Nama Kepala Divisi</td>
                <td class="separator">:</td>
                <td class="value">{{ $izincuti->nama_kepala_divisi }}</td>
            </tr>
            <tr>
                <td class="label">Alasan Cuti</td>
                <td class="separator">:</td>
                <td class="value" style="white-space: pre-wrap;">{{ $izincuti->keterangan }}</td>
            </tr>
        </table>

        <!-- Section 3: Quota (If C01 - Cuti Tahunan) -->
        @if ($izincuti->kode_cuti == 'C01')
            <div class="section-title">Informasi Saldo Cuti Tahunan</div>
            <table class="balance-table">
                <thead>
                    <tr>
                        <th>Jatah Cuti Tahunan</th>
                        <th>Cuti Telah Diambil (Tahun Ini)</th>
                        <th>Sisa Saldo Cuti</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $izincuti->jatah_cuti_max }} Hari</td>
                        <td>{{ $cuti_dipakai }} Hari</td>
                        <td style="font-weight: bold; color: {{ $sisa_cuti > 0 ? '#0f172a' : '#ef4444' }}">{{ $sisa_cuti }} Hari</td>
                    </tr>
                </tbody>
            </table>
        @endif

        <!-- Section 4: Tanda Tangan / Approval -->
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-title">Diajukan Oleh,</div>
                    <div class="signature-name">{{ $izincuti->nama_karyawan }}</div>
                    <div class="signature-role">Karyawan (Pemohon)</div>
                </td>
                <td>
                    <div class="signature-title">Diperiksa Oleh,</div>
                    <div class="signature-name">....................................</div>
                    <div class="signature-role">HRD & Personalia</div>
                </td>
                <td>
                    <div class="signature-title">Disetujui Oleh,</div>
                    <div class="signature-name">....................................</div>
                    <div class="signature-role">Atasan / Direksi</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Auto Print Script -->
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>

</html>
