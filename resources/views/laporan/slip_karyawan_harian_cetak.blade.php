@extends('layouts.mobile.app')
@section('content')
    <style>
        body {
            background: #f8fafc !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            margin: 0;
            padding: 0;
        }

        #content-section {
            margin-top: 30px;
            padding: 20px 10px 80px 10px;
            position: relative;
            z-index: 1;
            min-height: 100vh;
            background: #f8fafc;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            max-width: 650px;
            margin: 0 auto;
            padding: 0 10px;
        }

        .print-action-bar {
            width: 100%;
            max-width: 380px;
            display: flex;
            justify-content: center;
            margin-top: 10px;
            margin-bottom: -5px;
        }

        .btn-print-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            background: linear-gradient(135deg, var(--color-nav, #32745e), #255746) !important;
            color: white !important;
            border: none !important;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(50, 116, 94, 0.2);
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            gap: 8px;
        }

        .btn-print-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(50, 116, 94, 0.3);
            opacity: 0.95;
        }

        .slip-card {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.03), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            padding: 24px;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            page-break-inside: avoid;
            margin-top: 5px;
            background-image: radial-gradient(circle at 100% 150px, rgba(50, 116, 94, 0.02) 24%, transparent 24%),
                              radial-gradient(circle at 0% 100%, rgba(50, 116, 94, 0.01) 30%, transparent 30%);
        }

        .watermark {
            position: absolute;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 36px;
            font-weight: 900;
            color: rgba(148, 163, 184, 0.04);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
            text-transform: uppercase;
            letter-spacing: 6px;
        }

        .slip-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 6px;
        }

        .company-logo-badge {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--color-nav, #32745e), #4ca68c);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 15px;
            box-shadow: 0 4px 6px -1px rgba(50, 116, 94, 0.15);
            flex-shrink: 0;
        }

        .company-info {
            text-align: left;
        }

        .company-name {
            font-size: 14px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.2;
        }

        .company-sub {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
            margin-top: 1px;
        }

        .slip-title-block {
            text-align: right;
            flex-shrink: 0;
        }

        .slip-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--color-nav, #32745e);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .slip-periode {
            font-size: 10px;
            color: #475569;
            font-weight: 700;
            margin-top: 2px;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        .divider-dashed {
            border-top: 1.5px dashed #e2e8f0;
            margin: 14px 0;
            height: 0;
        }

        .employee-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 14px;
            margin-bottom: 12px;
        }

        .employee-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .employee-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
        }

        .employee-value {
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
        }

        .work-summary-grid {
            grid-column: span 2;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            background: #f8fafc;
            border-radius: 10px;
            padding: 8px;
            margin-top: 4px;
            border: 1px solid #f1f5f9;
        }

        .work-summary-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 2px;
        }

        .work-summary-label {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
        }

        .work-summary-value {
            font-size: 11px;
            font-weight: 700;
            color: #334155;
        }

        .table-section {
            margin-bottom: 14px;
        }

        .section-header {
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding-bottom: 4px;
            margin-bottom: 8px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .section-header::before {
            content: '';
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
        }

        .section-header.earning {
            color: #059669;
            border-bottom-color: rgba(5, 150, 105, 0.1);
        }
        .section-header.earning::before {
            background-color: #059669;
        }

        .section-header.deduction {
            color: #e11d48;
            border-bottom-color: rgba(225, 29, 72, 0.1);
        }
        .section-header.deduction::before {
            background-color: #e11d48;
        }

        .section-header.adjustment {
            color: #0284c7;
            border-bottom-color: rgba(2, 132, 199, 0.1);
        }
        .section-header.adjustment::before {
            background-color: #0284c7;
        }

        .slip-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 5px 0;
            font-size: 12.5px;
            line-height: 1.2;
        }

        .slip-row-label {
            color: #475569;
            background: #ffffff;
            padding-right: 4px;
            z-index: 1;
        }

        .slip-row-dots {
            flex-grow: 1;
            border-bottom: 1px dotted #e2e8f0;
            margin-bottom: 4px;
            height: 0;
            z-index: 0;
        }

        .slip-row-value {
            font-weight: 700;
            color: #1e293b;
            padding-left: 4px;
            z-index: 1;
            font-variant-numeric: tabular-nums;
        }

        .slip-row.subtotal {
            font-weight: 800;
            padding-top: 8px;
            margin-top: 4px;
            border-top: 1px dashed #cbd5e1;
        }

        .slip-row.subtotal .slip-row-label {
            font-weight: 800;
            color: #1e293b;
        }
        .slip-row.subtotal .slip-row-value {
            font-weight: 800;
            color: #1e293b;
        }

        .net-salary-card {
            border-top: 1.5px dashed #cbd5e1;
            border-bottom: 1.5px dashed #cbd5e1;
            padding: 12px 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin: 16px 0;
        }

        .net-salary-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            text-align: left;
            width: 100%;
        }

        .net-salary-value {
            font-size: 22px;
            font-weight: 800;
            color: var(--color-nav, #32745e);
            text-align: right;
            width: 100%;
            font-variant-numeric: tabular-nums;
        }

        .slip-footer {
            border-top: 1.5px dashed #e2e8f0;
            padding-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 10px;
        }

        .system-info {
            font-size: 10px;
            color: #94a3b8;
            line-height: 1.5;
            text-align: left;
        }

        .signature-block {
            text-align: center;
            min-width: 110px;
        }

        .signature-title {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 40px;
        }

        .signature-line {
            border-bottom: 1.5px solid #cbd5e1;
            width: 100%;
            margin-bottom: 4px;
        }

        .signature-subtitle {
            font-size: 9px;
            color: #94a3b8;
            font-weight: 500;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            #header-section, 
            .appHeader, 
            .appBottomMenu, 
            .print-action-bar,
            #preloaderOverlay {
                display: none !important;
            }

            #content-section {
                margin-top: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                height: auto !important;
                overflow: visible !important;
            }

            .container {
                padding: 0 !important;
                margin: 0 auto !important;
                max-width: 100% !important;
                display: block !important;
            }

            .slip-card {
                border: none !important;
                box-shadow: none !important;
                padding: 10px 0 !important;
                margin: 0 auto !important;
                background: #ffffff !important;
                background-image: none !important;
                max-width: 100% !important;
            }

            .net-salary-card {
                border-top: 2px dashed #000000 !important;
                border-bottom: 2px dashed #000000 !important;
                border-left: none !important;
                border-right: none !important;
                color: #000000 !important;
                background: transparent !important;
                box-shadow: none !important;
                padding: 10px 0 !important;
            }

            .net-salary-label {
                color: #000000 !important;
                text-align: left !important;
            }

            .net-salary-value {
                color: #000000 !important;
                text-align: right !important;
            }
            
            .watermark {
                color: rgba(0, 0, 0, 0.02) !important;
            }

            .slip-row-label {
                background: #ffffff !important;
            }
        }
    </style>
    <div id="header-section">
        <div class="appHeader bg-primary text-light">
            <div class="left">
                <a href="{{ route('dashboard.index') }}" class="headerButton goBack">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                </a>
            </div>
            <div class="pageTitle">Slip Gaji</div>
            <div class="right">
                <a href="#" onclick="window.print(); return false;" class="headerButton">
                    <ion-icon name="download-outline"></ion-icon>
                </a>
            </div>
        </div>
    </div>
    <div id="content-section" style="margin-top: 30px;">
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
                    <!-- Watermark Background -->
                    <div class="watermark">SALARY SLIP</div>

                    <div style="position: relative; z-index: 1;">
                        <!-- Header -->
                        <div class="slip-header">
                            <div class="company-logo-badge">
                                {{ strtoupper(substr($generalsetting->nama_perusahaan, 0, 2)) }}
                            </div>
                            <div class="company-info" style="flex-grow: 1;">
                                <div class="company-name">{{ $generalsetting->nama_perusahaan }}</div>
                                <div class="company-sub">Sistem Payroll Resmi (Harian)</div>
                            </div>
                            <div class="slip-title-block">
                                <div class="slip-title">Slip Gaji</div>
                                <div class="slip-periode">{{ date('d/m/Y', strtotime($periode_dari)) }} - {{ date('d/m/Y', strtotime($periode_sampai)) }}</div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="divider-dashed"></div>

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
                            <div class="work-summary-grid">
                                <div class="work-summary-item">
                                    <span class="work-summary-label">Tipe Gaji</span>
                                    <span class="work-summary-value">Harian</span>
                                </div>
                                <div class="work-summary-item">
                                    <span class="work-summary-label">Rate / Hari</span>
                                    <span class="work-summary-value">Rp {{ number_format($d['gaji_pokok'], 0, ',', '.') }}</span>
                                </div>
                                <div class="work-summary-item">
                                    <span class="work-summary-label">Kehadiran</span>
                                    <span class="work-summary-value">{{ $hari_hadir }} Hari</span>
                                </div>
                            </div>
                        </div>

                        <!-- Penghasilan -->
                        <div class="table-section">
                            <div class="section-header earning">Penghasilan</div>
                            <div class="slip-row">
                                <span class="slip-row-label">Upah Pokok (Harian)</span>
                                <span class="slip-row-dots"></span>
                                <span class="slip-row-value">Rp {{ number_format($total_upah, 0, ',', '.') }}</span>
                            </div>
                            <div class="slip-row subtotal">
                                <span class="slip-row-label">Total Penghasilan (Bruto)</span>
                                <span class="slip-row-dots"></span>
                                <span class="slip-row-value">Rp {{ number_format($total_upah, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Potongan -->
                        @if ($total_denda > 0)
                            <div class="table-section">
                                <div class="section-header deduction">Potongan</div>
                                <div class="slip-row">
                                    <span class="slip-row-label">Denda Keterlambatan</span>
                                    <span class="slip-row-dots"></span>
                                    <span class="slip-row-value">Rp {{ number_format($total_denda, 0, ',', '.') }}</span>
                                </div>
                                <div class="slip-row subtotal">
                                    <span class="slip-row-label">Total Potongan</span>
                                    <span class="slip-row-dots"></span>
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
                </div>
            @endforeach
        </div>
    </div>
@endsection
