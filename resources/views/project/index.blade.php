@extends('layouts.app')
@section('titlepage', 'Manajemen Project')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Project Management
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Kelola, tugaskan, dan pantau progress pengerjaan project organisasi Anda.
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard.index') }}">
                        <i class="ti ti-home-2 ti-xs"></i>
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    Projects
                </li>
            </ol>
        </nav>
    </div>
@endsection

@php
    // Calculate statistics
    $totalCount = \App\Models\Project::count();
    $activeCount = \App\Models\Project::whereIn('status', ['planning', 'in_progress'])->count();
    $completedCount = \App\Models\Project::where('status', 'completed')->count();
    $overdueCount = \App\Models\Project::where('status', '!=', 'completed')->where('end_date', '<', date('Y-m-d'))->count();

    // Map status enum to collection objects for x-select component compatibility
    $statusData = collect([
        (object) ['key' => 'planning', 'text' => 'PLANNING'],
        (object) ['key' => 'in_progress', 'text' => 'IN PROGRESS'],
        (object) ['key' => 'completed', 'text' => 'COMPLETED'],
        (object) ['key' => 'on_hold', 'text' => 'ON HOLD'],
        (object) ['key' => 'cancelled', 'text' => 'CANCELLED'],
    ]);
@endphp

<!-- Statistics Cards (Redesigned matching the reference image layout) -->
<div class="row mb-4 g-3">
    <div class="col-lg-3 col-sm-6">
        <div class="card h-100 border-0" style="border-radius: 12px; background-color: #ffffff; box-shadow: 0 4px 18px 0 rgba(15, 34, 58, 0.05);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1 fw-bold text-dark" style="font-size: 1.625rem; font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; letter-spacing: -0.5px; line-height: 1.2;">{{ $totalCount }}</h3>
                        <span class="text-secondary" style="font-size: 0.85rem; font-weight: 500;">Total Project</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: rgba(115, 103, 240, 0.1) !important; color: #7367f0 !important; border-radius: 10px;">
                        <i class="ti ti-briefcase" style="font-size: 22px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card h-100 border-0" style="border-radius: 12px; background-color: #ffffff; box-shadow: 0 4px 18px 0 rgba(15, 34, 58, 0.05);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1 fw-bold text-dark" style="font-size: 1.625rem; font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; letter-spacing: -0.5px; line-height: 1.2;">{{ $activeCount }}</h3>
                        <span class="text-secondary" style="font-size: 0.85rem; font-weight: 500;">Project Aktif</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: rgba(255, 159, 67, 0.1) !important; color: #ff9f43 !important; border-radius: 10px;">
                        <i class="ti ti-activity" style="font-size: 22px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card h-100 border-0" style="border-radius: 12px; background-color: #ffffff; box-shadow: 0 4px 18px 0 rgba(15, 34, 58, 0.05);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1 fw-bold text-dark" style="font-size: 1.625rem; font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; letter-spacing: -0.5px; line-height: 1.2;">{{ $completedCount }}</h3>
                        <span class="text-secondary" style="font-size: 0.85rem; font-weight: 500;">Project Selesai</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: rgba(40, 199, 111, 0.1) !important; color: #28c76f !important; border-radius: 10px;">
                        <i class="ti ti-circle-check" style="font-size: 22px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="card h-100 border-0" style="border-radius: 12px; background-color: #ffffff; box-shadow: 0 4px 18px 0 rgba(15, 34, 58, 0.05);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1 fw-bold text-danger" style="font-size: 1.625rem; font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; letter-spacing: -0.5px; line-height: 1.2;">{{ $overdueCount }}</h3>
                        <span class="text-secondary" style="font-size: 0.85rem; font-weight: 500;">Terlambat (Overdue)</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: rgba(234, 84, 85, 0.1) !important; color: #ea5455 !important; border-radius: 10px;">
                        <i class="ti ti-clock" style="font-size: 22px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 fw-bold text-dark"><i class="ti ti-list me-2 text-primary"></i>Daftar Project</h5>
                    @can('project.create')
                        <a href="javascript:void(0)" id="btnCreateProject" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Buat Project Baru
                        </a>
                    @endcan
                </div>
                <!-- Search & Filters (Using Karyawan Index form design) -->
                <form action="{{ route('project.index') }}">
                    <div class="row g-2 mb-3">
                        <div class="col-lg-3 col-sm-12 col-md-12">
                            <x-input-with-icon label="Cari Kode atau Nama Project" value="{{ Request('search') }}" name="search"
                                icon="ti ti-search" hideLabel />
                        </div>
                        <div class="col-lg-2 col-sm-12 col-md-12">
                            <x-select label="Kategori" name="category_id" :data="$categories" key="id" textShow="nama_kategori"
                                selected="{{ Request('category_id') }}" hideLabel />
                        </div>
                        <div class="col-lg-2 col-sm-12 col-md-12">
                            <x-select label="Departemen" name="kode_dept" :data="$departements" key="kode_dept" textShow="nama_dept"
                                selected="{{ Request('kode_dept') }}" upperCase="true" hideLabel />
                        </div>
                        <div class="col-lg-2 col-sm-12 col-md-12">
                            <x-select label="Status" name="status" :data="$statusData" key="key" textShow="text"
                                selected="{{ Request('status') }}" hideLabel />
                        </div>
                        <div class="col-lg-2 col-sm-12 col-md-12">
                            <x-select label="Cabang" name="kode_cabang" :data="$cabangs" key="kode_cabang" textShow="nama_cabang"
                                selected="{{ Request('kode_cabang') }}" upperCase="true" hideLabel />
                        </div>
                        <div class="col-lg-1 col-sm-12 col-md-12">
                            <button class="btn btn-primary w-100"><i class="ti ti-icons ti-search me-1"></i></button>
                        </div>
                    </div>
                </form>

                <!-- Project Cards Listing (Redesigned matching /karyawan cards index style) -->
                <div class="row">
                    <div class="col-12">
                        @forelse ($projects as $proj)
                            @php
                                $leader = $proj->members->where('role', 'leader')->first();
                                $leaderName = $leader && $leader->karyawan ? $leader->karyawan->nama_karyawan : 'Belum Ditentukan';
                                
                                // Progress bar colors
                                $progColor = 'bg-primary';
                                if ($proj->progress >= 100) $progColor = 'bg-success';
                                elseif ($proj->status === 'on_hold') $progColor = 'bg-warning';
                                elseif ($proj->status === 'cancelled') $progColor = 'bg-danger';

                                // Status Badges
                                $statusBadge = 'bg-label-secondary';
                                if ($proj->status === 'in_progress') $statusBadge = 'bg-label-primary';
                                elseif ($proj->status === 'completed') $statusBadge = 'bg-label-success';
                                elseif ($proj->status === 'on_hold') $statusBadge = 'bg-label-warning';
                                elseif ($proj->status === 'cancelled') $statusBadge = 'bg-label-danger';
                                
                                // Check overdue
                                $isOverdue = ($proj->status !== 'completed' && $proj->end_date->isPast());
                            @endphp
                            
                            <div class="card mb-2 shadow-sm border">
                                <div class="card-body p-2">
                                    <div class="row align-items-center">
                                        <!-- Category Icon Avatar (Matching /karyawan Avatar layout) -->
                                        <div class="col-md-1 text-center d-none d-md-block">
                                            <span class="avatar rounded-circle d-flex align-items-center justify-content-center mx-auto text-white fw-bold" 
                                                style="width: 40px; height: 40px; background-color: {{ $proj->category->warna ?? '#696cff' }}; font-size: 14px; border: 1px solid #e9ecef;">
                                                {{ strtoupper(substr($proj->nama_project, 0, 2)) }}
                                            </span>
                                        </div>
                                        
                                        <!-- Identity / Title (Matching /karyawan Identity layout) -->
                                        <div class="col-12 col-md-4">
                                            <div class="fw-bold text-dark" style="font-size: 14px;">
                                                <a href="{{ route('project.show', Crypt::encrypt($proj->id)) }}" class="text-dark" style="text-decoration: none;">
                                                    {{ $proj->nama_project }}
                                                </a>
                                                <span class="text-muted fw-normal" style="font-size: 12px;">({{ $proj->kode_project }})</span>
                                            </div>
                                            <div class="mt-1 flex-wrap gap-1 d-inline-flex">
                                                <span class="badge" style="background-color: {{ $proj->category->warna ?? '#696cff' }}; color: #fff; font-size: 9px; padding: 2px 6px;">
                                                    {{ $proj->category ? $proj->category->nama_kategori : 'Umum' }}
                                                </span>
                                                <span class="badge bg-label-primary" style="font-size: 9px; padding: 2px 6px;">Dept: {{ $proj->departemen ? $proj->departemen->nama_dept : '-' }}</span>
                                                <span class="badge bg-label-info" style="font-size: 9px; padding: 2px 6px;">Cab: {{ $proj->cabang ? $proj->cabang->nama_cabang : '-' }}</span>
                                                <span class="badge bg-label-warning text-dark" style="font-size: 9px; padding: 2px 6px;">Leader: {{ formatName($leaderName) }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Timeline & Status (Matching /karyawan Status/Work Duration layout) -->
                                        <div class="col-md-3 border-start border-end d-none d-md-block text-center">
                                            <div class="mb-1">
                                                <span class="badge {{ $statusBadge }} py-1 px-2 text-uppercase" style="font-size: 9px;">
                                                    {{ str_replace('_', ' ', $proj->status) }}
                                                </span>
                                                @if ($isOverdue)
                                                    <span class="badge bg-danger py-1 px-2 text-white text-uppercase" style="font-size: 9px;">TERLAMBAT</span>
                                                @endif
                                            </div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                Timeline: {{ $proj->start_date->format('d-m-Y') }} s/d {{ $proj->end_date->format('d-m-Y') }}
                                            </div>
                                            <div class="text-muted" style="font-size: 10px;">
                                                {{ $proj->tasks->count() }} Tasks | {{ $proj->tasks->where('status', 'completed')->count() }} Selesai
                                            </div>
                                        </div>
                                        
                                        <!-- Progress Column (Matching /karyawan details layout grid) -->
                                        <div class="col-md-2 text-center d-none d-md-block px-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted" style="font-size: 10px; font-weight: 600;">PROGRESS</small>
                                                <small class="fw-bold text-dark" style="font-size: 11px;">{{ $proj->progress }}%</small>
                                            </div>
                                            <div class="progress" style="height: 6px; margin-bottom: 0;">
                                                <div class="progress-bar {{ $progColor }}" role="progressbar" style="width: {{ $proj->progress }}%"></div>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Buttons (Matching /karyawan action buttons group) -->
                                        <div class="col-12 col-md-2 text-end mt-2 mt-md-0">
                                            <div class="btn-group shadow-sm" role="group">
                                                <a href="{{ route('project.show', Crypt::encrypt($proj->id)) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Detail Board">
                                                    <i class="ti ti-layout-dashboard"></i>
                                                </a>
                                                @can('project.edit')
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary py-1 px-2 btnEditProject" data-href="{{ route('project.edit', Crypt::encrypt($proj->id)) }}" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('project.delete')
                                                    <form method="POST" name="deleteform" class="deleteform d-inline" action="{{ route('project.delete', Crypt::encrypt($proj->id)) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger delete-confirm rounded-0 rounded-end py-1 px-2" title="Delete">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted border border-dashed rounded-3 mb-2">
                                <i class="ti ti-briefcase fs-1 mb-2 d-block text-secondary"></i>
                                Belum ada project yang terdaftar.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-end mt-3">
                    {{ $projects->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<x-modal-form id="modalCreateProject" show="loadmodalCreateProject" size="modal-xl" title="Buat Project Baru" />
<x-modal-form id="modalEditProject" show="loadmodalEditProject" size="modal-xl" title="Edit Project" />
@endsection

@push('myscript')
<script>
    $(function() {
        function loading(target) {
            $(target).html(`<div class="sk-wave sk-primary" style="margin:auto">
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
            </div>`);
        }

        $("#btnCreateProject").click(function() {
            $("#modalCreateProject").modal("show");
            loading("#loadmodalCreateProject");
            $("#loadmodalCreateProject").load("{{ route('project.create') }}");
        });

        $(".btnEditProject").click(function() {
            var href = $(this).attr("data-href");
            $("#modalEditProject").modal("show");
            loading("#loadmodalEditProject");
            $("#loadmodalEditProject").load(href);
        });

        $('.delete-confirm').click(function(e) {
            var form = $(this).closest("form");
            e.preventDefault();
            if (confirm("Apakah Anda yakin ingin menghapus project ini beserta seluruh datanya?")) {
                form.submit();
            }
        });
    });
</script>
@endpush
