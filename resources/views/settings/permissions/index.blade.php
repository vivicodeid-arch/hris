@extends('layouts.app')
@section('titlepage', 'Permissions')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Permissions
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Manajemen hak akses (permissions) sistem.
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
                        <i class="ti ti-settings ti-xs me-1"></i> Settings
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="ti ti-key ti-xs me-1"></i> Permissions
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="#" class="btn btn-primary" id="btncreatePermission">
                <i class="ti ti-plus me-1"></i> Tambah Permission
            </a>
        </div>
        
        <form action="{{ route('permissions.index') }}">
            <div class="row g-2 mb-3 align-items-center">
                <div class="col-lg-10 col-md-9 col-sm-12">
                    <div class="mb-0">
                        <x-select name="id_permission_group" label="Group" :data="$permission_groups" key="id"
                            textShow="name" selected="{{ Request('id_permission_group') }}" hideLabel placeholder="Pilih Group Permission" />
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12" style="margin-top: -12px;">
                    <button class="btn btn-primary w-100"><i class="ti ti-search me-1"></i> Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12">
        <div class="card shadow-none border">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-key me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Data Permissions</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="text-white py-3" style="width: 60px;">NO.</th>
                                <th class="text-white py-3">PERMISSION NAME</th>
                                <th class="text-white py-3">GROUP NAME</th>
                                <th class="text-white py-3 text-center" style="width: 120px;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $d)
                                <tr>
                                    <td class="py-2">{{ $loop->iteration + ($permissions->currentPage() - 1) * $permissions->perPage() }}</td>
                                    <td class="py-2"><code class="text-danger" style="font-size: 0.85rem;">{{ strtolower($d->name) }}</code></td>
                                    <td class="py-2 fw-semibold text-dark">{{ $d->group_name }}</td>
                                    <td class="py-2 text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="#" class="btn btn-sm btn-icon btn-label-primary editPermission shadow-none"
                                                id="{{ Crypt::encrypt($d->id) }}" title="Edit">
                                                <i class="ti ti-edit fs-5"></i>
                                            </a>
                                            <form method="POST" name="deleteform" class="deleteform m-0"
                                                action="{{ route('permissions.delete', Crypt::encrypt($d->id)) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-label-danger delete-confirm shadow-none"
                                                    title="Hapus">
                                                    <i class="ti ti-trash fs-5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if($permissions->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Data permission tidak ditemukan.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $permissions->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<style>
    .btn-label-primary {
        background-color: #f0f4ff;
        color: var(--bs-primary);
        border: none;
    }
    .btn-label-primary:hover {
        background-color: var(--bs-primary);
        color: white;
    }
    .btn-label-danger {
        background-color: #fff5f5;
        color: var(--bs-danger);
        border: none;
    }
    .btn-label-danger:hover {
        background-color: var(--bs-danger);
        color: white;
    }
</style>

<x-modal-form id="mdlcreatePermission" size="" show="loadcreatePermission" title="Tambah Permission" />
@endsection

@push('myscript')
<script>
    $(function() {
        $("#btncreatePermission").click(function(e) {
            e.preventDefault();
            $('#mdlcreatePermission').modal("show");
            $("#loadcreatePermission").load('/permissions/create');
        });

        $(".editPermission").click(function(e) {
            var id = $(this).attr("id");
            e.preventDefault();
            $('#mdlcreatePermission').modal("show"); // reusing the same modal for simplicity as standard modal form
            $('#mdlcreatePermission').find('.modal-title').text('Edit Permission');
            $("#loadcreatePermission").load('/permissions/' + id + '/edit');
        });
    });
</script>
@endpush
