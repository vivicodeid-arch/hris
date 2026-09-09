@extends('layouts.app')
@section('titlepage', 'Log Mesin Presensi')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Log Mesin Presensi
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Monitoring data log mentah dari mesin presensi fingerprint/RFID.
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
                        <i class="ti ti-adjustments-alt ti-xs me-1"></i> Utilities
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="ti ti-device-floppy ti-xs me-1"></i> Log Mesin
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <form action="{{ route('logmesin.index') }}" method="GET">
            <div class="row g-2 mb-3 align-items-end">
                <div class="col-lg-3 col-md-4 col-sm-12">
                    <x-input-with-icon label="Nama Karyawan / PIN" value="{{ request('nama_karyawan') }}" name="nama_karyawan"
                        icon="ti ti-user" hideLabel placeholder="Nama / PIN..." />
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                    <x-input-with-icon label="Tanggal Awal" value="{{ request('tanggal_awal') }}" name="tanggal_awal"
                        icon="ti ti-calendar" hideLabel placeholder="Tanggal Awal" datepicker="flatpickr-date" />
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12">
                    <x-input-with-icon label="Tanggal Akhir" value="{{ request('tanggal_akhir') }}" name="tanggal_akhir"
                        icon="ti ti-calendar" hideLabel placeholder="Tanggal Akhir" datepicker="flatpickr-date" />
                </div>
                <div class="col-lg-3 col-md-2 col-sm-12 mb-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="ti ti-search me-1"></i> Cari
                        </button>
                        <a href="{{ route('logmesin.index') }}" class="btn btn-label-secondary p-2" title="Reset">
                            <i class="ti ti-refresh"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-device-floppy me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Data Log Mesin</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="text-white py-3" width="50">NO.</th>
                                <th class="text-white py-3" width="100">PIN</th>
                                <th class="text-white py-3 border-start">KARYAWAN</th>
                                <th class="text-white py-3 border-start">JAM ABSEN</th>
                                <th class="text-white py-3 border-start">SCAN</th>
                                <th class="text-white py-3 border-start">MESIN</th>
                                <th class="text-white py-3 border-start">STATUS</th>
                                <th class="text-white py-3 border-start">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="py-2">{{ $logs->firstItem() + $loop->index }}</td>
                                    <td class="py-2">
                                        <span class="badge bg-label-primary">{{ $log->pin }}</span>
                                    </td>
                                    <td class="py-2 border-start fw-bold">
                                        @if($log->nama_karyawan)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-primary shadow-sm" style="font-size: 0.75rem;">
                                                        {{ strtoupper(substr($log->nama_karyawan, 0, 1)) }}
                                                    </span>
                                                </div>
                                                {{ $log->nama_karyawan }}
                                            </div>
                                        @else
                                            <span class="text-danger italic">Tidak ditemukan</span>
                                        @endif
                                    </td>
                                    <td class="py-2 border-start">
                                        <div class="d-flex flex-column">
                                            <span>{{ \Carbon\Carbon::parse($log->jam_absen)->format('d/m/Y') }}</span>
                                            <small class="text-primary" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($log->jam_absen)->format('H:i:s') }}</small>
                                        </div>
                                    </td>
                                    <td class="py-2 border-start">
                                        @php
                                            $status_scan = $log->status_scan;
                                            if($status_scan == '0') $btn = 'info';
                                            elseif($status_scan == '1') $btn = 'warning';
                                            elseif($status_scan == '2') $btn = 'primary';
                                            elseif($status_scan == '3') $btn = 'danger';
                                            elseif($status_scan == '4') $btn = 'success';
                                            elseif($status_scan == '5') $btn = 'secondary';
                                            else $btn = 'dark';
                                        @endphp
                                        <span class="badge bg-{{ $btn }}">{{ $status_scan }}</span>
                                    </td>
                                    <td class="py-2 border-start">
                                        {{ $log->nama_mesin ?? 'ID: ' . $log->id_mesin }}
                                    </td>
                                    <td class="py-2 border-start">
                                        @if($log->status == 1)
                                            <span class="badge bg-label-success">Success</span>
                                        @else
                                            <span class="badge bg-label-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td class="py-2 border-start">
                                        <small class="text-muted">{{ $log->keterangan }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center text-muted">
                                            <i class="ti ti-info-circle fs-1 mb-2"></i>
                                            <p class="mb-0">Tidak ada log mesin yang ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection

@push('myscript')
<script>
    $(function() {
        $('.flatpickr-date').flatpickr({
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    });
</script>
@endpush
