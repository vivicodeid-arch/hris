@extends('layouts.app')
@section('titlepage', 'Data Resign Karyawan')

@section('content')
@section('navigasi')
    <span>Resign Karyawan</span>
@endsection
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <a href="#" class="btn btn-primary" id="btnTambah"><i class="fa fa-plus me-2"></i> Tambah Data Resign</a>
            </div>

            <!-- Modal Tambah Data -->
            <div class="modal fade" id="modalInput" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Data Resign</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="load-form">
                            <!-- Form content will be loaded here via AJAX -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('resign.index') }}" method="GET">
                            <div class="row g-2">
                                <div class="col-lg-4 col-sm-12">
                                    <x-input-with-icon label="Cari Nama Karyawan" value="{{ Request('nama_karyawan') }}" name="nama_karyawan"
                                        icon="ti ti-search" hideLabel />
                                </div>
                                <div class="col-lg-3 col-sm-12">
                                    <select name="kode_cabang" class="form-select">
                                        <option value="">-- Semua Cabang --</option>
                                        @foreach ($cabang as $c)
                                            <option value="{{ $c->kode_cabang }}" {{ Request('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                                                {{ $c->nama_cabang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-sm-12">
                                    <select name="kode_dept" class="form-select">
                                        <option value="">-- Semua Departemen --</option>
                                        @foreach ($departemen as $d)
                                            <option value="{{ $d->kode_dept }}" {{ Request('kode_dept') == $d->kode_dept ? 'selected' : '' }}>
                                                {{ $d->nama_dept }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-sm-12">
                                    <button class="btn btn-primary w-100"><i class="ti ti-search me-1"></i> Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        @forelse ($resign as $d)
                        <div class="card mb-2 shadow-sm border">
                            <div class="card-body p-2">
                                <div class="row align-items-center">
                                    <!-- Avatar -->
                                    <div class="col-md-1 text-center">
                                        @if (!empty($d->karyawan->foto) && Storage::disk('public')->exists('/karyawan/' . $d->karyawan->foto))
                                            <img src="{{ getfotoKaryawan($d->karyawan->foto) }}" alt="Avatar"
                                                class="rounded-circle"
                                                style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #e9ecef;">
                                        @else
                                            <img src="{{ asset('assets/img/avatars/No_Image_Available.jpg') }}"
                                                alt="No Image" class="rounded-circle"
                                                style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #e9ecef;">
                                        @endif
                                    </div>
                                    <!-- Identity -->
                                    <div class="col-md-4">
                                        <div class="fw-bold text-dark" style="font-size: 14px;">
                                            {{ $d->karyawan->nama_karyawan ?? 'Unknown' }}
                                            <span class="text-muted fw-normal" style="font-size: 12px;">({{ $d->nik }})</span>
                                        </div>
                                        <div class="mt-1">
                                            <span class="text-muted" style="font-size: 12px;">
                                                <i class="ti ti-building me-1"></i> {{ $d->karyawan->cabang->nama_cabang ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                     <!-- Resign Details -->
                                     <div class="col-md-3">
                                         <div class="text-danger fw-bold" style="font-size: 13px;">
                                             <i class="ti ti-calendar-off me-1"></i> Tanggal Resign: {{ date('d-m-Y', strtotime($d->tanggal_resign)) }}
                                         </div>
                                         <div class="mt-1">
                                             <span class="badge bg-danger" style="font-size: 10px;">NON AKTIF</span>
                                             @if(!empty($d->kode_kategori))
                                                 <span class="badge bg-label-warning ms-1" style="font-size: 10px;">{{ $d->kategoriResign->nama_kategori ?? '-' }}</span>
                                             @endif
                                         </div>
                                     </div>
                                     <!-- Alasan -->
                                     <div class="col-md-2">
                                         <div class="text-muted mb-1" style="font-size: 11px;">
                                             <strong>Alasan:</strong><br>
                                             <em>{{ $d->alasan ?? 'Tidak ada keterangan' }}</em>
                                         </div>
                                     </div>
                                     <!-- Action -->
                                     <div class="col-md-2 text-end">
                                         <div class="btn-group" role="group">
                                             @if ($d->dokumen)
                                                 <a href="{{ asset('storage/uploads/resign/' . $d->dokumen) }}" target="_blank" class="btn btn-sm btn-outline-info py-1 px-2" title="Lihat Dokumen">
                                                     <i class="ti ti-file-text"></i>
                                                 </a>
                                             @endif
                                             <form method="POST" action="{{ route('resign.delete', $d->id) }}" class="delete-form d-inline">
                                                 @csrf
                                                 @method('DELETE')
                                                 <button type="submit" class="btn btn-sm btn-outline-success delete-confirm py-1 px-2" title="Batalkan Resign (Aktifkan Lagi)">
                                                     <i class="ti ti-arrow-back-up"></i>
                                                 </button>
                                             </form>
                                         </div>
                                     </div>
                                </div>
                            </div>
                        </div>
                        @empty
                            <div class="card shadow-none border">
                                <div class="card-body text-center py-5">
                                    <i class="ti ti-folder-off text-muted" style="font-size: 48px;"></i>
                                    <p class="text-muted mt-2 mb-0">Belum ada data resign karyawan.</p>
                                </div>
                            </div>
                        @endforelse
                        
                        <div class="d-flex justify-content-end mt-3">
                            {{ $resign->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
    $(function() {
        $(".delete-confirm").click(function(e){
            e.preventDefault();
            var form = $(this).closest("form");
            Swal.fire({
                title: 'Batalkan Resign?',
                text: "Karyawan ini akan dikembalikan menjadi status Aktif kembali.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Aktifkan Kembali!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        });

        $("#btnTambah").click(function(e) {
            e.preventDefault();
            $("#load-form").html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');
            $("#modalInput").modal("show");
            $("#load-form").load("{{ route('resign.create') }}");
        });
    });
</script>
@endpush
