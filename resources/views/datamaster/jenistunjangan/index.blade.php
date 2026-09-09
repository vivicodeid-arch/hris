@extends('layouts.app')
@section('titlepage', 'Jenis Tunjangan')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Jenis Tunjangan
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Manajemen jenis tunjangan karyawan.
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard.index') }}">
                        <i class="ti ti-home-2 ti-xs"></i>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="javascript:void(0);">
                        <i class="ti ti-database ti-xs me-1"></i> Data Master
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="ti ti-gift ti-xs me-1"></i> Jenis Tunjangan
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            @can('jenistunjangan.create')
                <a href="#" class="btn btn-primary" id="btnCreate">
                    <i class="ti ti-plus me-1"></i> Tambah Jenis Tunjangan
                </a>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-12 col-sm-12 mb-4 mb-lg-0">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-layout-grid me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Data Jenis Tunjangan</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="text-white py-3">KODE</th>
                                <th class="text-white py-3">JENIS TUNJANGAN</th>
                                <th class="text-white py-3">SIFAT</th>
                                <th class="text-white py-3 text-center" style="width: 100px;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jenistunjangan as $d)
                                <tr>
                                    <td class="fw-bold py-2">{{ $d->kode_jenis_tunjangan }}</td>
                                    <td class="py-2">{{ $d->jenis_tunjangan }}</td>
                                    <td class="py-2">
                                        @if ($d->status_tunjangan === '0')
                                            <span class="badge bg-label-warning">Tidak Tetap</span>
                                        @else
                                            <span class="badge bg-label-success">Tetap</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-center">
                                        <div class="d-inline-flex border rounded overflow-hidden shadow-xs">
                                            @can('jenistunjangan.edit')
                                                <a href="#" class="btn btn-sm btnEdit px-2 py-1 border-0 rounded-0"
                                                    kode_jenis_tunjangan="{{ Crypt::encrypt($d->kode_jenis_tunjangan) }}" title="Edit"
                                                    style="background: #f8f9fa;">
                                                    <i class="ti ti-edit fs-6 text-primary"></i>
                                                </a>
                                            @endcan

                                            @can('jenistunjangan.delete')
                                                <form method="POST" name="deleteform" class="deleteform m-0"
                                                    action="{{ route('jenistunjangan.delete', Crypt::encrypt($d->kode_jenis_tunjangan)) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm delete-confirm px-2 py-1 border-0 rounded-0 border-start"
                                                        title="Hapus" style="background: #f8f9fa;">
                                                        <i class="ti ti-trash fs-6 text-danger"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if($jenistunjangan->isEmpty())
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Data tidak ditemukan.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Tunjangan Tetap & Tidak Tetap -->
    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-info-circle me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Informasi Sifat Tunjangan</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <h6 class="text-success fw-bold d-flex align-items-center mb-2">
                        <i class="ti ti-circle-check-filled me-2 fs-4"></i> Tunjangan Tetap
                    </h6>
                    <p class="text-muted mb-2" style="font-size: 0.875rem; text-align: justify; line-height: 1.6;">
                        Merupakan pembayaran kepada karyawan yang dilakukan secara teratur dan rutin. Jumlahnya tetap dan tidak dipengaruhi oleh kehadiran (absensi), pencapaian kinerja, maupun faktor fluktuatif lainnya. Tunjangan ini biasanya dibayarkan bersamaan dengan gaji pokok bulanan.
                    </p>
                    <div class="p-2 bg-light rounded text-success" style="font-size: 0.75rem; border-left: 3px solid #28a745;">
                        <strong>Contoh:</strong> Tunjangan Jabatan, Tunjangan Keluarga, Tunjangan Perumahan, atau Tunjangan Keahlian.
                    </div>
                </div>

                <hr class="my-3 text-muted opacity-25">

                <div>
                    <h6 class="text-warning fw-bold d-flex align-items-center mb-2">
                        <i class="ti ti-clock-filled me-2 fs-4"></i> Tunjangan Tidak Tetap
                    </h6>
                    <p class="text-muted mb-2" style="font-size: 0.875rem; text-align: justify; line-height: 1.6;">
                        Merupakan pembayaran kepada karyawan yang dilakukan secara tidak teratur atau nilainya dapat berubah-ubah (fluktuatif). Besaran tunjangan ini umumnya dikaitkan dengan kehadiran (absensi) harian, pencapaian kinerja, atau faktor penentu lainnya di periode kerja tersebut.
                    </p>
                    <div class="p-2 bg-light rounded text-warning" style="font-size: 0.75rem; border-left: 3px solid #ffc107;">
                        <strong>Contoh:</strong> Tunjangan Uang Makan Harian, Tunjangan Transportasi Harian (berdasarkan kehadiran), atau Insentif Kinerja.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<x-modal-form id="modal" show="loadmodal" />
@endsection

@push('myscript')
<script>
    $(function() {
        function loading() {
            $("#loadmodal").html(`<div class="sk-wave sk-primary" style="margin:auto">
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            </div>`);
        };
        loading();

        $("#btnCreate").click(function() {
            $("#modal").modal("show");
            $(".modal-title").text("Tambah Jenis Tunjangan");
            $("#loadmodal").load('/jenistunjangan/create');
        });

        $(".btnEdit").click(function() {
            loading();
            const kode_jenis_tunjangan = $(this).attr("kode_jenis_tunjangan");
            $("#modal").modal("show");
            $(".modal-title").text("Edit Jenis Tunjangan");
            $("#loadmodal").load(`/jenistunjangan/${kode_jenis_tunjangan}/edit`);
        });
    });
</script>
@endpush
