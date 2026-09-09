@extends('layouts.app')
@section('titlepage', 'Permission Groups')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Permission Groups
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Manajemen group hak akses (permission groups) sistem.
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
                    <i class="ti ti-folder ti-xs me-1"></i> Permission Groups
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="#" class="btn btn-primary" id="btncreateGroup">
                <i class="ti ti-plus me-1"></i> Tambah Group
            </a>
        </div>
        
        <form action="{{ route('permissiongroups.index') }}">
            <div class="row g-2 mb-3">
                <div class="col-lg-10 col-md-9 col-sm-12">
                    <x-input-with-icon label="Search Group" value="{{ Request('name') }}" name="name"
                        icon="ti ti-search" hideLabel />
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12">
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
                    <i class="ti ti-folder me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Data Permission Groups</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="text-white py-3" style="width: 60px;">NO.</th>
                                <th class="text-white py-3">GROUP NAME</th>
                                <th class="text-white py-3 text-center" style="width: 120px;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permission_groups as $d)
                                <tr>
                                    <td class="py-2">{{ $loop->iteration + ($permission_groups->currentPage() - 1) * $permission_groups->perPage() }}</td>
                                    <td class="py-2 fw-semibold text-dark">{{ $d->name }}</td>
                                    <td class="py-2 text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="#" class="btn btn-sm btn-icon btn-label-primary editGroup shadow-none"
                                                id="{{ Crypt::encrypt($d->id) }}" title="Edit">
                                                <i class="ti ti-edit fs-5"></i>
                                            </a>
                                            <form method="POST" name="deleteform" class="deleteform m-0"
                                                action="{{ route('permissiongroups.delete', Crypt::encrypt($d->id)) }}">
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
                            @if($permission_groups->isEmpty())
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Data group tidak ditemukan.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $permission_groups->links('pagination::bootstrap-5') }}
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

<x-modal-form id="mdlcreateGroup" size="" show="loadcreateGroup" title="Tambah Group" />
@endsection

@push('myscript')
<script>
    $(function() {
        $("#btncreateGroup").click(function(e) {
            e.preventDefault();
            $('#mdlcreateGroup').modal("show");
            $("#loadcreateGroup").load('/permissiongroups/create');
        });

        $(".editGroup").click(function(e) {
            var id = $(this).attr("id");
            e.preventDefault();
            $('#mdlcreateGroup').modal("show"); // reusing the same modal for simplicity as standard modal form
            $('#mdlcreateGroup').find('.modal-title').text('Edit Permission Group');
            $("#loadcreateGroup").load('/permissiongroups/' + id + '/edit');
        });
    });
</script>
@endpush
