@extends('layouts.app')
@section('titlepage', 'Detail Project')

@section('content')
<style>
    .kanban-task-card.dragging {
        opacity: 0.45;
        border: 2px dashed #696cff !important;
        transform: scale(0.98);
        background-color: #f8f9fa !important;
    }
    .kanban-column {
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .kanban-column.drag-over {
        background-color: rgba(105, 108, 255, 0.06) !important;
        outline: 2px dashed #696cff;
        outline-offset: -2px;
    }
</style>
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Detail Project
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Informasi detail, koordinasi tim, dan status tugas project.
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
                    <a href="{{ route('project.index') }}">Projects</a>
                </li>
                <li class="breadcrumb-item active">Detail Project</li>
            </ol>
        </nav>
    </div>
@endsection

@php
    // Progress bar color
    $progColor = 'bg-primary';
    if ($project->progress >= 100) $progColor = 'bg-success';
    elseif ($project->status === 'on_hold') $progColor = 'bg-warning';
    elseif ($project->status === 'cancelled') $progColor = 'bg-danger';

    // Status Badges
    $statusBadge = 'bg-label-secondary';
    if ($project->status === 'in_progress') $statusBadge = 'bg-label-primary';
    elseif ($project->status === 'completed') $statusBadge = 'bg-label-success';
    elseif ($project->status === 'on_hold') $statusBadge = 'bg-label-warning';
    elseif ($project->status === 'cancelled') $statusBadge = 'bg-label-danger';
@endphp

<!-- Alerts -->
<div class="row">
    <div class="col-12">
        @if (Session::get('success'))
            <div class="alert alert-success alert-dismissible mb-3" role="alert">
                {{ Session::get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (Session::get('error'))
            <div class="alert alert-danger alert-dismissible mb-3" role="alert">
                {{ Session::get('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Project Info & Members -->
    <div class="col-xl-4 col-lg-5 col-md-12">
        <!-- Project Info Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge" style="background-color: {{ $project->category->warna ?? '#696cff' }}; color: #fff;">
                        {{ $project->category ? $project->category->nama_kategori : 'Umum' }}
                    </span>
                    <span class="badge {{ $statusBadge }} text-uppercase">
                        {{ str_replace('_', ' ', $project->status) }}
                    </span>
                </div>
                <h4 class="mb-1 fw-bold text-primary">{{ $project->nama_project }}</h4>
                <p class="text-muted small mb-3">Kode: {{ $project->kode_project }}</p>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-semibold">Overall Progress</small>
                        <small class="fw-bold">{{ $project->progress }}%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar {{ $progColor }}" role="progressbar" style="width: {{ $project->progress }}%" aria-valuenow="{{ $project->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="border-top pt-3">
                    <div class="row g-3 mb-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Tanggal Mulai</small>
                            <span class="fw-semibold small"><i class="ti ti-calendar me-1"></i>{{ $project->start_date->format('d M Y') }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Deadline</small>
                            <span class="fw-semibold small"><i class="ti ti-calendar-off me-1"></i>{{ $project->end_date->format('d M Y') }}</span>
                        </div>
                    </div>
                    <div class="row g-3 mb-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Departemen</small>
                            <span class="fw-semibold small"><i class="ti ti-building me-1"></i>{{ $project->departemen ? $project->departemen->nama_dept : '-' }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Cabang</small>
                            <span class="fw-semibold small"><i class="ti ti-map-pin me-1"></i>{{ $project->cabang ? $project->cabang->nama_cabang : '-' }}</span>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Prioritas</small>
                            <span class="fw-semibold small text-uppercase"><i class="ti ti-alert-circle me-1"></i>{{ $project->prioritas }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Budget</small>
                            <span class="fw-semibold small"><i class="ti ti-coin me-1"></i>Rp {{ $project->budget ? formatRupiah($project->budget) : '0' }}</span>
                        </div>
                    </div>
                </div>

                @if(!empty($project->deskripsi))
                    <div class="border-top mt-3 pt-3">
                        <small class="text-muted d-block mb-1">Deskripsi</small>
                        <p class="small text-secondary mb-0" style="white-space: pre-line;">{{ $project->deskripsi }}</p>
                    </div>
                @endif
                
                @if(!empty($project->catatan))
                    <div class="border-top mt-3 pt-3">
                        <small class="text-muted d-block mb-1">Catatan Tambahan</small>
                        <p class="small text-warning mb-0" style="white-space: pre-line;">{{ $project->catatan }}</p>
                    </div>
                @endif

                @can('project.edit')
                    <div class="d-grid mt-4">
                        <a href="javascript:void(0)" class="btn btn-outline-primary btnEditProject" data-href="{{ route('project.edit', Crypt::encrypt($project->id)) }}">
                            <i class="ti ti-edit me-1"></i> Edit Project
                        </a>
                    </div>
                @endcan
            </div>
        </div>

        <!-- Members Card -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 45px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-users me-2"></i>
                    <h6 class="card-title mb-0 text-white" style="font-size: 0.9rem;">Anggota Tim</h6>
                </div>
                @can('project.edit')
                    <button class="btn btn-xs btn-light py-1 text-primary" data-bs-toggle="modal" data-bs-target="#modalAddMember">
                        <i class="ti ti-plus"></i>
                    </button>
                @endcan
            </div>
            <div class="card-body py-3">
                <div class="list-group list-group-flush">
                    @foreach ($project->members as $member)
                        @php
                            $isLeader = $member->role === 'leader';
                            $k = $member->karyawan;
                        @endphp
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-2">
                                    <span class="avatar-initial rounded-circle bg-label-secondary p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="ti ti-user text-muted" style="font-size: 16px;"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-semibold small">{{ $k ? $k->nama_karyawan : 'Nama Karyawan' }}</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        NIK: {{ $member->nik }} | {{ $k && $k->jabatan ? $k->jabatan->nama_jabatan : 'Staf' }}
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                @if ($isLeader)
                                    <span class="badge bg-label-danger rounded-pill" style="font-size: 0.65rem;">LEADER</span>
                                @else
                                    <span class="badge bg-label-info rounded-pill me-2" style="font-size: 0.65rem;">MEMBER</span>
                                    @can('project.edit')
                                        <form action="{{ route('project.removemember', ['id' => Crypt::encrypt($project->id), 'nik' => $member->nik]) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-text-danger border-0 p-1 remove-member-btn" title="Keluarkan Anggota">
                                                <i class="ti ti-trash text-danger" style="font-size: 16px;"></i>
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Tasks Board -->
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-checkbox me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Daftar Tugas (Tasks)</h6>
                </div>
                @can('project.task.create')
                    <a href="javascript:void(0)" id="btnCreateTask" class="btn btn-white btn-sm" style="background-color: #fff; color: var(--theme-color-1) !important;">
                        <i class="ti ti-plus me-1"></i> Tambah Task
                    </a>
                @endcan
            </div>
            
            <div class="card-body py-3">
                <!-- Task Board Groupings Tabs -->
                <ul class="nav nav-pills mb-3 border-bottom pb-2" id="taskTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active small" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">
                            Semua Task ({{ $project->tasks->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link small" id="kanban-tab" data-bs-toggle="tab" data-bs-target="#kanban" type="button" role="tab" aria-controls="kanban" aria-selected="false">
                            Kanban Board
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-0" id="taskTabContent">
                    <!-- Tab 1: Card List View -->
                    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                        <div class="d-flex flex-column gap-2">
                            @forelse ($project->tasks as $task)
                                @php
                                    $badgeStatus = 'bg-label-secondary';
                                    if ($task->status === 'todo') $badgeStatus = 'bg-label-secondary';
                                    elseif ($task->status === 'in_progress') $badgeStatus = 'bg-label-primary';
                                    elseif ($task->status === 'review') $badgeStatus = 'bg-label-warning';
                                    elseif ($task->status === 'completed') $badgeStatus = 'bg-label-success';
                                    elseif ($task->status === 'cancelled') $badgeStatus = 'bg-label-danger';

                                    $badgePrioritas = 'bg-label-secondary';
                                    if ($task->prioritas === 'high') $badgePrioritas = 'bg-label-warning';
                                    elseif ($task->prioritas === 'critical') $badgePrioritas = 'bg-label-danger';
                                    elseif ($task->prioritas === 'low') $badgePrioritas = 'bg-label-success';
                                @endphp
                                
                                <div class="card shadow-none border" style="border-radius: 8px;">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center">
                                            <!-- Task Code -->
                                            <div class="col-12 col-md-2 mb-2 mb-md-0 text-nowrap">
                                                <a href="{{ route('project.task.show', Crypt::encrypt($task->id)) }}" class="fw-bold text-primary text-decoration-none d-inline-flex align-items-center" style="font-size: 13.5px;">
                                                    <i class="ti ti-hash me-1 fs-5"></i>{{ $task->kode_task }}
                                                </a>
                                            </div>
                                            
                                            <!-- Task Title & Type -->
                                            <div class="col-12 col-md-3 mb-2 mb-md-0">
                                                <div class="fw-semibold text-dark" style="font-size: 14.5px;">
                                                    <a href="{{ route('project.task.show', Crypt::encrypt($task->id)) }}" class="text-dark text-decoration-none hover-primary">
                                                        {{ $task->judul }}
                                                    </a>
                                                    @if($task->parent_id)
                                                        <span class="badge bg-label-info ms-1" style="font-size: 8px; padding: 2px 4px;">SUB-TASK</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Tim & Deadline (Stacked) -->
                                            <div class="col-6 col-md-2 mb-2 mb-md-0">
                                                <div class="d-flex align-items-center text-muted mb-1" title="Tim Assigned">
                                                    <i class="ti ti-users me-1.5 fs-5"></i>
                                                    <span style="font-size: 13px;">{{ $task->members->count() }} Orang</span>
                                                </div>
                                                <div class="d-flex align-items-center text-muted" title="Deadline">
                                                    <i class="ti ti-calendar-event me-1.5 fs-5"></i>
                                                    <span style="font-size: 13px;">{{ $task->due_date ? $task->due_date->format('d/m/Y') : '-' }}</span>
                                                </div>
                                            </div>

                                            <!-- Progress -->
                                            <div class="col-12 col-md-2 mb-2 mb-md-0 px-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="text-muted" style="font-size: 10px; font-weight: 600;">PROGRESS</small>
                                                    <small class="fw-bold text-dark" style="font-size: 11px;">{{ $task->progress }}%</small>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $task->progress }}%"></div>
                                                </div>
                                            </div>

                                            <!-- Badges: Priority & Status (Stacked) -->
                                            <div class="col-12 col-md-1 mb-2 mb-md-0 text-md-center d-flex d-md-block gap-2 justify-content-start">
                                                <span class="badge {{ $badgePrioritas }} text-uppercase me-md-1 mb-md-1" style="font-size: 9px; padding: 3px 6px; display: inline-block;">
                                                    {{ $task->prioritas }}
                                                </span>
                                                <span class="badge {{ $badgeStatus }} text-uppercase" style="font-size: 9px; padding: 3px 6px; display: inline-block;">
                                                    {{ str_replace('_', ' ', $task->status) }}
                                                </span>
                                            </div>

                                            <!-- Actions -->
                                            <div class="col-12 col-md-2 text-end mt-2 mt-md-0">
                                                <div class="btn-group shadow-xs" role="group">
                                                    <a href="{{ route('project.task.show', Crypt::encrypt($task->id)) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Detail Task">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    @can('project.task.edit')
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary py-1 px-2 border-start btnEditTask" data-href="{{ route('project.task.edit', Crypt::encrypt($task->id)) }}" title="Edit">
                                                            <i class="ti ti-edit"></i>
                                                        </a>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted border border-dashed rounded-3 bg-white">
                                    <i class="ti ti-checkbox fs-1 mb-2 d-block text-secondary"></i>
                                    Belum ada task dalam project ini.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tab 2: Kanban Board View -->
                    <div class="tab-pane fade" id="kanban" role="tabpanel" aria-labelledby="kanban-tab">
                        <div class="row g-2 overflow-auto" style="min-height: 400px; flex-wrap: nowrap;">
                            
                            <!-- Columns -->
                            @php
                                $statuses = [
                                    'todo' => ['title' => 'TO DO', 'border' => 'border-top-secondary', 'badge' => 'bg-secondary'],
                                    'in_progress' => ['title' => 'IN PROGRESS', 'border' => 'border-top-primary', 'badge' => 'bg-primary'],
                                    'review' => ['title' => 'REVIEW', 'border' => 'border-top-warning', 'badge' => 'bg-warning'],
                                    'completed' => ['title' => 'COMPLETED', 'border' => 'border-top-success', 'badge' => 'bg-success']
                                ];
                            @endphp

                            @foreach($statuses as $statusKey => $statusVal)
                                @php
                                    $statusTasks = $project->tasks->where('status', $statusKey);
                                @endphp
                                <div class="col-lg-3 col-md-6 col-sm-12 flex-shrink-0" style="width: 280px;">
                                    <div class="card bg-light h-100 shadow-none border border-2 border-dashed">
                                        <div class="card-header py-2 px-3 border-bottom d-flex justify-content-between align-items-center" style="background: #fff;">
                                            <h6 class="mb-0 fw-bold text-secondary" style="font-size: 0.8rem;">{{ $statusVal['title'] }}</h6>
                                            <span class="badge {{ $statusVal['badge'] }} text-white rounded-pill">{{ $statusTasks->count() }}</span>
                                        </div>
                                        <div class="card-body p-2 d-flex flex-column g-2 overflow-y-auto kanban-column" data-status="{{ $statusKey }}" style="max-height: 500px; min-height: 250px;">
                                            @foreach($statusTasks as $task)
                                                @php
                                                    $taskPriorColor = 'bg-label-secondary';
                                                    if ($task->prioritas === 'high') $taskPriorColor = 'bg-label-warning';
                                                    elseif ($task->prioritas === 'critical') $taskPriorColor = 'bg-label-danger';
                                                    elseif ($task->prioritas === 'low') $taskPriorColor = 'bg-label-success';
                                                @endphp
                                                <div class="card mb-2 shadow-sm border-start border-3 {{ $task->prioritas === 'critical' ? 'border-danger' : 'border-info' }} kanban-task-card" draggable="true" data-id="{{ Crypt::encrypt($task->id) }}" style="cursor: grab;">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <a href="{{ route('project.task.show', Crypt::encrypt($task->id)) }}" class="fw-bold small text-primary">{{ $task->kode_task }}</a>
                                                            <span class="badge {{ $taskPriorColor }} text-uppercase" style="font-size: 0.55rem;">{{ $task->prioritas }}</span>
                                                        </div>
                                                        <h6 class="card-title fw-semibold mb-1" style="font-size: 0.85rem;">{{ $task->judul }}</h6>
                                                        <p class="card-text text-muted mb-2 small text-truncate" style="max-width: 100%;">{{ $task->deskripsi }}</p>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                                            <small class="text-secondary small" style="font-size: 0.7rem;">
                                                                <i class="ti ti-calendar me-1"></i>{{ $task->due_date ? $task->due_date->format('d/m') : '-' }}
                                                            </small>
                                                            <div class="d-flex align-items-center">
                                                                <small class="fw-bold me-1 text-muted" style="font-size: 0.75rem;">{{ $task->progress }}%</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($statusTasks->isEmpty())
                                                <div class="text-center text-muted py-4 small kanban-empty-placeholder">Kosong</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Member -->
@can('project.edit')
<div class="modal fade" id="modalAddMember" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Tambah Anggota Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('project.addmember', Crypt::encrypt($project->id)) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Get active employees not currently in the project -->
                    @php
                        $assignedNiks = $project->members->pluck('nik')->toArray();
                        $nonMembers = \App\Models\Karyawan::where('status_aktif_karyawan', 1)
                            ->whereNotIn('nik', $assignedNiks)
                            ->orderBy('nama_karyawan')
                            ->get();
                    @endphp
                    
                    <div class="mb-3">
                        <label class="form-label" for="member_nik">Pilih Karyawan</label>
                        <select class="form-select" name="nik" id="member_nik" required>
                            <option value="">Pilih Karyawan...</option>
                            @foreach ($nonMembers as $nm)
                                <option value="{{ $nm->nik }}">{{ $nm->nik_show }} - {{ $nm->nama_karyawan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="member_role">Peran (Role) dalam Project</label>
                        <select class="form-select" name="role" id="member_role" required>
                            <option value="member" selected>Anggota (Member)</option>
                            <option value="leader">Pemimpin Project (Leader)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
<x-modal-form id="modalEditProject" show="loadmodalEditProject" size="modal-xl" title="Edit Project" />
<x-modal-form id="modalCreateTask" show="loadmodalCreateTask" size="modal-xl" title="Tambah Task Baru" />
<x-modal-form id="modalEditTask" show="loadmodalEditTask" size="modal-xl" title="Edit Task" />
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

        $(".btnEditProject").click(function() {
            var href = $(this).attr("data-href");
            $("#modalEditProject").modal("show");
            loading("#loadmodalEditProject");
            $("#loadmodalEditProject").load(href);
        });

        $("#btnCreateTask").click(function() {
            $("#modalCreateTask").modal("show");
            loading("#loadmodalCreateTask");
            $("#loadmodalCreateTask").load("{{ route('project.task.create', Crypt::encrypt($project->id)) }}");
        });

        $(document).on('click', '.btnEditTask', function() {
            var href = $(this).attr("data-href");
            $("#modalEditTask").modal("show");
            loading("#loadmodalEditTask");
            $("#loadmodalEditTask").load(href);
        });

        $('.remove-member-btn').click(function(e) {
            e.preventDefault();
            var form = $(this).closest("form");
            if (confirm("Keluarkan anggota ini dari project?")) {
                form.submit();
            }
        });

        // Kanban Drag & Drop Implementation
        const taskCards = document.querySelectorAll('.kanban-task-card');
        const columns = document.querySelectorAll('.kanban-column');

        taskCards.forEach(card => {
            card.addEventListener('dragstart', () => {
                card.classList.add('dragging');
            });

            card.addEventListener('dragend', () => {
                card.classList.remove('dragging');
            });
        });

        columns.forEach(column => {
            column.addEventListener('dragover', e => {
                e.preventDefault();
                const draggingCard = document.querySelector('.dragging');
                if (draggingCard) {
                    // Remove "Kosong" placeholder if card is dragged over
                    const placeholder = column.querySelector('.kanban-empty-placeholder');
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    column.appendChild(draggingCard);
                }
            });

            column.addEventListener('dragenter', e => {
                e.preventDefault();
                column.classList.add('drag-over');
            });

            column.addEventListener('dragleave', () => {
                column.classList.remove('drag-over');
                
                // Show "Kosong" placeholder again if column becomes empty
                const cards = column.querySelectorAll('.kanban-task-card');
                const placeholder = column.querySelector('.kanban-empty-placeholder');
                if (cards.length === 0 && placeholder) {
                    placeholder.style.display = 'block';
                }
            });

            column.addEventListener('drop', e => {
                e.preventDefault();
                column.classList.remove('drag-over');
                const card = document.querySelector('.dragging');
                if (!card) return;

                const taskId = card.getAttribute('data-id');
                const newStatus = column.getAttribute('data-status');

                // Update column badge counts
                columns.forEach(col => {
                    const count = col.querySelectorAll('.kanban-task-card').length;
                    col.closest('.card').querySelector('.badge').textContent = count;
                    
                    // Toggle empty placeholder visibility
                    const placeholder = col.querySelector('.kanban-empty-placeholder');
                    if (placeholder) {
                        placeholder.style.display = count === 0 ? 'block' : 'none';
                    }
                });

                // Send AJAX to update status
                $.ajax({
                    url: `/project/task/${taskId}/status`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show small toast success message
                            const toastDiv = $('<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">' +
                                '<div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">' +
                                '<div class="d-flex"><div class="toast-body"><i class="ti ti-check me-2"></i>' + response.message + '</div>' +
                                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div></div>');
                            $('body').append(toastDiv);
                            setTimeout(() => toastDiv.fadeOut(() => toastDiv.remove()), 3000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                            setTimeout(() => window.location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal merubah status tugas.'
                        });
                        setTimeout(() => window.location.reload(), 1500);
                    }
                });
            });
        });
    });
</script>
@endpush
