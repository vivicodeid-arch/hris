@extends('layouts.mobile.modern')

@section('title', 'ID Card')

@section('header_left')
    <a href="{{ route('dashboard.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@section('header_right')
    <div class="flex items-center gap-2">
        <button id="download-idcard" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-95 transition-all border-0 outline-none cursor-pointer" title="Simpan ke Galeri">
            <ion-icon name="cloud-download-outline" class="text-lg"></ion-icon>
        </button>
        <button id="flip-trigger" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-95 transition-all border-0 outline-none cursor-pointer" title="Balik Kartu">
            <ion-icon name="sync-outline" class="text-lg"></ion-icon>
        </button>
    </div>
@endsection

@push('mystyle')
    <style>
        /* Modern 3D ID Card Design v4 - Ultimate Flip & Export */
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&family=Alex+Brush&display=swap');

        @php
            $primaryColor = $t['primary'] ?? '#32745e';
            $primaryLight = $t['primary_light'] ?? '#e8f0ed';
            $bgBody = $t['bg_body'] ?? '#f8fafc';
        @endphp

        :root {
            --id-primary: {{ $primaryColor }};
            --id-secondary: {{ $primaryLight }};
        }

        .idcard-page-layout {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 0 40px 0;
            background: {{ $bgBody }};
            min-height: calc(100vh - 60px);
            box-sizing: border-box;
        }

        .idcard-flip-container {
            perspective: 1500px;
            width: 340px;
            height: 600px;
            margin: 0 auto !important;
            position: relative;
            cursor: pointer;
        }

        .idcard-flip-card {
            width: 100%;
            height: 100%;
            position: absolute;
            transform-style: preserve-3d;
            transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* Flipped state trigger */
        .idcard-flip-container.is-flipped .idcard-flip-card {
            transform: rotateY(180deg);
        }

        /* Common styles for front/back sides */
        .idcard-side {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 
                0 30px 60px -15px rgba(0, 0, 0, 0.12),
                0 15px 30px -10px rgba(0, 0, 0, 0.08),
                0 0 0 1px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-sizing: border-box;
        }

        /* Front side styling */
        .idcard-front {
            z-index: 2;
            transform: rotateY(0deg);
        }

        /* Back side styling */
        .idcard-back {
            z-index: 1;
            transform: rotateY(180deg);
            background: #fafcfd;
        }

        /* Front specific designs */
        .card-front-banner {
            height: 160px;
            background: linear-gradient(135deg, var(--id-primary), var(--id-secondary));
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            color: #ffffff;
            padding: 24px 24px 0 24px;
            box-sizing: border-box;
            border-bottom: 4px solid var(--id-primary);
        }


        .company-logo-img {
            height: 32px;
            width: auto;
            object-fit: contain;
            margin-bottom: 6px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));
            z-index: 2;
        }

        .brand-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.3px;
            text-transform: uppercase;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 2;
        }

        .brand-subtitle {
            font-size: 0.6rem;
            color: rgba(255,255,255,0.75);
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 1px;
            z-index: 2;
        }

        /* Profile avatar details */
        .avatar-container {
            width: 110px;
            height: 110px;
            margin: -50px auto 10px auto;
            position: relative;
            z-index: 3;
        }

        .avatar-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            padding: 3px;
            background: linear-gradient(135deg, var(--id-primary), var(--id-secondary));
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .avatar-mask {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #fff;
            padding: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-sizing: border-box;
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background: #f8fafc;
        }

        .name-heading {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.5px;
            text-align: center;
        }

        .job-title-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 8px auto 0 auto;
            padding: 4px 12px;
            background: rgba(var(--color-nav-rgb), 0.05);
            color: var(--id-primary);
            font-size: 0.75rem;
            font-weight: 800;
            border-radius: 12px;
            border: 1px solid rgba(var(--color-nav-rgb), 0.1);
        }



        /* Info columns front - Redesigned to meta grid */
        .idcard-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            padding: 0 24px;
            margin-top: 18px;
            width: 100%;
            box-sizing: border-box;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f8fafc;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        }

        .meta-item:active {
            transform: scale(0.98);
        }

        .meta-label {
            font-size: 0.58rem;
            color: #94a3b8;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .meta-value {
            font-size: 0.85rem;
            color: #334155;
            font-weight: 700;
        }

        /* Front Side Barcode */
        .barcode-front-section {
            margin: auto auto 16px auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            width: 100%;
            padding: 0 24px;
            box-sizing: border-box;
        }

        .barcode-front-box {
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ffffff;
            padding: 10px 16px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
            width: fit-content;
            max-width: 100%;
            box-sizing: border-box;
        }

        .barcode-id-label {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 1px;
        }

        .card-bottom-line {
            height: 8px;
            width: 100%;
            background: linear-gradient(90deg, var(--id-primary), var(--id-secondary));
            margin-top: auto;
        }


        /* Back specific designs */
        .card-back-header {
            padding: 16px 20px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px dashed #e2e8f0;
        }

        .card-back-logo-text {
            font-size: 0.85rem;
            font-weight: 800;
            color: #475569;
            letter-spacing: -0.2px;
            text-transform: uppercase;
        }

        .card-back-label {
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--id-primary);
            background: rgba(var(--color-nav-rgb), 0.05);
            padding: 3px 8px;
            border-radius: 6px;
        }

        .card-back-terms {
            padding: 16px 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .terms-title {
            font-size: 0.7rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .terms-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .terms-item {
            font-size: 0.65rem;
            color: #64748b;
            line-height: 1.35;
            display: flex;
            align-items: flex-start;
            gap: 6px;
        }

        .terms-item::before {
            content: "•";
            color: var(--id-primary);
            font-weight: 800;
        }

        .barcode-section {
            padding: 12px 20px;
            background: #ffffff;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            text-align: center;
            margin-top: auto;
        }

        .barcode-box {
            display: inline-flex;
            justify-content: center;
            background: #fff;
            padding: 6px;
            border-radius: 8px;
        }

        .barcode-id-label {
            display: block;
            margin-top: 4px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.75rem;
            letter-spacing: 3px;
            color: #64748b;
            font-weight: 600;
        }

        .card-back-footer {
            padding: 12px 20px 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            background: #f8fafc;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }

        .company-address {
            font-size: 0.55rem;
            color: #94a3b8;
            line-height: 1.3;
            max-width: 140px;
            text-align: left;
        }

        .signature-block {
            text-align: center;
            min-width: 90px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signature-image {
            font-family: 'Alex Brush', cursive;
            font-size: 1.5rem;
            color: var(--id-primary);
            margin-bottom: -2px;
            line-height: 1;
        }

        .signature-line {
            width: 100%;
            height: 1px;
            background: #cbd5e1;
            margin-bottom: 4px;
        }

        .signature-title {
            font-size: 0.55rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }



        /* Hidden Export Preview Sheet */
        .export-preview-sheet {
            display: flex;
            gap: 24px;
            padding: 24px;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            border-radius: 0;
            box-sizing: border-box;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@section('content')
    <div class="idcard-page-layout">
        <!-- 3D CARD WRAPPER -->
        <div class="idcard-flip-container" id="idcard-touch-area">
            <div class="idcard-flip-card">
                <!-- FRONT SIDE -->
                <div class="idcard-side idcard-front">
                    <div class="card-front-banner">
                        @if ($generalsetting->logo && Storage::exists('public/logo/' . $generalsetting->logo))
                            <img src="{{ asset('storage/logo/' . $generalsetting->logo) }}" class="company-logo-img" alt="Logo">
                        @else
                            <img src="https://placehold.co/100x100?text=LOGO" class="company-logo-img" alt="Logo">
                        @endif
                        <span class="brand-name">{{ $generalsetting->nama_perusahaan ?? 'E-Presensi' }}</span>
                        <span class="brand-subtitle">Employee Pass</span>
                    </div>

                    <!-- Profile Photo -->
                    <div class="avatar-container">
                        <div class="avatar-ring">
                            <div class="avatar-mask">
                                @if (!empty($karyawan->foto))
                                    <img src="{{ getfotoKaryawan($karyawan->foto) }}" class="avatar-img" alt="Employee Photo">
                                @else
                                    <img src="{{ asset('assets/template/img/sample/avatar/avatar1.jpg') }}" class="avatar-img" alt="Default Photo">
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Name and Badge -->
                    <h2 class="name-heading">{{ textUpperCase($karyawan->nama_karyawan) }}</h2>
                    <div class="job-title-badge">
                        <ion-icon name="ribbon-outline"></ion-icon>
                        <span>{{ $karyawan->nama_jabatan }}</span>
                    </div>

                    <!-- Details Grid -->
                    <div class="idcard-meta-grid">
                        <div class="meta-item">
                            <span class="meta-label">NIK</span>
                            <span class="meta-value">{{ $karyawan->nik }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Departemen</span>
                            <span class="meta-value">{{ $karyawan->nama_dept }}</span>
                        </div>
                    </div>

                    <!-- Barcode Section on the Front -->
                    <div class="barcode-front-section">
                        <div class="barcode-front-box">
                            {!! DNS1D::getBarcodeHTML($karyawan->nik, 'C128', 1.4, 38, 'black') !!}
                        </div>
                        <span class="barcode-id-label">{{ $karyawan->nik }}</span>
                    </div>

                    <!-- Brand accent line -->
                    <div class="card-bottom-line"></div>
                </div>


                <!-- BACK SIDE -->
                <div class="idcard-side idcard-back">
                    <div class="card-back-header">
                        <span class="card-back-logo-text">{{ $generalsetting->nama_perusahaan ?? 'E-Presensi' }}</span>
                        <span class="card-back-label">T&C Pass</span>
                    </div>

                    <div class="card-back-terms">
                        <span class="terms-title">Syarat & Ketentuan</span>
                        <ul class="terms-list">
                            <li class="terms-item">Kartu ini adalah properti resmi perusahaan dan wajib digunakan selama jam operasional kerja.</li>
                            <li class="terms-item">Penggunaan kartu ini sepenuhnya tunduk pada aturan & tata tertib kerja perusahaan.</li>
                            <li class="terms-item">Jika kartu ini hilang atau ditemukan, harap segera menyerahkan atau melaporkannya ke bagian HRD.</li>
                        </ul>
                    </div>

                    <!-- Barcode Section on the Back -->
                    <div class="barcode-section">
                        <div class="barcode-box">
                            {!! DNS1D::getBarcodeHTML($karyawan->nik, 'C128', 1.6, 42, 'black') !!}
                        </div>
                        <span class="barcode-id-label">{{ $karyawan->nik }}</span>
                    </div>

                    <!-- Back Footer -->
                    <div class="card-back-footer">
                        <div class="company-address">
                            <strong>{{ $generalsetting->nama_perusahaan ?? 'E-Presensi' }}</strong><br>
                            Sistem Payroll & Absensi GPS Modern
                        </div>
                        <div class="signature-block">
                            <span class="signature-image">HR Dept</span>
                            <div class="signature-line"></div>
                            <span class="signature-title">Authorized</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('myscript')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var container = document.getElementById('idcard-touch-area');
            var flipTrigger = document.getElementById('flip-trigger');
            var btnDownload = document.getElementById('download-idcard');

            // Handle Card Flipping on Click/Tap Card
            if (container) {
                container.addEventListener('click', function() {
                    container.classList.toggle('is-flipped');
                });
            }

            // Handle Flip Button Trigger
            if (flipTrigger) {
                flipTrigger.addEventListener('click', function() {
                    if (container) {
                        container.classList.toggle('is-flipped');
                    }
                });
            }

            // High-fidelity Download/Export side-by-side
            if (btnDownload) {
                btnDownload.addEventListener('click', function() {
                    var initialLabel = btnDownload.innerHTML;
                    btnDownload.innerHTML = '<ion-icon name="sync-outline" class="animate-spin text-lg"></ion-icon>';
                    btnDownload.disabled = true;

                    // Create offline rendering layout wrapper
                    var exportWrapper = document.createElement('div');
                    exportWrapper.className = 'export-preview-sheet';
                    exportWrapper.style.position = 'fixed';
                    exportWrapper.style.left = '-9999px';
                    exportWrapper.style.top = '-9999px';
                    exportWrapper.style.display = 'flex';
                    exportWrapper.style.gap = '24px';
                    exportWrapper.style.padding = '30px';
                    exportWrapper.style.background = 'linear-gradient(135deg, #f1f5f9, #e2e8f0)';
                    exportWrapper.style.borderRadius = '0'; // Flat boundary
                    exportWrapper.style.width = '770px'; // 340px * 2 + 24px + 60px padding
                    exportWrapper.style.height = '660px'; // 600px + 60px padding

                    // Clone front & back sides
                    var frontSide = document.querySelector('.idcard-front');
                    var backSide = document.querySelector('.idcard-back');

                    if (!frontSide || !backSide) {
                        alert('Elemen ID Card tidak ditemukan!');
                        btnDownload.innerHTML = initialLabel;
                        btnDownload.disabled = false;
                        return;
                    }

                    var frontClone = frontSide.cloneNode(true);
                    var backClone = backSide.cloneNode(true);

                    // Reset positioning & 3D transforms for flat side-by-side export
                    frontClone.style.position = 'relative';
                    frontClone.style.transform = 'none';
                    frontClone.style.width = '340px';
                    frontClone.style.height = '600px';
                    frontClone.style.boxShadow = '0 15px 30px rgba(0,0,0,0.06)';
                    
                    backClone.style.position = 'relative';
                    backClone.style.transform = 'none';
                    backClone.style.width = '340px';
                    backClone.style.height = '600px';
                    backClone.style.boxShadow = '0 15px 30px rgba(0,0,0,0.06)';

                    // Append clones into the offscreen layout wrapper
                    exportWrapper.appendChild(frontClone);
                    exportWrapper.appendChild(backClone);
                    document.body.appendChild(exportWrapper);

                    // Render with html2canvas (No 3D bugs, perfect scale, gorgeous output)
                    html2canvas(exportWrapper, {
                        backgroundColor: '#e2e8f0',
                        scale: 3, // Premium high res output (2220px x 1650px)
                        useCORS: true,
                        logging: false
                    }).then(function(canvas) {
                        var link = document.createElement('a');
                        link.download = 'IDCard_{{ $karyawan->nik }}.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();

                        // Clean up export wrapper
                        document.body.removeChild(exportWrapper);

                        // Reset button
                        btnDownload.innerHTML = initialLabel;
                        btnDownload.disabled = false;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Disimpan!',
                                text: 'Desain depan-belakang ID Card sudah tersimpan di galeri.',
                                showConfirmButton: false,
                                timer: 2500,
                                customClass: { popup: 'rounded-[1.5rem]' }
                            });
                        }
                    }).catch(function(e) {
                        console.error(e);
                        document.body.removeChild(exportWrapper);
                        btnDownload.innerHTML = initialLabel;
                        btnDownload.disabled = false;
                        alert('Gagal mengunduh ID Card: ' + e.message);
                    });
                });
            }
        });
    </script>
@endpush
