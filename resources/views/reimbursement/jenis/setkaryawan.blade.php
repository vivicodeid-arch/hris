@extends('layouts.app')
@section('titlepage', 'Enrollment Karyawan')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Enrollment: {{ $jenis->nama_jenis }}
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Daftar karyawan yang berhak mengajukan reimbursement {{ $jenis->nama_jenis }}.
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard.index') }}"><i class="ti ti-home-2 ti-xs"></i></a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('jenisreimbursement.index') }}">Jenis Reimbursement</a>
                </li>
                <li class="breadcrumb-item active">Enrollment</li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center py-3" style="background: linear-gradient(to right, var(--theme-color-1), var(--theme-color-2)); color: white !important;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-users me-2 fs-4"></i>
                    <h6 class="card-title mb-0 text-white">Karyawan Terdaftar ({{ $jenis->kode_jenis_reimburse }})</h6>
                </div>
                <button class="btn btn-white btn-sm" id="btnAddKaryawan">
                    <i class="ti ti-user-plus me-1"></i> Daftarkan Karyawan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
                            <tr>
                                <th class="text-white py-3 px-4">NIK</th>
                                <th class="text-white py-3">NAMA KARYAWAN</th>
                                <th class="text-white py-3">TGL MULAI</th>
                                <th class="text-white py-3">TGL SELESAI</th>
                                <th class="text-white py-3 text-end">OVERRIDE PLAFON</th>
                                <th class="text-white py-3 text-center" style="width: 100px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($details as $d)
                                <tr>
                                    <td class="px-4 fw-bold">{{ $d->nik_show ?: $d->nik }}</td>
                                    <td>{{ $d->nama_karyawan }}</td>
                                    <td>{{ date('d-m-Y', strtotime($d->tanggal_mulai)) }}</td>
                                    <td>{{ $d->tanggal_selesai ? date('d-m-Y', strtotime($d->tanggal_selesai)) : '-' }}</td>
                                    <td class="text-end">
                                        @if($d->batas_nominal_override)
                                            <span class="text-danger fw-bold">{{ number_format($d->batas_nominal_override, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted italic">Global ({{ $jenis->batas_nominal ? number_format($jenis->batas_nominal, 0, ',', '.') : 'Unlimited' }})</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('jenisreimbursement.destroykaryawan', Crypt::encrypt($d->id)) }}" class="deleteform">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon delete-confirm" title="Hapus dari Enrollment">
                                                <i class="ti ti-trash text-danger"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($details->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="ti ti-users-off fs-1 text-muted mb-3" style="font-size: 4rem !important;"></i>
                                        <p class="text-muted">Belum ada karyawan yang terdaftar untuk jenis reimbursement ini.</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
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

        $("#btnAddKaryawan").click(function(e) {
            e.preventDefault();
            loading();
            $("#modal").modal("show");
            $(".modal-title").text("Daftarkan Karyawan ke {{ $jenis->nama_jenis }}");
            $("#loadmodal").load("{{ route('jenisreimbursement.addkaryawan', Crypt::encrypt($jenis->id)) }}");
        });
    });
</script>
@endpush
