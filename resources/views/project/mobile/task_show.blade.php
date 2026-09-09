@extends('layouts.mobile.modern')
@section('title', 'Detail Tugas')

@php
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

@section('header_left')
    <a href="{{ route('myproject.show', Crypt::encrypt($task->project_id)) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        body {
            background-color: #f1f5f9;
        }

        .section-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .section-header {
            padding: 10px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-header h4 {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .section-body {
            padding: 12px 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            text-align: right;
        }

        .status-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .status-planning, .status-todo { background: #f1f5f9; color: #475569; }
        .status-in_progress { background: #dbeafe; color: #1d4ed8; }
        .status-completed { background: #dcfce7; color: #15803d; }
        .status-on_hold, .status-review { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #fee2e2; color: #b91c1c; }

        .priority-low { background: #dcfce7; color: #15803d; }
        .priority-medium, .priority-normal { background: #dbeafe; color: #1d4ed8; }
        .priority-high { background: #fef3c7; color: #92400e; }
        .priority-critical { background: #fee2e2; color: #b91c1c; }

        .progress-track {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
            background-color: var(--color-nav);
            transition: width 0.3s ease;
        }

        /* Range Slider */
        .range-slider {
            -webkit-appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background: #e2e8f0;
            outline: none;
            margin: 8px 0;
        }

        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--color-nav);
            cursor: pointer;
            border: 2px solid #ffffff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }

        .range-slider::-webkit-slider-thumb:active {
            transform: scale(1.15);
        }

        /* Avatar */
        .avatar-initial {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 10px;
            background: #e2e8f0;
            color: #475569;
        }

        /* File list */
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .file-item:last-child {
            border-bottom: none;
        }

        /* Comment item */
        .comment-item {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .comment-item:last-child {
            border-bottom: none;
        }

        /* Subtask item */
        .subtask-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .subtask-item:last-child {
            border-bottom: none;
        }

        /* Form textarea */
        .comment-textarea {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 12px;
            font-family: 'Inter', sans-serif;
            resize: vertical;
            min-height: 60px;
            outline: none;
            transition: border-color 0.2s;
        }

        .comment-textarea:focus {
            border-color: var(--color-nav);
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.1s ease, opacity 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .btn-submit:active {
            transform: scale(0.97);
        }

        .btn-primary {
            background-color: var(--color-nav);
            color: #ffffff;
        }

        .btn-dark {
            background-color: #1e293b;
            color: #ffffff;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out forwards;
            opacity: 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endpush

@section('content')
    <div class="px-1 pt-1 pb-24">

        {{-- Task Info Card --}}
        <div class="section-card fade-in">
            <div class="section-header">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] text-slate-400 font-bold">{{ $task->kode_task }}</span>
                    <div class="flex gap-1">
                        <span class="status-badge priority-{{ $task->prioritas }}">{{ strtoupper($task->prioritas) }}</span>
                        <span class="status-badge status-{{ $task->status }}">{{ str_replace('_', ' ', strtoupper($task->status)) }}</span>
                    </div>
                </div>
            </div>

            <div class="section-body">
                <h3 class="text-[14px] font-bold text-slate-800 leading-snug mb-3">{{ $task->judul }}</h3>

                @if($task->deskripsi)
                    <p class="text-[12px] text-slate-500 leading-relaxed mb-3 pb-3 border-b border-slate-100" style="white-space: pre-line;">{{ $task->deskripsi }}</p>
                @endif

                <div class="info-row">
                    <span class="info-label">Tanggal Mulai</span>
                    <span class="info-value">{{ $task->start_date ? $task->start_date->format('d M Y') : '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Deadline</span>
                    <span class="info-value">{{ $task->due_date ? $task->due_date->format('d M Y') : '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Progress</span>
                    <span class="info-value" style="color: var(--color-nav);">{{ $task->progress }}%</span>
                </div>
            </div>
        </div>

        @php
            $nik = DB::table('users_karyawan')->where('id_user', Auth::user()->id)->value('nik');
            $isAssigned = $task->members->contains('nik', $nik);
        @endphp

        {{-- Progress Update Card --}}
        <div class="section-card fade-in" style="animation-delay: 0.05s;">
            <div class="section-header">
                <h4>Update Progress</h4>
            </div>
            <div class="section-body">
                @if($isAssigned)
                    <form action="{{ route('myproject.task.progress', Crypt::encrypt($task->id)) }}" method="POST">
                        @csrf
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[11px] text-slate-500 font-medium">Geser untuk update</span>
                            <span id="slider_percentage" class="text-[16px] font-bold" style="color: var(--color-nav);">{{ $task->progress }}%</span>
                        </div>
                        <input type="range" name="progress" id="progress_input" min="0" max="100" value="{{ $task->progress }}" step="5" class="range-slider">
                        <div class="flex justify-between text-[10px] text-slate-400 font-medium mb-3">
                            <span>0%</span>
                            <span>100%</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-[11px] text-slate-500 font-semibold block mb-1">Status Tugas</label>
                            <select name="status" id="status_input" class="comment-textarea py-2 px-3" style="min-height: auto; width: 100%; display: block; background-color: #fff;">
                                <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>To Do</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="review" {{ $task->status === 'review' ? 'selected' : '' }}>Review / Testing</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $task->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit btn-primary">Simpan Progress & Status</button>
                    </form>
                @else
                    <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 12px; display: flex; align-items: center; gap: 8px;">
                        <ion-icon name="lock-closed-outline" style="color: #d97706; font-size: 18px; flex-shrink: 0;"></ion-icon>
                        <p style="margin: 0; font-size: 11px; color: #b45309; font-weight: 600;">Hanya anggota yang ditugaskan yang dapat memperbarui progress tugas ini.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sub-tasks --}}
        @if($task->subtasks->isNotEmpty())
            <div class="section-card fade-in" style="animation-delay: 0.1s;">
                <div class="section-header">
                    <h4>Sub-task ({{ $task->subtasks->count() }})</h4>
                </div>
                <div class="section-body" style="padding-top: 4px; padding-bottom: 4px;">
                    @foreach($task->subtasks as $sub)
                        <div class="subtask-item">
                            <div class="min-w-0 pr-2" style="flex: 1;">
                                <span class="text-[10px] font-medium text-slate-400 block">{{ $sub->kode_task }}</span>
                                <span class="text-[12px] font-bold text-slate-700 block truncate mt-0.5">{{ $sub->judul }}</span>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="status-badge status-{{ $sub->status }}" style="font-size: 9px;">{{ str_replace('_', ' ', strtoupper($sub->status)) }}</span>
                                <span class="text-[11px] font-bold text-slate-700">{{ $sub->progress }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Attachments --}}
        <div class="section-card fade-in" style="animation-delay: 0.15s;">
            <div class="section-header">
                <h4>Lampiran ({{ $task->attachments->count() }})</h4>
            </div>
            <div class="section-body">
                @if($isAssigned)
                    <form action="{{ route('myproject.task.attachment', Crypt::encrypt($task->id)) }}" method="POST" enctype="multipart/form-data" class="mb-3 pb-3 border-b border-slate-100">
                        @csrf
                        <input type="file" name="file" required class="block w-full text-[11px] text-slate-500 mb-2
                            file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                            file:text-[11px] file:font-semibold file:bg-slate-100 file:text-slate-700">
                        <button type="submit" class="btn-submit btn-dark">Upload Lampiran</button>
                    </form>
                @else
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; display: flex; align-items: center; gap: 6px; margin-bottom: 12px;">
                        <ion-icon name="lock-closed-outline" style="color: #64748b; font-size: 16px; flex-shrink: 0;"></ion-icon>
                        <p style="margin: 0; font-size: 10px; color: #64748b; font-weight: 600;">Hanya anggota yang ditugaskan yang dapat mengunggah lampiran.</p>
                    </div>
                @endif

                @forelse($task->attachments as $attach)
                    <div class="file-item">
                        <div class="flex items-center gap-2 min-w-0">
                            <ion-icon name="document-text-outline" class="text-slate-400 text-lg flex-shrink-0"></ion-icon>
                            <div class="min-w-0">
                                <span class="text-[12px] font-bold text-slate-700 block truncate">{{ $attach->nama_file }}</span>
                                <span class="text-[9px] text-slate-400 block mt-0.5">
                                    {{ formatBytesLocal($attach->ukuran) }} &middot; {{ $attach->karyawan ? getNamaDepan($attach->karyawan->nama_karyawan) : 'Admin' }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $attach->path) }}" target="_blank"
                            class="w-7 h-7 flex items-center justify-center rounded-lg flex-shrink-0"
                            style="background: rgba(0,0,0,0.04);">
                            <ion-icon name="download-outline" class="text-slate-600 text-sm"></ion-icon>
                        </a>
                    </div>
                @empty
                    <p class="text-center text-[11px] text-slate-400 py-2">Belum ada lampiran.</p>
                @endforelse
            </div>
        </div>

        {{-- Comments --}}
        <div class="section-card fade-in" style="animation-delay: 0.2s;">
            <div class="section-header">
                <h4>Diskusi ({{ $task->comments->count() }})</h4>
            </div>
            <div class="section-body">
                <form action="{{ route('myproject.task.comment', Crypt::encrypt($task->id)) }}" method="POST" class="mb-3">
                    @csrf
                    <textarea name="komentar" rows="2" placeholder="Tulis komentar..." required class="comment-textarea mb-2"></textarea>
                    <button type="submit" class="btn-submit btn-primary">Kirim Komentar</button>
                </form>

                <div style="max-height: 300px; overflow-y: auto;">
                    @forelse($task->comments->sortByDesc('created_at') as $comment)
                        @php
                            $cKaryawan = $comment->karyawan;
                        @endphp
                        <div class="comment-item">
                            <div class="flex items-start gap-2.5">
                                <span class="avatar-initial flex-shrink-0">
                                    {{ $cKaryawan ? strtoupper(substr($cKaryawan->nama_karyawan, 0, 2)) : 'AD' }}
                                </span>
                                <div class="min-w-0" style="flex: 1;">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="text-[12px] font-bold text-slate-800 truncate pr-2">
                                            {{ $cKaryawan ? formatName($cKaryawan->nama_karyawan) : 'Admin' }}
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-medium flex-shrink-0">
                                            {{ $comment->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="text-[12px] text-slate-500 leading-normal" style="white-space: pre-line;">{{ $comment->komentar }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-[11px] text-slate-400 py-3">Belum ada komentar.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
@endsection

@push('myscript')
    <script>
        $(function() {
            $('#progress_input').on('input', function() {
                var progress = $(this).val();
                $('#slider_percentage').text(progress + '%');
                
                if (progress == 100) {
                    $('#status_input').val('completed');
                } else if ($('#status_input').val() == 'completed' && progress < 100) {
                    $('#status_input').val('in_progress');
                }
            });

            $('#status_input').on('change', function() {
                var status = $(this).val();
                if (status == 'completed') {
                    $('#progress_input').val(100);
                    $('#slider_percentage').text('100%');
                } else if (status == 'todo') {
                    $('#progress_input').val(0);
                    $('#slider_percentage').text('0%');
                } else if (status == 'in_progress' && $('#progress_input').val() == 0) {
                    $('#progress_input').val(10);
                    $('#slider_percentage').text('10%');
                }
            });
        });
    </script>
@endpush
