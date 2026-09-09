@extends('layouts.mobile.app')
@section('content')
    <style>
        #header-section {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        #content-section {
            margin-top: 75px;
            padding: 15px 16px 80px 16px;
            position: relative;
            z-index: 1;
            background-color: #f7f9fa;
            min-height: 100vh;
        }

        .approval-summary {
            background: linear-gradient(135deg, #1f4d3d 0%, #32745e 100%);
            border-radius: 16px;
            padding: 20px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(50, 116, 94, 0.25);
            position: relative;
            overflow: hidden;
        }

        .approval-summary::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            pointer-events: none;
        }

        .approval-summary .admin-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }

        .approval-summary .admin-label {
            font-size: 12px;
            opacity: 0.85;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .approval-summary .count-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            padding: 12px 18px;
            text-align: center;
            min-width: 80px;
        }

        .approval-summary .count-number {
            font-size: 32px;
            font-weight: 800;
            line-height: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .approval-summary .count-label {
            font-size: 11px;
            font-weight: 600;
            opacity: 0.9;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 0 10px 4px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.04);
            margin-bottom: 12px;
        }

        .section-title ion-icon {
            font-size: 20px;
        }

        .section-title .section-count {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 700;
            margin-left: auto;
        }

        .izin-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 12px;
            border: 1px solid rgba(0, 0, 0, 0.04);
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .izin-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
            border-color: rgba(50, 116, 94, 0.15);
        }

        .izin-card .card-inner {
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .izin-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .izin-icon ion-icon {
            font-size: 24px;
        }

        .izin-info {
            flex: 1;
            min-width: 0;
        }

        .izin-info .nama {
            font-size: 15px;
            font-weight: 700;
            color: #1a202c;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.2px;
        }

        .izin-info .detail {
            font-size: 12px;
            color: #718096;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .izin-info .keterangan {
            font-size: 12px;
            color: #4a5568;
            margin-top: 6px;
            padding: 6px 10px;
            background: #f7f9fa;
            border-radius: 8px;
            border-left: 3px solid #cbd5e0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .izin-action {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .izin-action .step-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .btn-proses {
            background: #32745e;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(50, 116, 94, 0.2);
            transition: all 0.2s ease;
        }

        .btn-proses:hover, .btn-proses:active {
            background: #235343;
            color: white;
            text-decoration: none;
            transform: scale(1.03);
            box-shadow: 0 6px 14px rgba(50, 116, 94, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(0, 0, 0, 0.04);
            margin-top: 10px;
        }

        .empty-state ion-icon {
            font-size: 80px;
            color: #32745e;
            opacity: 0.9;
        }

        .empty-state p {
            margin-top: 14px;
            font-size: 15px;
            font-weight: 600;
            color: #4a5568;
            line-height: 1.5;
        }

        /* Type colors */
        .type-absen { color: #3182ce; }
        .type-absen-bg { background: rgba(49, 130, 206, 0.08); }
        .type-sakit { color: #e53e3e; }
        .type-sakit-bg { background: rgba(229, 62, 62, 0.08); }
        .type-cuti { color: #dd6b20; }
        .type-cuti-bg { background: rgba(221, 107, 32, 0.08); }
        .type-dinas { color: #319795; }
        .type-dinas-bg { background: rgba(49, 151, 149, 0.08); }
        .type-reimburse { color: #805ad5; }
        .type-reimburse-bg { background: rgba(128, 90, 213, 0.08); }
    </style>

    <div id="header-section">
        <div class="appHeader bg-primary text-light">
            <div class="left">
                <a href="{{ route('shortcut.index') }}" class="headerButton goBack">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                </a>
            </div>
            <div class="pageTitle">Approval Delegasi</div>
            <div class="right"></div>
        </div>
    </div>

    <div id="content-section">
        <div class="row" style="margin-top: 10px">
            <div class="col" style="padding: 0 15px;">

                {{-- Summary Card --}}
                <div class="approval-summary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="admin-label">
                                <ion-icon name="shield-checkmark-outline" style="vertical-align: middle;"></ion-icon>
                                Atas nama
                            </div>
                            <div class="admin-name">{{ $admin->name }}</div>
                            <div class="admin-label">{{ $admin->getRoleNames()->first() }}</div>
                        </div>
                        <div class="count-badge">
                            <div class="count-number">{{ $totalPending }}</div>
                            <div class="count-label">Pending</div>
                        </div>
                    </div>
                </div>

                {{-- Empty State --}}
                @if($totalPending == 0)
                    <div class="empty-state">
                        <ion-icon name="checkmark-done-circle-outline"></ion-icon>
                        <p>Semua izin sudah diproses.<br>Tidak ada yang menunggu approval.</p>
                    </div>
                @endif

                {{-- Izin Absen --}}
                @if($pendingIzinAbsen->count() > 0)
                    <div class="section-title type-absen">
                        <ion-icon name="calendar-outline"></ion-icon>
                        Izin Absen
                        <span class="section-count type-absen">{{ $pendingIzinAbsen->count() }}</span>
                    </div>
                    @foreach($pendingIzinAbsen as $izin)
                        <div class="izin-card">
                            <div class="card-inner">
                                <div class="izin-icon type-absen-bg">
                                    <ion-icon name="calendar-outline" class="type-absen"></ion-icon>
                                </div>
                                <div class="izin-info">
                                    <div class="nama">{{ $izin->nama_karyawan }}</div>
                                    <div class="detail">
                                        {{ $izin->nama_dept ?? '-' }} • {{ date('d/m/Y', strtotime($izin->dari)) }} - {{ date('d/m/Y', strtotime($izin->sampai)) }}
                                    </div>
                                    <div class="keterangan">{{ $izin->keterangan }}</div>
                                </div>
                                <div class="izin-action">
                                    <div class="step-badge" style="background: rgba(30,144,255,0.1); color: #1e90ff;">Tahap {{ $izin->approval_step }}</div>
                                    <a href="{{ route('karyawan-approval.izinabsen.approve', Crypt::encrypt($izin->kode_izin)) }}" class="btn-proses">
                                        <ion-icon name="checkmark-outline"></ion-icon> Proses
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Izin Sakit --}}
                @if($pendingIzinSakit->count() > 0)
                    <div class="section-title type-sakit">
                        <ion-icon name="medkit-outline"></ion-icon>
                        Izin Sakit
                        <span class="section-count type-sakit">{{ $pendingIzinSakit->count() }}</span>
                    </div>
                    @foreach($pendingIzinSakit as $izin)
                        <div class="izin-card">
                            <div class="card-inner">
                                <div class="izin-icon type-sakit-bg">
                                    <ion-icon name="medkit-outline" class="type-sakit"></ion-icon>
                                </div>
                                <div class="izin-info">
                                    <div class="nama">{{ $izin->nama_karyawan }}</div>
                                    <div class="detail">
                                        {{ $izin->nama_dept ?? '-' }} • {{ date('d/m/Y', strtotime($izin->dari)) }} - {{ date('d/m/Y', strtotime($izin->sampai)) }}
                                    </div>
                                    <div class="keterangan">{{ $izin->keterangan }}</div>
                                </div>
                                <div class="izin-action">
                                    <div class="step-badge" style="background: rgba(255,99,132,0.1); color: #ff6384;">Tahap {{ $izin->approval_step }}</div>
                                    <a href="{{ route('karyawan-approval.izinsakit.approve', Crypt::encrypt($izin->kode_izin_sakit)) }}" class="btn-proses">
                                        <ion-icon name="checkmark-outline"></ion-icon> Proses
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Izin Cuti --}}
                @if($pendingIzinCuti->count() > 0)
                    <div class="section-title type-cuti">
                        <ion-icon name="airplane-outline"></ion-icon>
                        Izin Cuti
                        <span class="section-count type-cuti">{{ $pendingIzinCuti->count() }}</span>
                    </div>
                    @foreach($pendingIzinCuti as $izin)
                        <div class="izin-card">
                            <div class="card-inner">
                                <div class="izin-icon type-cuti-bg">
                                    <ion-icon name="airplane-outline" class="type-cuti"></ion-icon>
                                </div>
                                <div class="izin-info">
                                    <div class="nama">{{ $izin->nama_karyawan }}</div>
                                    <div class="detail">
                                        {{ $izin->nama_dept ?? '-' }} • {{ date('d/m/Y', strtotime($izin->dari)) }} - {{ date('d/m/Y', strtotime($izin->sampai)) }}
                                    </div>
                                    <div class="keterangan">{{ $izin->keterangan }}</div>
                                </div>
                                <div class="izin-action">
                                    <div class="step-badge" style="background: rgba(255,159,64,0.1); color: #ff9f40;">Tahap {{ $izin->approval_step }}</div>
                                    <a href="{{ route('karyawan-approval.izincuti.approve', Crypt::encrypt($izin->kode_izin_cuti)) }}" class="btn-proses">
                                        <ion-icon name="checkmark-outline"></ion-icon> Proses
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Izin Dinas --}}
                @if($pendingIzinDinas->count() > 0)
                    <div class="section-title type-dinas">
                        <ion-icon name="briefcase-outline"></ion-icon>
                        Izin Dinas
                        <span class="section-count type-dinas">{{ $pendingIzinDinas->count() }}</span>
                    </div>
                    @foreach($pendingIzinDinas as $izin)
                        <div class="izin-card">
                            <div class="card-inner">
                                <div class="izin-icon type-dinas-bg">
                                    <ion-icon name="briefcase-outline" class="type-dinas"></ion-icon>
                                </div>
                                <div class="izin-info">
                                    <div class="nama">{{ $izin->nama_karyawan }}</div>
                                    <div class="detail">
                                        {{ $izin->nama_dept ?? '-' }} • {{ date('d/m/Y', strtotime($izin->dari)) }} - {{ date('d/m/Y', strtotime($izin->sampai)) }}
                                    </div>
                                    <div class="keterangan">{{ $izin->keterangan }}</div>
                                </div>
                                <div class="izin-action">
                                    <div class="step-badge" style="background: rgba(50,116,94,0.1); color: #32745e;">Tahap {{ $izin->approval_step }}</div>
                                    <a href="{{ route('karyawan-approval.izindinas.approve', Crypt::encrypt($izin->kode_izin_dinas)) }}" class="btn-proses">
                                        <ion-icon name="checkmark-outline"></ion-icon> Proses
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- Reimbursement --}}
                @if($pendingReimbursement->count() > 0)
                    <div class="section-title type-reimburse">
                        <ion-icon name="wallet-outline"></ion-icon>
                        Reimbursement
                        <span class="section-count type-reimburse">{{ $pendingReimbursement->count() }}</span>
                    </div>
                    @foreach($pendingReimbursement as $r)
                        <div class="izin-card">
                            <div class="card-inner">
                                <div class="izin-icon type-reimburse-bg">
                                    <ion-icon name="wallet-outline" class="type-reimburse"></ion-icon>
                                </div>
                                <div class="izin-info">
                                    <div class="nama">{{ $r->nama_karyawan }}</div>
                                    <div class="detail">
                                        {{ $r->nama_dept ?? '-' }} • {{ $r->no_reimbursement }}
                                    </div>
                                    <div class="keterangan">Total: Rp {{ number_format($r->total_nominal, 0, ',', '.') }}</div>
                                </div>
                                <div class="izin-action">
                                    <div class="step-badge" style="background: rgba(115, 103, 240, 0.1); color: #7367f0;">Tahap {{ $r->approval_step }}</div>
                                    <a href="{{ route('karyawan-approval.reimbursement.approve', Crypt::encrypt($r->no_reimbursement)) }}" class="btn-proses">
                                        <ion-icon name="checkmark-outline"></ion-icon> Proses
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

            </div>
        </div>
    </div>
@endsection
