<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Slip Gaji Harian {{ date('Y-m-d H:i:s') }}</title>
    <style>
        body {
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            margin: 0;
            padding: 20px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
        }

        .slip-card {
            width: 320px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.025);
            padding: 20px;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .slip-header {
            border-bottom: 1.5px dashed #cbd5e1;
            padding-bottom: 12px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-info {
            text-align: left;
        }

        .company-name {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
        }

        .company-sub {
            font-size: 9px;
            color: #64748b;
            font-weight: 500;
        }

        .slip-title-block {
            text-align: right;
        }

        .slip-title {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
        }

        .slip-periode {
            font-size: 9px;
            color: #475569;
            font-weight: 600;
            margin-top: 2px;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        .employee-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1.5px dashed #cbd5e1;
        }

        .employee-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .employee-label {
            font-size: 8px;
            font-weight: 700;
            color: #94a3b8;
        }

        .employee-value {
            font-size: 11px;
            font-weight: 600;
            color: #334155;
        }

        .work-summary-badge {
            grid-column: span 2;
            border-top: 1.5px dashed #e2e8f0;
            padding-top: 6px;
            margin-top: 2px;
            font-size: 9px;
            color: #64748b;
            font-weight: 500;
            line-height: 1.3;
        }

        .table-section {
            margin-bottom: 12px;
        }

        .section-header {
            font-size: 10px;
            font-weight: 700;
            padding: 4px 0;
            margin-bottom: 6px;
            border-bottom: 1px solid #cbd5e1;
        }

        .section-header.earning {
            color: #047857;
        }

        .section-header.deduction {
            color: #be123c;
        }

        .section-header.adjustment {
            color: #0369a1;
        }

        .slip-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            border-bottom: 1px dashed #f1f5f9;
            font-size: 11px;
        }

        .slip-row:last-child {
            border-bottom: none;
        }

        .slip-row.subtotal {
            font-weight: 700;
            border-top: 1px dashed #cbd5e1;
            border-bottom: none;
            padding-top: 6px;
            color: #1e293b;
            margin-top: 2px;
        }

        .slip-row-label {
            color: #475569;
        }

        .slip-row-value {
            font-weight: 600;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
        }

        .net-salary-card {
            border-top: 1.5px dashed #cbd5e1;
            border-bottom: 1.5px dashed #cbd5e1;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 14px 0;
        }

        .net-salary-label {
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
        }

        .net-salary-value {
            font-size: 14px;
            font-weight: 800;
            color: #047857;
            font-variant-numeric: tabular-nums;
        }

        .slip-footer {
            border-top: 1.5px dashed #cbd5e1;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 6px;
        }

        .system-info {
            font-size: 8px;
            color: #94a3b8;
            line-height: 1.3;
        }

        .signature-block {
            text-align: center;
            min-width: 100px;
        }

        .signature-title {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 25px;
        }

        .signature-line {
            border-bottom: 1px solid #cbd5e1;
            width: 100%;
            margin-bottom: 2px;
        }

        .signature-subtitle {
            font-size: 7px;
            color: #94a3b8;
            font-weight: 500;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0;
            }

            .container {
                gap: 10px;
            }

            .slip-card {
                border: 1px solid #e2e8f0 !important;
                box-shadow: none !important;
                margin-bottom: 10px;
            }

            .net-salary-card {
                border: 1px solid #0f172a !important;
                color: #0f172a !important;
                background: #f8fafc !important;
            }

            .net-salary-value {
                color: #0f172a !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        @foreach ($laporan_presensi as $d)
            @php
                $tanggal_presensi = $periode_dari;
                $hari_hadir = 0;
                $total_denda = 0;

                while (strtotime($tanggal_presensi) <= strtotime($periode_sampai)) {
                    if (isset($d[$tanggal_presensi])) {
                        if ($d[$tanggal_presensi]['status'] == 'h') {
                            $hari_hadir++;
                            
                            $denda_dari_db = isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                ? $d[$tanggal_presensi]['denda']
                                : null;
                            
                            if ($denda_dari_db !== null) {
                                $total_denda += $denda_dari_db;
                            } else {
                                $jam_masuk = $tanggal_presensi . ' ' . $d[$tanggal_presensi]['jam_masuk'];
                                $terlambat = hitungjamterlambat($d[$tanggal_presensi]['jam_in'], $jam_masuk);
                                if ($terlambat != null && $terlambat['desimal_terlambat'] < 1) {
                                    $total_denda += hitungdenda($denda_list, $terlambat['menitterlambat']);
                                }
                            }
                        }
                    }
                    $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                }

                $total_upah = $d['gaji_pokok'] * $hari_hadir;
                $gaji_bersih = $total_upah - $total_denda;
            @endphp

            <div class="slip-card">
                <!-- Header -->
                <div class="slip-header">
                    <div class="company-info">
                        <div class="company-name">{{ $generalsetting->nama_perusahaan }}</div>
                        <div class="company-sub">Sistem Payroll Modern (Harian)</div>
                    </div>
                    <div class="slip-title-block">
                        <div class="slip-title">Slip Gaji</div>
                        <div class="slip-periode">{{ date('d/m/Y', strtotime($periode_dari)) }} - {{ date('d/m/Y', strtotime($periode_sampai)) }}</div>
                    </div>
                </div>

                <!-- Employee Info -->
                <div class="employee-grid">
                    <div class="employee-item">
                        <span class="employee-label">NIK</span>
                        <span class="employee-value">{{ $d['nik_show'] ?? $d['nik'] }}</span>
                    </div>
                    <div class="employee-item">
                        <span class="employee-label">No Rekening</span>
                        <span class="employee-value">{{ $d['no_rekening'] ?? '-' }} {{ !empty($d['nama_bank']) ? '(' . strtoupper($d['nama_bank']) . ')' : '' }}</span>
                    </div>
                    <div class="employee-item">
                        <span class="employee-label">Nama Rekening</span>
                        <span class="employee-value">{{ $d['nama_rekening'] ?? '-' }}</span>
                    </div>
                    <div class="employee-item">
                        <span class="employee-label">Nama Karyawan</span>
                        <span class="employee-value">{{ $d['nama_karyawan'] }}</span>
                    </div>
                    <div class="employee-item">
                        <span class="employee-label">Jabatan</span>
                        <span class="employee-value">{{ $d['nama_jabatan'] }}</span>
                    </div>
                    <div class="employee-item">
                        <span class="employee-label">Departemen</span>
                        <span class="employee-value">{{ $d['kode_dept'] }}</span>
                    </div>
                    <div class="work-summary-badge">
                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <span>Tipe Gaji: Harian</span>
                            <span>Rate: Rp {{ number_format($d['gaji_pokok'], 0, ',', '.') }}/hari</span>
                            <span>Kehadiran: {{ $hari_hadir }} Hari</span>
                        </div>
                    </div>
                </div>

                <!-- Penghasilan -->
                <div class="table-section">
                    <div class="section-header earning">Penghasilan</div>
                    <div class="slip-row">
                        <span class="slip-row-label">Upah Pokok (Harian)</span>
                        <span class="slip-row-value">Rp {{ number_format($total_upah, 0, ',', '.') }}</span>
                    </div>
                    <div class="slip-row subtotal">
                        <span class="slip-row-label">Total Penghasilan (Bruto)</span>
                        <span class="slip-row-value">Rp {{ number_format($total_upah, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Potongan -->
                @if ($total_denda > 0)
                    <div class="table-section">
                        <div class="section-header deduction">Potongan</div>
                        <div class="slip-row">
                            <span class="slip-row-label">Denda Keterlambatan</span>
                            <span class="slip-row-value">Rp {{ number_format($total_denda, 0, ',', '.') }}</span>
                        </div>
                        <div class="slip-row subtotal">
                            <span class="slip-row-label">Total Potongan</span>
                            <span class="slip-row-value">Rp {{ number_format($total_denda, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Total -->
                <div class="net-salary-card">
                    <span class="net-salary-label">Gaji Bersih (Take Home Pay)</span>
                    <span class="net-salary-value">Rp {{ number_format($gaji_bersih, 0, ',', '.') }}</span>
                </div>

                <!-- Footer -->
                <div class="slip-footer">
                    <div class="system-info">
                        Dicetak: {{ date('d/m/Y H:i') }}<br>
                        Sistem Payroll Premium v2.0
                    </div>
                    <div class="signature-block">
                        <div class="signature-title">Manager Payroll</div>
                        <div class="signature-line"></div>
                        <div class="signature-subtitle">{{ $generalsetting->nama_perusahaan }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>
