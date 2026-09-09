@extends('layouts.app')
@section('titlepage', 'Roles')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Roles
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Manajemen data role dan hak akses (permissions) sistem.
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
                    <i class="ti ti-user-shield ti-xs me-1"></i> Roles
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="#" class="btn btn-primary" id="btncreateRole">
                <i class="ti ti-plus me-1"></i> Tambah Role
            </a>
        </div>
        
        <form action="{{ route('roles.index') }}">
            <div class="row g-2 mb-3">
                <div class="col-lg-10 col-md-9 col-sm-12">
                    <x-input-with-icon label="Search Role Name" value="{{ Request('name') }}"
                        name="name" icon="ti ti-search" hideLabel />
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
                    <i class="ti ti-user-shield me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Data Roles</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="text-white py-3" style="width: 60px;">NO.</th>
                                <th class="text-white py-3">ROLE NAME</th>
                                <th class="text-white py-3">GUARD</th>
                                <th class="text-white py-3 text-center" style="width: 150px;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $d)
                                <tr>
                                    <td class="py-2">{{ $loop->iteration + ($roles->currentPage() - 1) * $roles->perPage() }}</td>
                                    <td class="py-2 fw-semibold text-dark">{{ ucwords($d->name) }}</td>
                                    <td class="py-2"><span class="badge bg-label-secondary">{{ $d->guard_name }}</span></td>
                                    <td class="py-2 text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('roles.createrolepermission', Crypt::encrypt($d->id)) }}"
                                                class="btn btn-sm btn-icon btn-label-info shadow-none" 
                                                title="Set Permission">
                                                <i class="ti ti-shield-lock fs-5"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-icon btn-label-primary editRole shadow-none"
                                                id="{{ $d->id }}" title="Edit">
                                                <i class="ti ti-edit fs-5"></i>
                                            </a>
                                            <form method="POST" name="deleteform" class="deleteform m-0"
                                                action="{{ route('roles.delete', Crypt::encrypt($d->id)) }}">
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
                            @if($roles->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Data role tidak ditemukan.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $roles->links('pagination::bootstrap-5') }}
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
    .btn-label-info {
        background-color: #e0f7fc;
        color: #00bcd4;
        border: none;
    }
    .btn-label-info:hover {
        background-color: #00bcd4;
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

<x-modal-form id="mdlcreateRole" size="" show="loadcreateRole" title="Tambah Role" />
@endsection

@push('myscript')
<script>
    $(function() {
        $("#btncreateRole").click(function(e) {
            e.preventDefault();
            $('#mdlcreateRole').modal("show");
            $("#loadcreateRole").load('/roles/create');
        });

        $(".editRole").click(function(e) {
            var id = $(this).attr("id");
            e.preventDefault();
            $('#mdlcreateRole').modal("show"); // reusing the same modal for simplicity as standard modal form
            $('#mdlcreateRole').find('.modal-title').text('Edit Role');
            $("#loadcreateRole").load('/roles/' + id + '/edit');
        });
    });
</script>
@endpush
