@extends('layouts.app')
@section('titlepage', 'Transaksi KPI')
@section('content')
@section('navigasi')
    <span>Transaksi KPI</span>
@endsection

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header">
                @if ($active_period)
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <i class="ti ti-calendar me-2"></i> Periode Aktif: <strong>{{ $active_period->nama_periode }}</strong>
                            <span class="text-muted ms-2">({{ date('d M Y', strtotime($active_period->start_date)) }} - {{ date('d M Y', strtotime($active_period->end_date)) }})</span>
                        </div>
                    </div>
                @else
                    <div class="text-danger"><i class="ti ti-alert-triangle me-2"></i> Belum ada Periode KPI yang Aktif</div>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        @if (Session::get('success'))
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                            </div>
                        @endif
                        @if (Session::get('warning'))
                            <div class="alert alert-warning">
                                {{ Session::get('warning') }}
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('kpi.transactions.index') }}" method="GET">
                            <div class="row g-2">
                                <div class="col-lg-3 col-sm-12 col-md-12">
                                    <x-input-with-icon label="Cari Nama Karyawan" value="{{ Request('nama_karyawan') }}"
                                        name="nama_karyawan" icon="ti ti-search" hideLabel />
                                </div>
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <x-select label="Cabang" name="kode_cabang" :data="$cabang" key="kode_cabang" textShow="nama_cabang"
                                        selected="{{ Request('kode_cabang') }}" hideLabel />
                                </div>
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <x-select label="Departemen" name="kode_dept" :data="$departemen" key="kode_dept" textShow="nama_dept"
                                        selected="{{ Request('kode_dept') }}" upperCase="true" hideLabel />
                                </div>
                                <div class="col-lg-3 col-sm-12 col-md-12">
                                    <x-select label="Jabatan" name="kode_jabatan" :data="$jabatan" key="kode_jabatan" textShow="nama_jabatan"
                                        selected="{{ Request('kode_jabatan') }}" upperCase="true" hideLabel />
                                </div>
                                <div class="col-lg-2 col-sm-12 col-md-12">
                                    <button class="btn btn-primary w-100"><i class="ti ti-icons ti-search me-1"></i> Cari</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex flex-column gap-2">
                            @forelse ($karyawan as $item)
                                <div class="card border shadow-none bg-white mb-2">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center g-2">
                                            <!-- Profil Karyawan -->
                                            <div class="col-md-5 col-12">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="ti ti-user fs-4"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-0 fw-bold text-dark">{{ $item->nama_karyawan }}</h5>
                                                        <small class="text-muted d-block">{{ $item->nik }} • {{ $item->nama_jabatan }}</small>
                                                        <small class="text-muted d-block"><i class="ti ti-building fs-6 me-1"></i>{{ $item->nama_dept }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Status KPI, Nilai & Grade -->
                                            <div class="col-md-4 col-12">
                                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                                    <div>
                                                        <small class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 600;">STATUS KPI</small>
                                                        @if (empty($item->kpi_status))
                                                            <span class="badge bg-secondary-subtle text-secondary fw-bold">Belum Diset</span>
                                                        @elseif ($item->kpi_status == 'draft')
                                                            <span class="badge bg-warning-subtle text-warning-emphasis fw-bold">Draft</span>
                                                        @elseif ($item->kpi_status == 'submitted')
                                                            <span class="badge bg-info-subtle text-info-emphasis fw-bold">Submitted</span>
                                                        @elseif ($item->kpi_status == 'approved')
                                                            <span class="badge bg-success-subtle text-success-emphasis fw-bold">Approved</span>
                                                        @endif
                                                    </div>
                                                    <div class="border-start ps-3">
                                                        <small class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 600;">NILAI</small>
                                                        <span class="fw-bold text-dark">{{ !empty($item->total_nilai) ? number_format($item->total_nilai, 2) : '-' }}</span>
                                                    </div>
                                                    <div class="border-start ps-3">
                                                        <small class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 600;">GRADE</small>
                                                        @if(!empty($item->grade))
                                                            <span class="badge bg-primary-subtle text-primary fw-bold">{{ $item->grade }}</span>
                                                        @else
                                                            <span class="text-muted fw-bold">-</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Aksi -->
                                            <div class="col-md-3 col-12 text-md-end text-start mt-md-0 mt-2">
                                                @if ($active_period)
                                                     @if (empty($item->kpi_id))
                                                         <a href="{{ route('kpi.transactions.settarget', $item->nik) }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-primary">
                                                             <i class="ti ti-target me-1"></i> Set Target
                                                         </a>
                                                     @else
                                                         <a href="{{ route('kpi.transactions.show', $item->kpi_id) }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-success">
                                                             <i class="ti ti-file-analytics me-1"></i> Lihat KPI
                                                         </a>
                                                     @endif
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger" style="font-size: 11px;"><i class="ti ti-lock me-1"></i>Periode Non-Aktif</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="card shadow-none border">
                                    <div class="card-body text-center py-5">
                                        <i class="ti ti-folder-off text-muted" style="font-size: 48px;"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada data karyawan ditemukan.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            {{ $karyawan->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
