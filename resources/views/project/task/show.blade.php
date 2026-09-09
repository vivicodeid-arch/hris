@extends('layouts.app')
@section('titlepage', 'Detail Task')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Detail Task - {{ $task->kode_task }}
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Detail tugas, progres pengerjaan, komentar, dan lampiran.
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
                <li class="breadcrumb-item">
                    <a href="{{ route('project.show', Crypt::encrypt($project->id)) }}">{{ $project->nama_project }}</a>
                </li>
                <li class="breadcrumb-item active">{{ $task->kode_task }}</li>
            </ol>
        </nav>
    </div>
@endsection

@php
    // Priority Badges
    $badgePrioritas = 'bg-label-secondary';
    if ($task->prioritas === 'high') $badgePrioritas = 'bg-label-warning';
    elseif ($task->prioritas === 'critical') $badgePrioritas = 'bg-label-danger';
    elseif ($task->prioritas === 'low') $badgePrioritas = 'bg-label-success';

    // Status Badges
    $badgeStatus = 'bg-secondary';
    if ($task->status === 'in_progress') $badgeStatus = 'bg-primary';
    elseif ($task->status === 'review') $badgeStatus = 'bg-warning';
    elseif ($task->status === 'completed') $badgeStatus = 'bg-success';
    elseif ($task->status === 'cancelled') $badgeStatus = 'bg-danger';

    // Date calculations / formatting
    $startDateStr = $task->start_date ? $task->start_date->format('d M Y') : '-';
    $dueDateStr = $task->due_date ? $task->due_date->format('d M Y') : '-';

    // File Size Formatter Helper (Local inside blade)
    if (!function_exists('formatBytesLocal')) {
        function formatBytesLocal($bytes, $precision = 2) {
            $units = ['B', 'KB', 'MB', 'GB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);
            return round($bytes, $precision) . ' ' . $units[$pow];
        }
    }
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
    <!-- Left Column: Task Detail, Comments, Logs -->
    <div class="col-xl-8 col-lg-7 col-md-12">
        <!-- Task Detail Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 48px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-info-circle me-2"></i>
                    <h6 class="card-title mb-0 text-white" style="font-size: 0.9rem;">Detail Tugas</h6>
                </div>
                @can('project.task.edit')
                    <a href="javascript:void(0)" id="btnEditTaskDetail" class="btn btn-xs btn-light text-primary" data-href="{{ route('project.task.edit', Crypt::encrypt($task->id)) }}">
                        <i class="ti ti-edit me-1"></i> Edit Task
                    </a>
                @endcan
            </div>
            <div class="card-body py-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge {{ $badgePrioritas }} text-uppercase">{{ $task->prioritas }} PRIORITY</span>
                    <span class="badge {{ $badgeStatus }} text-white text-uppercase">{{ str_replace('_', ' ', $task->status) }}</span>
                    @if($task->parent_id)
                        <span class="badge bg-label-info text-uppercase">SUB-TASK</span>
                    @endif
                </div>

                <h4 class="fw-bold text-dark mb-1">{{ $task->judul }}</h4>
                <p class="text-muted small mb-3">
                    Project: <a href="{{ route('project.show', Crypt::encrypt($project->id)) }}" class="fw-semibold">{{ $project->nama_project }}</a>
                    @if($task->parent)
                        | Parent Task: <a href="{{ route('project.task.show', Crypt::encrypt($task->parent->id)) }}" class="fw-semibold text-info">{{ $task->parent->kode_task }}</a>
                    @endif
                </p>

                <div class="row border rounded-3 p-3 my-4 g-3 bg-white mx-0">
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-label-primary rounded me-2" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="ti ti-calendar fs-4"></i>
                            </span>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">Tanggal Mulai</small>
                                <span class="fw-bold text-dark small" style="font-size: 0.8rem;">{{ $startDateStr }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-label-danger rounded me-2" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="ti ti-calendar-off fs-4"></i>
                            </span>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">Deadline</small>
                                <span class="fw-bold text-dark small" style="font-size: 0.8rem;">{{ $dueDateStr }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-label-info rounded me-2" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="ti ti-user fs-4"></i>
                            </span>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">Dibuat Oleh</small>
                                <span class="fw-bold text-dark small" style="font-size: 0.8rem;" title="{{ $task->creator ? $task->creator->nama_karyawan : 'Administrator' }}">{{ $task->creator ? formatName($task->creator->nama_karyawan) : 'Administrator' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-label-success rounded me-2" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="ti ti-arrows-sort fs-4"></i>
                            </span>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">Urutan Prioritas</small>
                                <span class="fw-bold text-dark small" style="font-size: 0.8rem;">Urutan ke-{{ $task->urutan }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="fw-semibold border-bottom pb-2 text-primary"><i class="ti ti-align-left me-1"></i> Deskripsi Tugas</h5>
                <p class="text-secondary small mb-0" style="white-space: pre-line; line-height: 1.6;">
                    {!! !empty($task->deskripsi) ? e($task->deskripsi) : '<em>Tidak ada deskripsi untuk tugas ini.</em>' !!}
                </p>
            </div>
        </div>

        <!-- Sub-tasks Listing Card -->
        @if($task->subtasks->isNotEmpty() || !$task->parent_id)
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 45px;">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-git-merge me-2"></i>
                        <h6 class="card-title mb-0 text-white" style="font-size: 0.85rem;">Daftar Sub-task</h6>
                    </div>
                    @can('project.task.create')
                        @if(!$task->parent_id)
                            <a href="javascript:void(0)" id="btnCreateSubtask" class="btn btn-xs btn-light text-primary" data-href="{{ route('project.task.create', ['projectId' => Crypt::encrypt($project->id)]) }}?parent_id={{ $task->id }}">
                                <i class="ti ti-plus"></i> Sub-task
                            </a>
                        @endif
                    @endcan
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        @forelse($task->subtasks as $sub)
                            @php
                                $subBadge = 'bg-label-secondary';
                                if ($sub->status === 'in_progress') $subBadge = 'bg-label-primary';
                                elseif ($sub->status === 'review') $subBadge = 'bg-label-warning';
                                elseif ($sub->status === 'completed') $subBadge = 'bg-label-success';
                                elseif ($sub->status === 'cancelled') $subBadge = 'bg-label-danger';
                            @endphp
                            <div class="card shadow-none border" style="border-radius: 8px;">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <!-- Code & Judul -->
                                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                                            <div class="d-flex align-items-center mb-1">
                                                <a href="{{ route('project.task.show', Crypt::encrypt($sub->id)) }}" class="fw-bold text-primary text-decoration-none small" style="font-size: 13.5px;">
                                                    <i class="ti ti-hash me-0.5 fs-5"></i>{{ $sub->kode_task }}
                                                </a>
                                            </div>
                                            <div class="fw-semibold text-dark" style="font-size: 14px;">
                                                <a href="{{ route('project.task.show', Crypt::encrypt($sub->id)) }}" class="text-dark text-decoration-none hover-primary">
                                                    {{ $sub->judul }}
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Tim & Progress -->
                                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                                            <div class="d-flex align-items-center text-muted mb-1" title="Tim Assigned">
                                                <i class="ti ti-users me-1.5 fs-5"></i>
                                                <span style="font-size: 13px;">{{ $sub->members->count() }} Orang</span>
                                            </div>
                                            <div class="d-flex align-items-center" style="min-width: 100px;">
                                                <div class="progress w-100 me-2" style="height: 6px; margin-bottom: 0;">
                                                    <div class="progress-bar bg-info" style="width: {{ $sub->progress }}%"></div>
                                                </div>
                                                <span class="small fw-semibold">{{ $sub->progress }}%</span>
                                            </div>
                                        </div>

                                        <!-- Status Badge -->
                                        <div class="col-6 col-md-3 mb-2 mb-md-0 text-md-center">
                                            <span class="badge {{ $subBadge }} text-uppercase" style="font-size: 10px; padding: 4px 8px;">
                                                {{ str_replace('_', ' ', $sub->status) }}
                                            </span>
                                        </div>

                                        <!-- Action -->
                                        <div class="col-12 col-md-2 text-end">
                                            <a href="{{ route('project.task.show', Crypt::encrypt($sub->id)) }}" class="btn btn-sm btn-outline-secondary py-1 px-3" style="font-size: 12px;" title="Detail Task">
                                                Detail <i class="ti ti-chevron-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted border border-dashed rounded-3 bg-light">
                                <i class="ti ti-git-merge fs-2 mb-2 d-block text-secondary"></i>
                                Tidak ada sub-task terdaftar.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        <!-- Discussion / Comments Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 45px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-messages me-2"></i>
                    <h6 class="card-title mb-0 text-white" style="font-size: 0.85rem;">Diskusi & Komentar ({{ $task->comments->count() }})</h6>
                </div>
            </div>
            <div class="card-body py-4">
                <!-- Add Comment Form -->
                <form action="{{ route('project.task.comment', Crypt::encrypt($task->id)) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-3">
                        <textarea class="form-control" name="komentar" rows="3" placeholder="Tulis tanggapan atau koordinasikan pekerjaan..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-send me-1"></i> Kirim Komentar</button>
                    </div>
                </form>

                <!-- Comments Timeline -->
                <div class="comments-list border-top pt-3" style="max-height: 400px; overflow-y: auto;">
                    @forelse($task->comments->sortByDesc('created_at') as $comment)
                        @php
                            $cKaryawan = $comment->karyawan;
                        @endphp
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded-circle bg-label-info p-1 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-weight: bold;">
                                    {{ $cKaryawan ? strtoupper(substr($cKaryawan->nama_karyawan, 0, 2)) : 'AD' }}
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold text-dark small">{{ $cKaryawan ? $cKaryawan->nama_karyawan : 'Administrator / System' }}</h6>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $comment->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="text-secondary small mb-0" style="white-space: pre-line;">{{ $comment->komentar }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3 small">Belum ada komentar dalam tugas ini. Jadilah yang pertama berkomentar!</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Activity Audit Logs Card -->
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 45px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-history me-2"></i>
                    <h6 class="card-title mb-0 text-white" style="font-size: 0.85rem;">Riwayat Aktivitas & Log</h6>
                </div>
            </div>
            <div class="card-body py-4">
                <div class="timeline position-relative ps-2">
                    @forelse($task->logs->sortByDesc('created_at') as $log)
                        @php
                            $logCreator = $log->karyawan;
                            // Accent icon/color based on action type
                            $iconColor = 'var(--theme-color-1)';
                            $iconClass = 'ti-edit';
                            if ($log->aksi === 'created') {
                                $iconColor = '#28c76f'; // Success green
                                $iconClass = 'ti-plus';
                            } elseif ($log->aksi === 'status_changed') {
                                $iconColor = '#ff9f43'; // Orange
                                $iconClass = 'ti-arrow-right';
                            } elseif ($log->aksi === 'attachment_added') {
                                $iconColor = '#00cfc8'; // Cyan
                                $iconClass = 'ti-paperclip';
                            } elseif ($log->aksi === 'progress_updated') {
                                $iconColor = '#7367f0'; // Purple
                                $iconClass = 'ti-chart-bar';
                            } elseif ($log->aksi === 'attachment_removed') {
                                $iconColor = '#ea5455'; // Red
                                $iconClass = 'ti-trash';
                            }
                        @endphp
                        <div class="timeline-item position-relative mb-3">
                            <!-- Linkage Line -->
                            @if (!$loop->last)
                                <div class="position-absolute" style="left: 13px; top: 28px; bottom: -24px; width: 1px; background-color: #d1d5db; z-index: 1;"></div>
                            @endif
                            
                            <div class="d-flex align-items-start">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center me-3 z-3 position-relative mt-2" style="background-color: {{ $iconColor }}; width: 28px; height: 28px; font-size: 13px; font-weight: bold; flex-shrink: 0; box-shadow: 0 0 0 4px #ffffff;">
                                    <i class="ti {{ $iconClass }}" style="font-size: 14px;"></i>
                                </div>
                                <div class="flex-grow-1 bg-white rounded p-3 border shadow-none" style="border-color: #e5e7eb !important; border-radius: 8px !important;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="fw-bold text-dark d-block small">{{ $log->keterangan }}</span>
                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                Oleh: <strong>{{ $logCreator ? $logCreator->nama_karyawan : 'Admin / System' }}</strong>
                                            </span>
                                        </div>
                                        <small class="text-muted flex-shrink-0" style="font-size: 0.7rem;">{{ $log->created_at->format('d/m H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3 small">Tidak ada log aktivitas.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Status Board, Members, Attachments -->
    <div class="col-xl-4 col-lg-5 col-md-12">
        <!-- Quick Progress Update Card -->
        <div class="card mb-4 shadow-sm border border-primary">
            <div class="card-header py-2 bg-primary text-white" style="min-height: 42px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-chart-donut-2 me-2"></i>
                    <h6 class="card-title mb-0 text-white" style="font-size: 0.85rem;">Status & Progres Kerja</h6>
                </div>
            </div>
            <div class="card-body py-3">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-semibold">Progres Task</small>
                        <small class="fw-bold" style="font-size: 1rem;">{{ $task->progress }}%</small>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $task->progress }}%"></div>
                    </div>
                </div>

                <!-- Update Status Form -->
                <form action="{{ route('project.task.status', Crypt::encrypt($task->id)) }}" method="POST" class="mb-3 border-top pt-3">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-secondary" for="status_select">Ubah Status</label>
                        <select class="form-select form-select-sm" name="status" id="status_select">
                            <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>TO DO</option>
                            <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>IN PROGRESS</option>
                            <option value="review" {{ $task->status === 'review' ? 'selected' : '' }}>REVIEW</option>
                            <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>COMPLETED</option>
                            <option value="cancelled" {{ $task->status === 'cancelled' ? 'selected' : '' }}>CANCELLED</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-xs btn-outline-primary w-100">Simpan Status</button>
                </form>

                <!-- Update Progress Slider Form -->
                <form action="{{ route('project.task.progress', Crypt::encrypt($task->id)) }}" method="POST" class="border-top pt-2">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-secondary d-flex justify-content-between" for="progress_slider">
                            <span>Geser Progres</span>
                            <span id="slider_val" class="text-primary">{{ $task->progress }}%</span>
                        </label>
                        <input type="range" class="form-range" name="progress" id="progress_slider" min="0" max="100" value="{{ $task->progress }}" step="5">
                    </div>
                    <button type="submit" class="btn btn-xs btn-primary w-100">Set Progres</button>
                </form>
            </div>
        </div>

        <!-- Assigned Members Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 45px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-users me-2"></i>
                    <h6 class="card-title mb-0 text-white" style="font-size: 0.85rem;">Anggota Tim Ditugaskan</h6>
                </div>
            </div>
            <div class="card-body py-3">
                <div class="list-group list-group-flush">
                    @forelse ($task->members as $member)
                        @php
                            $tmKaryawan = $member->karyawan;
                        @endphp
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-2">
                                    <span class="avatar-initial rounded-circle bg-label-secondary p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                        <i class="ti ti-user text-muted"></i>
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-semibold small" style="font-size: 0.8rem;">{{ $tmKaryawan ? $tmKaryawan->nama_karyawan : 'Karyawan' }}</div>
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">
                                        NIK: {{ $member->nik }} | {{ $tmKaryawan && $tmKaryawan->jabatan ? $tmKaryawan->jabatan->nama_jabatan : 'Staf' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3 small">Tugas ini belum ditugaskan ke siapapun.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- File Attachments Card -->
        <div class="card shadow-sm">
            <div class="card-header py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 45px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-paperclip me-2"></i>
                    <h6 class="card-title mb-0 text-white" style="font-size: 0.85rem;">Lampiran & Dokumen</h6>
                </div>
            </div>
            <div class="card-body py-3">
                <!-- Upload Attachment Form -->
                <form action="{{ route('project.task.attachment', Crypt::encrypt($task->id)) }}" method="POST" enctype="multipart/form-data" class="mb-3 pb-3 border-bottom">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-secondary" for="file_input">Unggah File (Maks. 10MB)</label>
                        <input class="form-control form-control-sm" type="file" name="file" id="file_input" required>
                    </div>
                    <button type="submit" class="btn btn-xs btn-primary w-100"><i class="ti ti-upload me-1"></i> Unggah Lampiran</button>
                </form>

                <!-- Attachments List -->
                <div class="attachments-list">
                    @forelse($task->attachments as $attach)
                        @php
                            $attKaryawan = $attach->karyawan;
                        @endphp
                        <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2 bg-light">
                            <div class="d-flex align-items-center overflow-hidden me-2">
                                <i class="ti ti-file-text text-primary me-2 fs-4 flex-shrink-0"></i>
                                <div class="overflow-hidden">
                                    <span class="d-block text-dark fw-semibold text-truncate small" style="font-size: 0.75rem;" title="{{ $attach->nama_file }}">
                                        {{ $attach->nama_file }}
                                    </span>
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">
                                        {{ formatBytesLocal($attach->ukuran) }} | Oleh: {{ $attKaryawan ? getNamaDepan($attKaryawan->nama_karyawan) : 'Admin' }}
                                    </small>
                                </div>
                            </div>
                            <div class="d-inline-flex">
                                <a href="{{ asset('storage/' . $attach->path) }}" target="_blank" class="btn btn-xs btn-label-info p-1 me-1" title="Unduh File">
                                    <i class="ti ti-download"></i>
                                </a>
                                <form action="{{ route('project.task.attachment.delete', Crypt::encrypt($attach->id)) }}" method="POST" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-label-danger p-1 delete-attach-btn" title="Hapus File">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3 small">Belum ada lampiran diunggah.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
<x-modal-form id="modalCreateTask" show="loadmodalCreateTask" size="modal-xl" title="Tambah Sub-task Baru" />
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

        $("#btnCreateSubtask").click(function() {
            var href = $(this).attr("data-href");
            $("#modalCreateTask").modal("show");
            loading("#loadmodalCreateTask");
            $("#loadmodalCreateTask").load(href);
        });

        $("#btnEditTaskDetail").click(function() {
            var href = $(this).attr("data-href");
            $("#modalEditTask").modal("show");
            loading("#loadmodalEditTask");
            $("#loadmodalEditTask").load(href);
        });

        // Slider value update indicator
        $('#progress_slider').on('input', function() {
            $('#slider_val').text($(this).val() + '%');
        });

        // Confirmation on deleting attachments
        $('.delete-attach-btn').click(function(e) {
            e.preventDefault();
            var form = $(this).closest("form");
            if (confirm("Apakah Anda yakin ingin menghapus file lampiran ini secara permanen?")) {
                form.submit();
            }
        });
    });
</script>
@endpush
