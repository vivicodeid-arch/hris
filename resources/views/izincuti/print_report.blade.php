<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Laporan Pengajuan Cuti Karyawan - {{ date('Y-m-d H:i:s') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 10mm 12mm;
        }

        body {
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
        }

        /* Header Logo & Info */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 15px;
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
            font-size: 10px;
            color: #475569;
            margin: 0;
            line-height: 1.3;
        }

        /* Title block */
        .report-title-block {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-title {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 5px 0;
            color: #0f172a;
        }

        .report-subtitle {
            font-size: 11px;
            color: #475569;
            margin: 0;
        }

        /* Filter info table */
        .filter-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .filter-table td {
            padding: 6px 12px;
            vertical-align: top;
        }

        .filter-table td span.label {
            font-weight: 600;
            color: #64748b;
        }

        .filter-table td span.val {
            font-weight: 700;
            color: #334155;
        }

        /* Main Table styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th, .data-table td {
            border: 1px solid #1e293b;
            padding: 8px 6px;
            font-size: 10px;
            text-align: left;
            vertical-align: top;
        }

        .data-table th {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            text-align: center;
            font-size: 9px;
        }

        .data-table td.center {
            text-align: center;
        }

        /* Status colors */
        .status-pending {
            color: #d97706;
            font-weight: 700;
        }

        .status-disetujui {
            color: #16a34a;
            font-weight: 700;
        }

        .status-ditolak {
            color: #dc2626;
            font-weight: 700;
        }

        /* Signatures block */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 5px;
        }

        .signature-title {
            font-weight: 600;
            color: #475569;
            font-size: 10px;
            margin-bottom: 55px;
        }

        .signature-name {
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
            margin-bottom: 2px;
            font-size: 11px;
        }

        .signature-role {
            font-size: 9px;
            color: #64748b;
            font-style: italic;
        }

        /* Print Media styling */
        @media print {
            body {
                background: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
            .data-table th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .filter-table {
                background-color: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Floating Toolbar */
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
            font-size: 11px;
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
            Cetak Laporan
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
        <div class="report-title-block">
            <h2 class="report-title">Laporan Pengajuan Cuti Karyawan</h2>
            <p class="report-subtitle">Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
        </div>

        <!-- Applied Filters Info -->
        <table class="filter-table">
            <tr>
                <td>
                    <span class="label">Periode Cuti:</span> <span class="val">{{ !empty($filters['dari']) && !empty($filters['sampai']) ? DateToIndo($filters['dari']) . ' s.d ' . DateToIndo($filters['sampai']) : 'Semua Tanggal' }}</span>
                </td>
                <td>
                    <span class="label">Cabang:</span> <span class="val">{{ $filters['cabang'] }}</span>
                </td>
                <td>
                    <span class="label">Departemen:</span> <span class="val">{{ $filters['dept'] }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Nama Karyawan:</span> <span class="val">{{ !empty($filters['karyawan']) ? $filters['karyawan'] : 'Semua Karyawan' }}</span>
                </td>
                <td colspan="2">
                    <span class="label">Status Pengajuan:</span> <span class="val">{{ $filters['status'] }}</span>
                </td>
            </tr>
        </table>

        <!-- Main Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 70px;">Kode Cuti</th>
                    <th style="width: 80px;">NIK</th>
                    <th style="width: 140px;">Nama Karyawan</th>
                    <th style="width: 110px;">Jabatan</th>
                    <th style="width: 90px;">Dept</th>
                    <th style="width: 90px;">Cabang</th>
                    <th style="width: 90px;">Jenis Cuti</th>
                    <th style="width: 110px;">Tanggal Ajuan</th>
                    <th style="width: 140px;">Periode Cuti</th>
                    <th style="width: 40px;">Lama</th>
                    <th>Pelimpahan Tugas</th>
                    <th>Nama Kepala Divisi</th>
                    <th>Alasan Cuti</th>
                    <th style="width: 70px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($izincuti as $d)
                    @php
                        $lama = hitungHari($d->dari, $d->sampai);
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="center">{{ $d->kode_izin_cuti }}</td>
                        <td class="center">{{ $d->nik_show ?? $d->nik }}</td>
                        <td>{{ $d->nama_karyawan }}</td>
                        <td>{{ $d->nama_jabatan }}</td>
                        <td>{{ $d->nama_dept }}</td>
                        <td>{{ $d->nama_cabang }}</td>
                        <td>{{ $d->jenis_cuti }}</td>
                        <td class="center">{{ DateToIndo($d->tanggal) }}</td>
                        <td>{{ DateToIndo($d->dari) }} s.d {{ DateToIndo($d->sampai) }}</td>
                        <td class="center">{{ $lama }} Hari</td>
                        <td>{{ $d->pelimpahan_tugas }}</td>
                        <td>{{ $d->nama_kepala_divisi }}</td>
                        <td>{{ $d->keterangan }}</td>
                        <td class="center">
                            @if ($d->status == 0)
                                <span class="status-pending">Pending</span>
                            @elseif ($d->status == 1)
                                <span class="status-disetujui">Disetujui</span>
                            @elseif ($d->status == 2)
                                <span class="status-ditolak">Ditolak</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="center" style="padding: 20px; font-style: italic; color: #64748b;">
                            Tidak ada data pengajuan cuti yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signatures -->
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-title">Dibuat Oleh,</div>
                    <div class="signature-name">....................................</div>
                    <div class="signature-role">HRD & Personalia</div>
                </td>
                <td>
                    <div class="signature-title">Mengetahui,</div>
                    <div class="signature-name">....................................</div>
                    <div class="signature-role">Direktur / Pimpinan</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Auto Print Script -->
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(function() {
                window.print();
            }, 600);
        });
    </script>
</body>

</html>
