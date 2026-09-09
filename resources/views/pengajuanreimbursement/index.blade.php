@extends('layouts.mobile.app')
@section('content')
    <style>
        body {
            background-color: #f8f9fa !important;
        }
        
        #header-custom {
            background-color: {{ $t['primary'] }};
            background-image: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(0,0,0,0) 100%);
            padding-top: 20px; /* Safe area / status bar padding */
            padding-bottom: 50px;
            color: #fff;
            position: relative;
            z-index: 1;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .header-nav {
            display: flex;
            align-items: center;
            padding: 15px 20px;
        }

        .header-nav a {
            color: #fff !important;
            font-size: 24px;
            margin-right: 15px;
            display: flex;
            align-items: center;
        }

        .header-nav .page-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .total-section {
            text-align: center;
            padding: 10px 20px 20px;
        }

        .total-section .title {
            font-size: 13px;
            font-weight: 600;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .total-section .amount {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }

        .total-section .subtitle {
            font-size: 11px;
            opacity: 0.8;
            line-height: 1.4;
        }

        #content-section {
            margin-top: -30px;
            padding: 0 16px 80px 16px;
            position: relative;
            z-index: 2;
        }

        /* Filter Card */
        .filter-card {
            background: #fff;
            border-radius: 12px;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .filter-card a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .filter-card a:active {
            background-color: #f1f5f9;
        }

        .filter-card .icon-arrow {
            color: #64748b;
            font-size: 18px;
        }

        .filter-card .filter-text {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }

        /* Item Card */
        .reimburse-card {
            background: #fff;
            border-radius: 0; /* Modern clean edge, or slightly rounded */
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            border: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: all 0.2s;
        }

        .reimburse-card:active {
            transform: scale(0.98);
            background: #f8fafc;
        }

        .card-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-row.top {
            margin-bottom: 4px;
        }

        .no-bukti {
            font-size: 14px;
            font-weight: 800;
            color: #334155;
            letter-spacing: 0.3px;
        }

        .status-badge {
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .status-badge.approved { color: #10b981; }
        .status-badge.pending { color: #f59e0b; }
        .status-badge.rejected { color: #ef4444; }

        .catatan-text {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }

        .date-text {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }

        .amount-text {
            font-size: 16px;
            font-weight: 800;
            color: {{ $t['primary'] }}; /* Sync with theme color */
        }

        .fab-button {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 56px;
            height: 56px;
            background: {{ $t['primary'] }};
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px {{ $t['primary'] }}60;
            z-index: 999;
            transition: all 0.2s;
        }

        .fab-button:active {
            transform: scale(0.95);
        }

        .fab-button ion-icon {
            color: #fff;
            font-size: 28px;
        }
        
        .alert-empty {
            background: #fff;
            padding: 30px 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            border: 1px solid #f1f5f9;
        }
    </style>

    @php
        // Calculate Date Filter Variables
        $filter_dari = request('dari') ?: date('Y-m-01');
        $filter_sampai = request('sampai') ?: date('Y-m-t');

        $prev_dari = date('Y-m-01', strtotime('-1 month', strtotime($filter_dari)));
        $prev_sampai = date('Y-m-t', strtotime('-1 month', strtotime($filter_dari)));

        $next_dari = date('Y-m-01', strtotime('+1 month', strtotime($filter_dari)));
        $next_sampai = date('Y-m-t', strtotime('+1 month', strtotime($filter_dari)));

        $tgl_dari_text = date('d', strtotime($filter_dari));
        $tgl_sampai_text = date('d', strtotime($filter_sampai));
        
        // Translate month manually
        $eng_months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        $idn_months = array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
        $bln_text = str_replace($eng_months, $idn_months, date('M Y', strtotime($filter_dari)));
        
        $display_text = "$tgl_dari_text sd $tgl_sampai_text $bln_text";

        // Calculate total reimbursement for this period as display
        $user_nik = auth()->user()->karyawan->nik ?? (auth()->user()->userkaryawan->nik ?? '');
        $total_amount_period = \App\Models\Reimbursement::where('nik', $user_nik)
                             ->whereBetween('tanggal_pengajuan', [$filter_dari, $filter_sampai])
                             ->where('status', '!=', 'R') // Exclude rejected
                             ->sum('total_nominal');
    @endphp

    <div id="header-custom">
        <div class="header-nav">
            <a href="{{ route('dashboard.index') }}" class="goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
            <span class="page-title">Reimbursement</span>
        </div>

        <div class="total-section">
            <div class="title">Total reimbursement bulan ini</div>
            <div class="amount">Rp {{ number_format($total_amount_period, 0, ',', '.') }}</div>
            <div class="subtitle">
                Klik pengajuan untuk melihat detail<br>
                Hanya menampilkan data yang tidak ditolak
            </div>
        </div>
    </div>

    <div id="content-section">
        <!-- Filter Card -->
        <div class="filter-card">
            <a href="{{ route('pengajuanreimbursement.index', ['dari' => $prev_dari, 'sampai' => $prev_sampai]) }}">
                <ion-icon name="arrow-back-outline" class="icon-arrow"></ion-icon>
            </a>
            <span class="filter-text">{{ $display_text }}</span>
            <a href="{{ route('pengajuanreimbursement.index', ['dari' => $next_dari, 'sampai' => $next_sampai]) }}">
                <ion-icon name="arrow-forward-outline" class="icon-arrow"></ion-icon>
            </a>
        </div>

        @if ($reimbursement->count() == 0)
            <div class="alert-empty">
                <ion-icon name="receipt-outline" style="font-size: 48px; color: #cbd5e1; margin-bottom: 10px;"></ion-icon>
                <p class="text-muted mb-0" style="font-size: 14px; font-weight: 500;">Belum ada pengajuan reimbursement.</p>
            </div>
        @endif

        @foreach ($reimbursement as $d)
            @php
                $status_class = $d->status == 'P' ? 'pending' : ($d->status == 'A' ? 'approved' : 'rejected');
                $status_icon = $d->status == 'P' ? 'time-outline' : ($d->status == 'A' ? 'checkmark-outline' : 'close-outline');
                $status_text = $d->status == 'P' ? 'Pending' : ($d->status == 'A' ? 'Disetujui' : 'Ditolak');
                
                // Format tanggal "01 Mar 2021"
                $tgl_formatted = date('d M Y', strtotime($d->tanggal_pengajuan));
            @endphp
            
            <div class="reimburse-card" onclick="window.location.href='{{ route('pengajuanreimbursement.show', Crypt::encrypt($d->id)) }}'">
                <div class="card-row top">
                    <div class="no-bukti">{{ $d->no_reimbursement }}</div>
                    <div class="status-badge {{ $status_class }}">
                        <ion-icon name="{{ $status_icon }}"></ion-icon>
                        {{ $status_text }}
                    </div>
                </div>
                
                <div class="card-row bottom">
                    <div class="details-left">
                        <div class="catatan-text">{{ $d->catatan }}</div>
                        <div class="date-text">{{ $tgl_formatted }}</div>
                    </div>
                    <div class="amount-text">
                        Rp {{ number_format($d->total_nominal, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-4" style="padding-bottom: 20px;">
            {{ $reimbursement->links() }}
        </div>
    </div>

    {{-- FAB Button Custom --}}
    <a href="{{ route('pengajuanreimbursement.create') }}" class="fab-button">
        <ion-icon name="add-outline"></ion-icon>
    </a>

@endsection

@push('myscript')
    <script>
        // Sembunyikan default appHeader karena kita menggunakan custom header
        document.addEventListener('DOMContentLoaded', function() {
            let defaultHeader = document.querySelector('.appHeader');
            if (defaultHeader) {
                defaultHeader.style.display = 'none';
            }
        });
    </script>
@endpush
