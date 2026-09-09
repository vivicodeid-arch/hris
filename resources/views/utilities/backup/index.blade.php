@extends('layouts.app')
@section('titlepage', 'Backup Data')

@section('content')
@section('navigasi')
    <span>Utilities > Backup Data</span>
@endsection

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="font-weight-bold mb-1">
                        <i class="ti ti-database-export me-2 text-primary"></i>Backup & Restore Database
                    </h4>
                    <p class="text-muted mb-0">Download salinan database atau pulihkan data sistem secara keseluruhan</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Information Alert -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm" role="alert" style="background-color: #e7f1ff; border-left: 4px solid #0d6efd !important;">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="ti ti-info-circle fs-4 text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="alert-heading mb-2 text-primary">Informasi Backup & Restore</h5>
                        <p class="mb-2" style="color: #495057;"><strong>Backup:</strong> Fitur ini akan menghasilkan file `.sql` yang berisi salinan lengkap data saat ini.</p>
                        <p class="mb-2" style="color: #495057;"><strong>Restore:</strong> Mengembalikan data sistem ke kondisi saat file `.sql` dibuat. <strong class="text-danger">Peringatan:</strong> Proses ini akan menimpa seluruh data yang ada saat ini!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- BACKUP SECTION -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 text-center h-100" style="border-radius: 12px; transition: all 0.3s ease;">
                <div class="card-body p-5 d-flex flex-column">
                    <div class="avatar avatar-xl rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center"
                        style="background-color: rgba(13, 110, 253, 0.15); width: 6rem; height: 6rem; border: 2px solid rgba(13, 110, 253, 0.2);">
                        <i class="ti ti-download text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="mb-3 font-weight-bold" style="color: #212529;">Download Backup Database</h4>
                    <p class="text-muted mb-4 pb-2 flex-grow-1" style="font-size: 0.95rem; line-height: 1.6;">
                        Simpan salinan data Anda di tempat yang aman. Disarankan untuk melakukan backup secara berkala untuk menghindari kehilangan data yang tidak terduga.
                    </p>
                    
                    <a href="{{ route('backup.download') }}" class="btn btn-primary btn-lg w-100 font-weight-bold mt-auto" id="btn-download-backup"
                        style="padding: 0.8rem 1.5rem; font-size: 1.05rem; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3); border-radius: 8px;">
                        <i class="ti ti-cloud-download me-2" style="font-size: 1.2rem;"></i> Mulai Backup Sekarang
                    </a>
                </div>
            </div>
        </div>

        <!-- RESTORE SECTION -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 text-center h-100" style="border-radius: 12px; transition: all 0.3s ease;">
                <div class="card-body p-5 d-flex flex-column">
                    <div class="avatar avatar-xl rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center"
                        style="background-color: rgba(220, 53, 69, 0.15); width: 6rem; height: 6rem; border: 2px solid rgba(220, 53, 69, 0.2);">
                        <i class="ti ti-upload text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="mb-3 font-weight-bold" style="color: #212529;">Restore Database</h4>
                    <p class="text-muted mb-3 flex-grow-1" style="font-size: 0.95rem; line-height: 1.6;">
                        Pulihkan data dari file `.sql` backup sebelumnya. <br>Tindakan ini akan menimpa data yang ada saat ini.
                    </p>
                    
                    <form action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data" id="restore-form" class="mt-auto">
                        @csrf
                        <div class="mb-3 text-start">
                            <label for="backup_file" class="form-label font-weight-semibold">Pilih File Backup (.sql)</label>
                            <input class="form-control" type="file" id="backup_file" name="backup_file" accept=".sql,.txt" required>
                            @error('backup_file')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="button" class="btn btn-danger btn-lg w-100 font-weight-bold" id="btn-restore-confirm"
                            style="padding: 0.8rem 1.5rem; font-size: 1.05rem; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3); border-radius: 8px;">
                            <i class="ti ti-database-import me-2" style="font-size: 1.2rem;"></i> Mulai Restore Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<style>
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.8rem 1.5rem rgba(0, 0, 0, 0.1) !important;
    }
    
    #btn-download-backup {
        transition: all 0.3s ease;
    }
    
    #btn-download-backup:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4) !important;
    }
</style>

<script>
    $(document).ready(function() {
        $('#btn-download-backup').click(function() {
            var $btn = $(this);
            var originalText = $btn.html();
            
            // Mengubah tombol menjadi state loading sementara file di-download
            $btn.html('<div class="spinner-border spinner-border-sm me-2" role="status"></div> Sedang Memproses...');
            $btn.addClass('disabled').css('pointer-events', 'none');
            
            // Toast notifikasi
            Swal.fire({
                title: 'Sedang Mempersiapkan Backup...',
                text: 'Mohon tunggu sementara sistem menghasilkan file backup Anda.',
                icon: 'info',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            // Kembalikan tombol ke keadaan semula setelah beberapa detik (asumsi file mulai terdownload)
            setTimeout(function() {
                $btn.html(originalText);
                $btn.removeClass('disabled').css('pointer-events', 'auto');
            }, 4000);
        });

        $('#btn-restore-confirm').click(function() {
            var fileInput = $('#backup_file');
            if (fileInput.get(0).files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pilih file backup (.sql) terlebih dahulu!'
                });
                return;
            }

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Proses ini akan menimpa SEMUA data di database saat ini dengan data dari file backup. Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Restore Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang Merestore...',
                        text: 'Mohon jangan tutup halaman ini.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    $('#restore-form').submit();
                }
            })
        });
    });
</script>
@endpush
