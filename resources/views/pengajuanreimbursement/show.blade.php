@extends('layouts.mobile.app')
@section('content')
    <style>
        body {
            background-color: #f8f9fa !important;
        }
        
        #header-custom {
            background-color: {{ $t['primary'] }};
            background-image: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(0,0,0,0) 100%);
            padding-top: 20px;
            padding-bottom: 65px;
            color: #fff;
            position: relative;
            z-index: 1;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }

        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
        }

        .header-nav .left-actions {
            display: flex;
            align-items: center;
        }
        
        .header-nav a {
            color: #fff !important;
            font-size: 24px;
            display: flex;
            align-items: center;
        }
        
        .header-nav .page-title {
            font-size: 18px;
            font-weight: 700;
            margin-left: 15px;
            letter-spacing: 0.3px;
        }

        .header-content {
            text-align: center;
            padding: 10px 20px 0px;
        }
        
        .header-content .amount-label {
            font-size: 11px;
            font-weight: 600;
            opacity: 0.8;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header-content .amount-value {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        
        #content-section {
            margin-top: -45px;
            padding: 0 16px 100px;
            position: relative;
            z-index: 2;
        }
        
        .detail-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 16px;
        }
        
        .status-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .status-badge.pending { background: #fffbeb; color: #f59e0b; }
        .status-badge.approved { background: #ecfdf5; color: #10b981; }
        .status-badge.rejected { background: #fef2f2; color: #ef4444; }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            margin-top: 2px;
        }
        
        .info-value {
            font-size: 13px;
            color: #1e293b;
            font-weight: 600;
            text-align: right;
            max-width: 65%;
            line-height: 1.4;
        }
        
        .info-value.bold {
            font-size: 14px;
            font-weight: 800;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            margin: 24px 8px 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .item-box {
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        
        .item-title {
            font-size: 14px;
            font-weight: 700;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .item-amount {
            font-size: 15px;
            font-weight: 800;
            color: {{ $t['primary'] }};
        }
        
        .item-desc {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 12px;
        }
        
        .btn-bukti {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: #3b82f6;
            background: #eff6ff;
            padding: 6px 12px;
            border-radius: 8px;
            border: none;
            text-decoration: none !important;
            transition: all 0.2s;
        }
        
        .btn-bukti:active {
            background: #dbeafe;
            transform: scale(0.97);
        }

        /* Modal Image Styles */
        .image-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.85);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .image-modal-overlay.show {
            opacity: 1;
        }
        .image-modal-content {
            background: #fff;
            width: 90%;
            max-width: 400px;
            border-radius: 16px;
            overflow: hidden;
            transform: scale(0.9);
            transition: transform 0.2s ease;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .image-modal-overlay.show .image-modal-content {
            transform: scale(1);
        }
        .image-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .image-modal-title { margin: 0; font-size: 15px; font-weight: 700; color: #1e293b; }
        .image-modal-close { 
            background: #f1f5f9; border: none; font-size: 20px; color: #64748b; 
            width: 32px; height: 32px; border-radius: 50%; display: flex; 
            align-items: center; justify-content: center; transition: all 0.2s;
        }
        .image-modal-close:active { background: #e2e8f0; transform: scale(0.95); }
        .image-modal-body { 
            padding: 0; text-align: center; background: #f8fafc; 
            display: flex; align-items: center; justify-content: center;
            min-height: 200px;
        }
        .image-modal-body img { max-width: 100%; max-height: 70vh; display: block; object-fit: contain; }
        
        /* Timeline Modern */
        .timeline-modern {
            position: relative;
            padding-left: 20px;
        }
        
        .timeline-modern::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 5px;
            bottom: 5px;
            width: 2px;
            background: #e2e8f0;
            border-radius: 2px;
        }
        
        .tl-item {
            position: relative;
            margin-bottom: 24px;
        }
        .tl-item:last-child {
            margin-bottom: 0;
        }
        
        .tl-dot {
            position: absolute;
            left: -20px;
            top: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #cbd5e1;
            border: 2px solid #fff;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.5);
            z-index: 2;
        }
        .tl-dot.approved { background: #10b981; }
        .tl-dot.rejected { background: #ef4444; }
        .tl-dot.pending { background: #f59e0b; }
        
        .tl-content {
            background: #f8fafc;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
        }
        
        .tl-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        
        .tl-name {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }
        
        .tl-date {
            font-size: 10px;
            color: #94a3b8;
            font-weight: 500;
        }
        
        .tl-badge {
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            margin-bottom: 6px;
            display: inline-block;
        }
        .tl-badge.approved { background: #ecfdf5; color: #10b981; }
        .tl-badge.rejected { background: #fef2f2; color: #ef4444; }

        .tl-note {
            font-size: 11px;
            color: #64748b;
            line-height: 1.4;
            margin: 0;
        }

        .btn-batal {
            background: #fff;
            color: #ef4444;
            border: 1px solid #fca5a5;
            font-weight: 700;
            font-size: 14px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            transition: all 0.2s;
        }
        .btn-batal:active {
            background: #fef2f2;
            transform: scale(0.98);
        }
    </style>

    @php
        $status_class = $reimbursement->status == 'P' ? 'pending' : ($reimbursement->status == 'A' ? 'approved' : 'rejected');
        $status_icon = $reimbursement->status == 'P' ? 'time-outline' : ($reimbursement->status == 'A' ? 'checkmark-circle-outline' : 'close-circle-outline');
        $status_text = $reimbursement->status == 'P' ? 'Sedang Diproses' : ($reimbursement->status == 'A' ? 'Telah Disetujui' : 'Ditolak');
    @endphp

    <div id="header-custom">
        <div class="header-nav">
            <div class="left-actions">
                <a href="{{ route('pengajuanreimbursement.index') }}" class="goBack">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                </a>
                <span class="page-title">Detail Pengajuan</span>
            </div>
            @if ($reimbursement->status == 'P')
                <a href="{{ route('pengajuanreimbursement.edit', Crypt::encrypt($reimbursement->id)) }}">
                    <ion-icon name="create-outline"></ion-icon>
                </a>
            @endif
        </div>

        <div class="header-content">
            <div class="amount-label">Total Reimbursement</div>
            <div class="amount-value">Rp {{ number_format($reimbursement->total_nominal, 0, ',', '.') }}</div>
        </div>
    </div>

    <div id="content-section">
        {{-- Summary Card --}}
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3" style="border-bottom: 1px solid #f1f5f9;">
                <div>
                    <div class="text-muted" style="font-size: 10px; font-weight: 600; margin-bottom: 2px;">STATUS PENGAJUAN</div>
                    <div class="status-badge {{ $status_class }}">
                        <ion-icon name="{{ $status_icon }}" style="font-size: 14px;"></ion-icon>
                        {{ $status_text }}
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-muted" style="font-size: 10px; font-weight: 600; margin-bottom: 2px;">JUMLAH ITEM</div>
                    <div style="font-size: 14px; font-weight: 800; color: #1e293b;">{{ $reimbursement->details->count() }} Item</div>
                </div>
            </div>

            <div class="info-row">
                <span class="info-label">No. Pengajuan</span>
                <span class="info-value bold">{{ $reimbursement->no_reimbursement }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Pengajuan</span>
                <span class="info-value">{{ DateToIndo($reimbursement->tanggal_pengajuan) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Keterangan / Tujuan</span>
                <span class="info-value">{{ $reimbursement->catatan ?: '-' }}</span>
            </div>
        </div>

        {{-- Items List --}}
        <div class="section-title">Rincian Item Nota</div>
        
        @foreach ($reimbursement->details as $index => $item)
            @php
                $jenis = DB::table('jenis_reimbursement')->where('kode_jenis_reimburse', $item->kode_jenis_reimburse)->first();
            @endphp
            <div class="item-box">
                <div class="item-header">
                    <div class="item-title">
                        <ion-icon name="receipt" style="color: {{ $t['primary'] }}; opacity: 0.8;"></ion-icon>
                        {{ $jenis ? $jenis->nama_jenis : $item->kode_jenis_reimburse }}
                    </div>
                    <div class="item-amount">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                </div>
                
                <div class="item-desc">{{ $item->keterangan }}</div>
                
                <div class="d-flex justify-content-between align-items-center mt-2 border-top pt-3" style="border-color: #f1f5f9 !important;">
                    <div style="font-size: 11px; color: #94a3b8; font-weight: 500;">
                        Tgl Nota: {{ date('d M Y', strtotime($item->tanggal_transaksi)) }}
                    </div>
                    @if ($item->bukti_file)
                        <button type="button" class="btn-bukti" onclick="showBuktiModal('{{ Storage::url('uploads/reimbursement/' . $item->bukti_file) }}')">
                            <ion-icon name="image-outline"></ion-icon> Lihat Bukti
                        </button>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Approval Timeline --}}
        <div class="section-title mt-4">Jejak Persetujuan</div>
        
        <div class="detail-card">
            @if ($approvals->count() == 0)
                <div class="text-center py-4">
                    <ion-icon name="time-outline" style="font-size: 40px; color: #cbd5e1; margin-bottom: 8px;"></ion-icon>
                    <p class="text-muted mb-0" style="font-size: 13px; font-weight: 500;">Belum ada aksi persetujuan. <br>Menunggu verifikasi pertama.</p>
                </div>
            @else
                <div class="timeline-modern">
                    @foreach ($approvals as $app)
                        @php
                            $tl_dot_class = $app->status == 'approved' ? 'approved' : ($app->status == 'rejected' ? 'rejected' : 'pending');
                        @endphp
                        <div class="tl-item">
                            <div class="tl-dot {{ $tl_dot_class }}"></div>
                            <div class="tl-content">
                                <div class="tl-header">
                                    <div class="tl-name">{{ $app->user_name }}</div>
                                    <div class="tl-date">{{ date('d M y, H:i', strtotime($app->created_at)) }}</div>
                                </div>
                                <div class="tl-badge {{ $tl_dot_class }}">
                                    {{ $app->status == 'approved' ? 'DISETUJUI' : 'DITOLAK' }} (LVL {{ $app->level }})
                                </div>
                                @if($app->keterangan)
                                    <p class="tl-note">"{{ $app->keterangan }}"</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Delete Button --}}
        @if ($reimbursement->status == 'P')
            <form action="{{ route('pengajuanreimbursement.delete', Crypt::encrypt($reimbursement->id)) }}" method="POST" id="formDelete" class="mt-4">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-batal btnDelete">
                    <ion-icon name="trash-outline" style="font-size: 18px;"></ion-icon> Batalkan Pengajuan Ini
                </button>
            </form>
        @endif
    </div>

    {{-- Image Modal Overlay --}}
    <div id="imageModal" class="image-modal-overlay" onclick="if(event.target === this) closeBuktiModal()">
        <div class="image-modal-content">
            <div class="image-modal-header">
                <h5 class="image-modal-title">Foto Bukti Nota</h5>
                <button type="button" class="image-modal-close" onclick="closeBuktiModal()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
            <div class="image-modal-body">
                <img id="modalImage" src="" alt="Memuat Bukti...">
            </div>
        </div>
    </div>

@endsection

@push('myscript')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let defaultHeader = document.querySelector('.appHeader');
        if (defaultHeader) {
            defaultHeader.style.display = 'none';
        }
    });

    // Custom Modal Functions
    window.showBuktiModal = function(url) {
        document.getElementById('modalImage').src = url;
        let modal = document.getElementById('imageModal');
        modal.style.display = 'flex';
        // Trigger reflow
        void modal.offsetWidth;
        modal.classList.add('show');
    };

    window.closeBuktiModal = function() {
        let modal = document.getElementById('imageModal');
        modal.classList.remove('show');
        setTimeout(() => { 
            modal.style.display = 'none'; 
            document.getElementById('modalImage').src = "";
        }, 200);
    };

    $(function() {
        $(".btnDelete").click(function() {
            Swal.fire({
                title: 'Batalkan Pengajuan?',
                text: "Data yang dibatalkan tidak dapat dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tutup'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#formDelete").submit();
                }
            })
        });
    });
</script>
@endpush
