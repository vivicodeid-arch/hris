@extends('layouts.mobile.modern')
@section('title', 'Project Board')

@section('header_left')
    <a href="{{ route('shortcut.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        body {
            background-color: #f1f5f9;
        }

        .summary-card {
            background: linear-gradient(135deg, var(--color-nav) 0%, #1e293b 100%);
            border-radius: 14px;
            padding: 14px 18px;
            color: white;
            margin-bottom: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .summary-card .card-wave {
            position: absolute;
            bottom: -15px;
            right: -15px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.015);
            border-radius: 50%;
        }

        .summary-card .card-wave-2 {
            position: absolute;
            bottom: -20px;
            right: 10px;
            width: 80px;
            height: 80px;
            background: transparent;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.02);
        }

        .project-item {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            display: block;
            color: inherit;
            text-decoration: none;
            transition: transform 0.1s ease;
            overflow: hidden;
        }

        .project-item:active {
            transform: scale(0.98);
        }

        .project-header {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .project-body {
            padding: 12px 16px;
        }

        .project-footer {
            padding: 10px 16px;
            background-color: #f8fafc;
            border-top: 1px solid #f1f5f9;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
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
        }

        .progress-track {
            width: 100%;
            height: 5px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
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

        {{-- Summary Card --}}
        <div class="summary-card fade-in">
            <div class="card-wave"></div>
            <div class="card-wave-2"></div>

            <div class="flex justify-between items-start mb-4 relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-white/60 uppercase tracking-[1px]">Ringkasan Project</p>
                    <h2 class="text-xl font-black mt-1 tracking-tight text-white">{{ $projects->count() }} Project</h2>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                    <ion-icon name="briefcase" class="text-white text-xl"></ion-icon>
                </div>
            </div>

            <div class="flex justify-between items-end pt-3 border-t border-white/10 relative z-10">
                <div>
                    <p class="text-[9px] text-white/50 uppercase font-bold mb-1">Tugas Aktif</p>
                    <p class="text-[14px] font-bold">{{ $tasksCount }} Tugas</p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] text-white/50 uppercase font-bold mb-1">Selesai</p>
                    <p class="text-[14px] font-bold">{{ $projects->where('status', 'completed')->count() }} Project</p>
                </div>
            </div>
        </div>

        {{-- Project List --}}
        <div class="space-y-2">
            <h4 class="text-[12px] font-bold text-slate-500 px-1 mb-2 uppercase tracking-wide">Daftar Project</h4>

            @forelse($projects as $index => $proj)
                @php
                    $leader = $proj->members->where('role', 'leader')->first();
                    $leaderName = $leader && $leader->karyawan ? $leader->karyawan->nama_karyawan : 'Belum ditentukan';

                    $statusClass = 'status-' . $proj->status;
                    $statusMap = [
                        'planning' => 'PLANNING',
                        'in_progress' => 'IN PROGRESS',
                        'completed' => 'SELESAI',
                        'on_hold' => 'ON HOLD',
                        'cancelled' => 'BATAL'
                    ];
                    $statusText = $statusMap[$proj->status] ?? strtoupper($proj->status);

                    $progColor = '#10b981';
                    if ($proj->status === 'on_hold') $progColor = '#f59e0b';
                    elseif ($proj->status === 'cancelled') $progColor = '#ef4444';
                    elseif ($proj->progress >= 100) $progColor = '#14b8a6';
                @endphp
                <a href="{{ route('myproject.show', Crypt::encrypt($proj->id)) }}" class="project-item fade-in" style="animation-delay: {{ ($index * 0.05) + 0.1 }}s;">
                    <div class="project-header">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-[34px] h-[34px] flex items-center justify-center rounded-lg text-white font-bold text-[12px] flex-shrink-0"
                                    style="background-color: {{ $proj->category->warna ?? '#64748b' }};">
                                    {{ strtoupper(substr($proj->nama_project, 0, 2)) }}
                                </span>
                                <div class="min-w-0">
                                    <h3 class="text-[13px] font-bold text-slate-800 truncate leading-tight">{{ $proj->nama_project }}</h3>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ $proj->kode_project }}</span>
                                </div>
                            </div>
                            <span class="status-badge {{ $statusClass }} flex-shrink-0 ml-2">{{ $statusText }}</span>
                        </div>
                    </div>

                    <div class="project-body">
                        <div class="info-row">
                            <span class="info-label">Leader</span>
                            <span class="info-value">{{ formatName($leaderName) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Deadline</span>
                            <span class="info-value">{{ $proj->end_date->format('d M Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Anggota</span>
                            <span class="info-value">{{ $proj->members->count() }} orang</span>
                        </div>
                    </div>

                    <div class="project-footer">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Progress</span>
                            <span class="text-[11px] font-bold text-slate-700">{{ $proj->progress }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $proj->progress }}%; background-color: {{ $progColor }};"></div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-12 text-slate-400 bg-white border border-slate-200 rounded-xl fade-in">
                    <ion-icon name="briefcase-outline" class="text-3xl text-slate-300 mb-2"></ion-icon>
                    <p class="text-[12px] font-medium">Anda belum bergabung di project apapun.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
