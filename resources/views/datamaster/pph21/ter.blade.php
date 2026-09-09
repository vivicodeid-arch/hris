@extends('layouts.app')
@section('titlepage', 'Tabel TER PPh 21')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Tabel TER PPh 21
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Tarif Efektif Rata-rata (TER) sesuai PP No. 58 Tahun 2023
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}"><i class="ti ti-home-2 ti-xs"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('pph21.index') }}">PPh 21</a></li>
                <li class="breadcrumb-item active">Tabel TER</li>
            </ol>
        </nav>
    </div>
@endsection

{{-- Navigasi Sub-menu --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('pph21.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-settings me-1"></i> Pengaturan
            </a>
            <a href="{{ route('pph21.formula') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-function me-1"></i> Formula Komponen
            </a>
            <a href="{{ route('pph21.ter') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-table me-1"></i> Tabel TER
            </a>
            <a href="{{ route('pph21.progresif') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-chart-bar me-1"></i> Tarif Progresif
            </a>
            <a href="{{ route('pph21.simulasi') }}" class="btn btn-outline-success btn-sm">
                <i class="ti ti-calculator me-1"></i> Simulasi Kalkulator
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 col-md-12">
        {{-- Info Alert --}}
        <div class="alert alert-info d-flex align-items-start border-0 shadow-xs p-2 mb-3 text-white" 
            style="background-color: var(--theme-color-1) !important; border-left: 3px solid #00cfe8 !important; opacity: 0.9; font-size: 0.7rem;">
            <i class="ti ti-info-circle me-2 fs-5 text-white"></i>
            <div>
                <strong>Info:</strong> Kategori TER ditentukan oleh status PTKP karyawan (A, B, atau C).
            </div>
        </div>

        {{-- Card Utama --}}
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="card-header p-0 border-bottom" style="background-color: #f8f9fa !important;">
                <ul class="nav nav-tabs nav-tabs-line border-0 px-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-3 px-4 fw-bold" data-bs-toggle="tab" data-bs-target="#ter-A" type="button" style="font-size: 0.8rem;">
                            Kategori A <span class="badge bg-primary ms-2" style="font-size: 0.65rem;">{{ $terA->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 px-4 fw-bold" data-bs-toggle="tab" data-bs-target="#ter-B" type="button" style="font-size: 0.8rem;">
                            Kategori B <span class="badge bg-warning ms-2" style="font-size: 0.65rem;">{{ $terB->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-3 px-4 fw-bold" data-bs-toggle="tab" data-bs-target="#ter-C" type="button" style="font-size: 0.8rem;">
                            Kategori C <span class="badge bg-danger ms-2" style="font-size: 0.65rem;">{{ $terC->count() }}</span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content p-0">
                    @php $categories = ['A' => $terA, 'B' => $terB, 'C' => $terC]; @endphp
                    @foreach($categories as $cat => $data)
                    <div class="tab-pane fade {{ $cat === 'A' ? 'show active' : '' }}" id="ter-{{ $cat }}" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead style="background-color: var(--theme-color-1) !important;">
                                    <tr>
                                        <th class="py-2 px-3 text-center text-white" style="width: 40px;">#</th>
                                        <th class="py-2 text-end text-white fw-bold">Dari (Rp)</th>
                                        <th class="py-2 text-end text-white fw-bold">Sampai (Rp)</th>
                                        <th class="py-2 text-center text-white fw-bold" style="width: 90px;">Tarif (%)</th>
                                        <th class="py-2 text-center text-white fw-bold" style="width: 60px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size: 0.8rem;">
                                    @foreach($data as $index => $r)
                                    <tr>
                                        <td class="text-center text-muted px-3">{{ $index + 1 }}</td>
                                        <td class="text-end fw-bold">Rp {{ number_format($r->penghasilan_dari, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            @if($r->penghasilan_sampai)
                                                Rp {{ number_format($r->penghasilan_sampai, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">Tak Terbatas</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-label-danger fw-bold" style="font-size: 0.8rem;">{{ number_format($r->tarif_persen, 2) }}%</span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-xs btn-icon border btnEdit" 
                                                data-id="{{ $r->id }}" 
                                                data-tarif="{{ $r->tarif_persen }}"
                                                style="background: #fff; width: 26px; height: 26px;">
                                                <i class="ti ti-edit text-primary fs-6"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2 px-3 border-bottom">
                <h6 class="modal-title">Edit Tarif TER (%)</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUpdateTer" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-3">
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Tarif Persen (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="tarif_persen" id="edit_tarif" class="form-control" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2 border-top">
                    <button type="button" class="btn btn-xs btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-xs btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('myscript')
<script>
$(function() {
    $('.btnEdit').on('click', function() {
        let id = $(this).data('id');
        let tarif = $(this).data('tarif');
        $('#edit_tarif').val(tarif);
        $('#formUpdateTer').attr('action', `/pph21/ter/${id}`);
        $('#modalEdit').modal('show');
    });
});
</script>
@endpush