@extends('layouts.app')
@section('titlepage', 'Jenis Reimbursement')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Jenis Reimbursement
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Manajemen jenis dan aturan plafon reimbursement global.
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
                        <i class="ti ti-receipt-refund ti-xs me-1"></i> Reimbursement
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    Jenis & Aturan
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            @can('jenisreimbursement.create')
                <a href="#" class="btn btn-primary" id="btnCreate">
                    <i class="ti ti-plus me-1"></i> Tambah Jenis Reimbursement
                </a>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center py-3" style="background: linear-gradient(to right, var(--theme-color-1), var(--theme-color-2)); color: white !important;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-settings-automation me-2 fs-4"></i>
                    <h6 class="card-title mb-0 text-white">Master Jenis & Aturan Plafon</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="py-3 px-4 text-white" style="width: 100px;">KODE</th>
                                <th class="py-3 text-white">NAMA JENIS</th>
                                <th class="py-3 text-end text-white">PLAFON /KLAIM</th>
                                <th class="py-3 text-end text-white">PLAFON /BULAN</th>
                                <th class="py-3 text-center text-white">WAJIB BUKTI</th>
                                <th class="py-3 text-center text-white">STATUS</th>
                                <th class="py-3 text-center text-white" style="width: 150px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jenis_reimbursement as $d)
                                <tr>
                                    <td class="fw-bold px-4">
                                        <span class="badge bg-label-primary">{{ $d->kode_jenis_reimburse }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $d->nama_jenis }}</div>
                                        <small class="text-muted">{{ Str::limit($d->deskripsi, 50) }}</small>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        {{ $d->batas_nominal ? number_format($d->batas_nominal, 0, ',', '.') : 'Unlimited' }}
                                    </td>
                                    <td class="text-end fw-bold text-info">
                                        {{ $d->batas_nominal_bulanan ? number_format($d->batas_nominal_bulanan, 0, ',', '.') : 'Unlimited' }}
                                    </td>
                                    <td class="text-center">
                                        @if($d->wajib_bukti)
                                            <span class="badge bg-label-warning"><i class="ti ti-camera me-1"></i> Ya</span>
                                        @else
                                            <span class="badge bg-label-secondary">Tidak</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($d->status)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex border rounded overflow-hidden shadow-xs">
                                            @can('jenisreimbursement.edit')
                                                <a href="#" class="btn btn-sm btnEnroll px-2 py-1 border-0 rounded-0"
                                                    id_jenis="{{ Crypt::encrypt($d->id) }}" title="Karyawan Enrolled"
                                                    style="background: #f8f9fa;">
                                                    <i class="ti ti-users fs-6 text-info"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btnEdit px-2 py-1 border-0 rounded-0 border-start"
                                                    id_jenis="{{ Crypt::encrypt($d->id) }}" title="Edit"
                                                    style="background: #f8f9fa;">
                                                    <i class="ti ti-edit fs-6 text-primary"></i>
                                                </a>
                                            @endcan

                                            @can('jenisreimbursement.delete')
                                                <form method="POST" name="deleteform" class="deleteform m-0"
                                                    action="{{ route('jenisreimbursement.delete', Crypt::encrypt($d->id)) }}">
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
                            @if($jenis_reimbursement->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="ti ti-database-x fs-1 text-muted" style="font-size: 5rem !important;"></i>
                                        </div>
                                        <p class="text-muted">Belum ada data jenis reimbursement.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<x-modal-form id="modal" show="loadmodal" size="modal-lg" />

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

        $("#btnCreate").click(function(e) {
            e.preventDefault();
            loading();
            $("#modal").modal("show");
            $(".modal-title").text("Tambah Jenis Reimbursement");
            $("#loadmodal").load("{{ route('jenisreimbursement.create') }}");
        });

        $(".btnEdit").click(function(e) {
            e.preventDefault();
            loading();
            const id = $(this).attr("id_jenis");
            $("#modal").modal("show");
            $(".modal-title").text("Edit Jenis Reimbursement");
            $("#loadmodal").load(`/jenisreimbursement/${id}/edit`);
        });

        $(".btnEnroll").click(function(e) {
            e.preventDefault();
            const id = $(this).attr("id_jenis");
            location.href = `/jenisreimbursement/${id}/setkaryawan`;
        });
    });
</script>
@endpush
