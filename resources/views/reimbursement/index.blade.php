@extends('layouts.app')
@section('titlepage', 'Pengajuan Reimbursement')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Pengajuan Reimbursement
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Daftar histori pengajuan klaim biaya karyawan.
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard.index') }}"><i class="ti ti-home-2 ti-xs"></i></a>
                </li>
                <li class="breadcrumb-item active">Reimbursement</li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                @can('reimbursement.create')
                    <a href="{{ route('reimbursement.create') }}" class="btn text-white me-2" style="background: var(--theme-color-1) !important;">
                        <i class="ti ti-plus me-1"></i> Buat Pengajuan
                    </a>
                @endcan
            </div>
            <div>
                @can('approvallayer.index')
                    <a href="{{ route('approvallayer.index', ['feature' => 'REIMBURSEMENT']) }}" class="btn btn-info">
                        <i class="ti ti-settings me-1"></i> Konfigurasi Approval
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->getRoleNames()->first() != 'karyawan')
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <form action="{{ route('reimbursement.index') }}" method="GET">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <x-input-with-icon label="Cari Nama Karyawan" value="{{ request('nama_karyawan') }}" name="nama_karyawan" icon="ti ti-search" hideLabel />
                        </div>
                        <div class="col-md-2">
                            <x-select label="Cabang" name="kode_cabang" :data="$cabang" key="kode_cabang" textShow="nama_cabang" selected="{{ request('kode_cabang') }}" hideLabel />
                        </div>
                        <div class="col-md-2">
                            <x-select label="Departemen" name="kode_dept" :data="$departemen" key="kode_dept" textShow="nama_dept" selected="{{ request('kode_dept') }}" upperCase="true" hideLabel />
                        </div>
                        <div class="col-md-2">
                            <x-input-with-icon label="Dari" value="{{ request('dari') }}" name="dari" icon="ti ti-calendar" hideLabel datepicker="flatpickr-date" placeholder="Dari Tanggal" />
                        </div>
                        <div class="col-md-2">
                            <x-input-with-icon label="Sampai" value="{{ request('sampai') }}" name="sampai" icon="ti ti-calendar" hideLabel datepicker="flatpickr-date" placeholder="Sampai Tanggal" />
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100 px-0">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center py-3" style="background: linear-gradient(to right, var(--theme-color-1), var(--theme-color-2)); color: white !important;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-history me-2 fs-4"></i>
                    <h6 class="card-title mb-0 text-white">Histori Pengajuan Klaim</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-nowrap">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="py-3 px-4 text-white">NO. REIMBURSEMENT</th>
                                <th class="py-3 text-white">NIK</th>
                                <th class="py-3 text-white">NAMA KARYAWAN</th>
                                <th class="py-3 text-white">JABATAN</th>
                                <th class="py-3 text-white">DEPARTEMEN</th>
                                <th class="py-3 text-white">TGL AJU</th>
                                <th class="py-3 text-end text-white">TOTAL KLAIM</th>
                                <th class="py-3 text-center text-white">STATUS</th>
                                <th class="py-3 text-center text-white" style="width: 150px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reimbursement as $d)
                                <tr>
                                    <td class="fw-bold px-4">
                                        <span class="text-primary">{{ $d->no_reimbursement }}</span>
                                    </td>
                                    <td>{{ $d->nik_show ?? $d->nik }}</td>
                                    <td class="fw-bold text-dark">{{ $d->nama_karyawan }}</td>
                                    <td>{{ $d->nama_jabatan ?? '-' }}</td>
                                    <td>{{ $d->nama_dept }}</td>
                                    <td>{{ date('d-m-Y', strtotime($d->tanggal_pengajuan)) }}</td>
                                    <td class="text-end fw-bold text-success">
                                        Rp {{ number_format($d->total_nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        {!! $d->status_label !!}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex border rounded overflow-hidden shadow-xs">
                                            <a href="#" class="btn btn-sm btnShow px-2 py-1 border-0 rounded-0"
                                                id_reimbursement="{{ Crypt::encrypt($d->id) }}" title="Detail"
                                                style="background: #f8f9fa;">
                                                <i class="ti ti-eye fs-6 text-info"></i>
                                            </a>
                                            <a href="{{ route('reimbursement.print', Crypt::encrypt($d->id)) }}" target="_blank" class="btn btn-sm px-2 py-1 border-0 rounded-0 border-start"
                                                title="Cetak Ajuan" style="background: #f8f9fa;">
                                                <i class="ti ti-printer fs-6 text-secondary"></i>
                                            </a>
                                            
                                            @if($d->status == 'P')
                                                @can('reimbursement.delete')
                                                    <form method="POST" action="{{ route('reimbursement.delete', Crypt::encrypt($d->id)) }}" class="deleteform m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm delete-confirm px-2 py-1 border-0 rounded-0 border-start"
                                                            title="Hapus" style="background: #f8f9fa;">
                                                            <i class="ti ti-trash fs-6 text-danger"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if($reimbursement->isEmpty())
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="ti ti-receipt-off fs-1 text-muted" style="font-size: 5rem !important;"></i>
                                        </div>
                                        <p class="text-muted">Belum ada histori pengajuan reimbursement.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $reimbursement->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<x-modal-form id="modal" show="loadmodal" size="modal-lg" />

@endsection

@push('myscript')
<script>
    $(function() {
        function loading() {
            $("#loadmodal").html(`<div class="sk-wave sk-primary" style="margin:auto">
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
                <div class="sk-wave-rect"></div>
            </div>`);
        };

        $(".btnShow").click(function(e) {
            e.preventDefault();
            loading();
            const id = $(this).attr("id_reimbursement");
            $("#modal").modal("show");
            $(".modal-title").text("Detail Pengajuan Reimbursement");
            $("#loadmodal").load(`/reimbursement/${id}/show`);
        });
    });
</script>
@endpush
