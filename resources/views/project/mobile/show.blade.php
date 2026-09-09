@extends('layouts.mobile.modern')
@section('title', 'Detail Project')

@section('header_left')
    <a href="{{ route('myproject.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        body {
            background-color: #f1f5f9;
        }

        .detail-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .detail-header {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-body {
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
            max-width: 60%;
        }

        .status-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .status-planning { background: #f1f5f9; color: #475569; }
        .status-in_progress { background: #dbeafe; color: #1d4ed8; }
        .status-completed { background: #dcfce7; color: #15803d; }
        .status-on_hold { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #fee2e2; color: #b91c1c; }
        .status-review { background: #fef3c7; color: #92400e; }
        .status-todo { background: #f1f5f9; color: #475569; }

        .priority-low { background: #dcfce7; color: #15803d; }
        .priority-medium { background: #dbeafe; color: #1d4ed8; }
        .priority-high { background: #fef3c7; color: #92400e; }
        .priority-critical { background: #fee2e2; color: #b91c1c; }
        .priority-normal { background: #f1f5f9; color: #475569; }

        .tab-wrapper {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 12px;
            background: #ffffff;
            border-radius: 12px 12px 0 0;
            overflow: hidden;
        }

        .tab-btn {
            flex: 1;
            text-align: center;
            padding: 10px 8px;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            background: transparent;
            border: none;
            cursor: pointer;
            position: relative;
            transition: color 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tab-btn.active {
            color: var(--color-nav);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 16px;
            right: 16px;
            height: 2px;
            background-color: var(--color-nav);
            border-radius: 2px;
        }

        .task-item {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            display: block;
            color: inherit;
            text-decoration: none;
            overflow: hidden;
            transition: transform 0.1s ease;
        }

        .task-item:active {
            transform: scale(0.98);
        }

        .task-header {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .task-body {
            padding: 10px 14px;
        }

        .progress-track {
            width: 100%;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 2px;
            background-color: var(--color-nav);
        }

        .members-row {
            display: flex;
            padding: 8px 16px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }

        .member-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #e2e8f0;
            border: 2px solid #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 700;
            color: #475569;
            margin-left: -6px;
        }

        .member-avatar:first-child {
            margin-left: 0;
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

        {{-- Project Info --}}
        <div class="detail-card fade-in">
            <div class="detail-header">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-[34px] h-[34px] flex items-center justify-center rounded-lg text-white font-bold text-[12px] flex-shrink-0"
                            style="background-color: {{ $project->category->warna ?? '#64748b' }};">
                            {{ strtoupper(substr($project->nama_project, 0, 2)) }}
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-[14px] font-bold text-slate-800 truncate leading-tight">{{ $project->nama_project }}</h3>
                            <span class="text-[10px] text-slate-400 font-medium">{{ $project->kode_project }} &middot; {{ $project->category->nama_kategori ?? 'Umum' }}</span>
                        </div>
                    </div>
                    @php
                        $statusMap = [
                            'planning' => 'PLANNING',
                            'in_progress' => 'IN PROGRESS',
                            'completed' => 'SELESAI',
                            'on_hold' => 'ON HOLD',
                            'cancelled' => 'BATAL'
                        ];
                        $statusText = $statusMap[$project->status] ?? strtoupper($project->status);
                    @endphp
                    @if(isset($isLeader) && $isLeader)
                        <form action="{{ route('myproject.status', Crypt::encrypt($project->id)) }}" method="POST" id="form-update-status" class="m-0 flex-shrink-0 ml-2">
                            @csrf
                            <select name="status" onchange="this.form.submit()" class="status-badge status-{{ $project->status }} border-0 cursor-pointer text-xs font-bold py-1 px-3 rounded" style="outline: none; -webkit-appearance: none; -moz-appearance: none; appearance: none; padding-right: 1.25rem; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23475569%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.35rem center; background-size: 0.4rem auto;">
                                <option value="planning" {{ $project->status === 'planning' ? 'selected' : '' }}>PLANNING</option>
                                <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>IN PROGRESS</option>
                                <option value="on_hold" {{ $project->status === 'on_hold' ? 'selected' : '' }}>ON HOLD</option>
                                <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>SELESAI</option>
                                <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>BATAL</option>
                            </select>
                        </form>
                    @else
                        <span class="status-badge status-{{ $project->status }} flex-shrink-0 ml-2">{{ $statusText }}</span>
                    @endif
                </div>
            </div>

            <div class="detail-body">
                <div class="info-row">
                    <span class="info-label">Tanggal Mulai</span>
                    <span class="info-value">{{ $project->start_date->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Deadline</span>
                    <span class="info-value">{{ $project->end_date->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Anggaran</span>
                    <span class="info-value">{{ $project->budget ? 'Rp ' . formatRupiah($project->budget) : '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Progress</span>
                    <span class="info-value" style="color: var(--color-nav);">{{ $project->progress }}%</span>
                </div>
            </div>

            @if($project->deskripsi)
                <div style="padding: 0 16px 12px 16px; border-top: 1px solid #f1f5f9; padding-top: 10px;">
                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Deskripsi</p>
                    <p class="text-[12px] text-slate-600 leading-relaxed" style="white-space: pre-line;">{{ $project->deskripsi }}</p>
                </div>
            @endif

            {{-- Members --}}
            <div class="members-row">
                <div class="flex items-center justify-between w-full">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Tim ({{ $project->members->count() }})</span>
                    <div class="flex items-center">
                        @foreach($project->members->take(5) as $member)
                            @php
                                $mKaryawan = $member->karyawan;
                                $initials = $mKaryawan ? strtoupper(substr($mKaryawan->nama_karyawan, 0, 2)) : 'M';
                            @endphp
                            <span class="member-avatar" title="{{ $mKaryawan ? $mKaryawan->nama_karyawan : '' }}">{{ $initials }}</span>
                        @endforeach
                        @if($project->members->count() > 5)
                            <span class="member-avatar" style="background: #94a3b8; color: #ffffff;">+{{ $project->members->count() - 5 }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Task Tabs --}}
        <div class="tab-wrapper fade-in" style="animation-delay: 0.1s;">
            <button id="tab-my" class="tab-btn active">Tugas Saya ({{ $myTasks->count() }})</button>
            <button id="tab-all" class="tab-btn">Tugas Lain ({{ $otherTasks->count() }})</button>
        </div>

        {{-- Task List --}}
        <div id="tasks-container" class="space-y-2">
            {{-- Rendered by JS --}}
        </div>
    </div>
@endsection

@push('myscript')
    @php
        $myTasksData = $myTasks->map(function($t) {
            return [
                'id' => Crypt::encrypt($t->id),
                'kode' => $t->kode_task,
                'judul' => $t->judul,
                'progress' => $t->progress,
                'due' => $t->due_date ? $t->due_date->format('d M Y') : '-',
                'status' => $t->status,
                'statusText' => str_replace('_', ' ', strtoupper($t->status)),
                'prioritas' => $t->prioritas,
                'membersCount' => $t->members->count()
            ];
        });

        $otherTasksData = $otherTasks->map(function($t) {
            return [
                'id' => Crypt::encrypt($t->id),
                'kode' => $t->kode_task,
                'judul' => $t->judul,
                'progress' => $t->progress,
                'due' => $t->due_date ? $t->due_date->format('d M Y') : '-',
                'status' => $t->status,
                'statusText' => str_replace('_', ' ', strtoupper($t->status)),
                'prioritas' => $t->prioritas,
                'membersCount' => $t->members->count()
            ];
        });
    @endphp
    <script>
        $(function() {
            var myTasks = @json($myTasksData);
            var otherTasks = @json($otherTasksData);

            function renderTasks(list) {
                var html = '';
                if (list.length === 0) {
                    html = '<div class="text-center py-10 text-slate-400 bg-white border border-slate-200 rounded-xl">' +
                           '<ion-icon name="checkbox-outline" class="text-3xl text-slate-300 mb-1"></ion-icon>' +
                           '<p class="text-[12px] font-medium">Tidak ada tugas terdaftar.</p>' +
                           '</div>';
                    $('#tasks-container').html(html);
                    return;
                }

                list.forEach(function(t, i) {
                    var statusClass = 'status-' + t.status;
                    var priorClass = 'priority-' + t.prioritas;

                    html += '<a href="/myproject/task/' + t.id + '" class="task-item fade-in" style="animation-delay:' + ((i * 0.03) + 0.15) + 's;">' +
                        '<div class="task-header">' +
                            '<div class="flex justify-between items-center">' +
                                '<div class="min-w-0 pr-2">' +
                                    '<h4 class="text-[13px] font-bold text-slate-800 truncate leading-tight">' + t.judul + '</h4>' +
                                    '<span class="text-[10px] text-slate-400 font-medium">' + t.kode + '</span>' +
                                '</div>' +
                                '<div class="flex gap-1 flex-shrink-0">' +
                                    '<span class="status-badge ' + statusClass + '">' + t.statusText + '</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="task-body">' +
                            '<div class="flex justify-between items-center mb-2">' +
                                '<span class="text-[11px] text-slate-500 font-medium">Deadline: ' + t.due + '</span>' +
                                '<span class="text-[11px] font-bold text-slate-700">' + t.progress + '%</span>' +
                            '</div>' +
                            '<div class="progress-track">' +
                                '<div class="progress-fill" style="width:' + t.progress + '%;"></div>' +
                            '</div>' +
                        '</div>' +
                    '</a>';
                });

                $('#tasks-container').html(html);
            }

            renderTasks(myTasks);

            $('#tab-my').click(function() {
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                renderTasks(myTasks);
            });

            $('#tab-all').click(function() {
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                renderTasks(otherTasks);
            });
        });
    </script>
@endpush
