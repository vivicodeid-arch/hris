@extends('layouts.app')
@section('titlepage', 'Tarif Progresif PPh 21')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Tarif Progresif PPh 21
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Tarif Pajak Pasal 17 ayat (1) huruf a UU PPh (UU HPP)
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}"><i class="ti ti-home-2 ti-xs"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('pph21.index') }}">PPh 21</a></li>
                <li class="breadcrumb-item active">Progresif</li>
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
            <a href="{{ route('pph21.ter') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-table me-1"></i> Tabel TER
            </a>
            <a href="{{ route('pph21.progresif') }}" class="btn btn-primary btn-sm">
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
        <div class="alert alert-primary d-flex align-items-start border-0 shadow-xs p-2 mb-3 text-white" 
            style="background-color: var(--theme-color-1) !important; border-left: 3px solid #0d6efd !important; opacity: 0.9; font-size: 0.7rem;">
            <i class="ti ti-info-circle me-2 fs-5 text-white"></i>
            <div>
                <strong>Info:</strong> Tarif ini digunakan untuk perhitungan PPh 21 di bulan Desember atau perhitungan tahunan.
            </div>
        </div>

        {{-- Card Utama --}}
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="card-header py-2 px-3 border-bottom" style="background-color: var(--theme-color-1) !important;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-chart-bar me-2 fs-5 text-white"></i>
                    <h6 class="card-title mb-0 fw-bold text-white" style="font-size: 0.85rem;">Lapisan Penghasilan Kena Pajak (PKP)</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead style="background-color: var(--theme-color-1) !important;">
                            <tr>
                                <th class="py-2 px-3 text-center text-white" style="width: 40px;">#</th>
                                <th class="py-2 text-end text-white fw-bold">PKP Dari (Rp)</th>
                                <th class="py-2 text-end text-white fw-bold">PKP Sampai (Rp)</th>
                                <th class="py-2 text-center text-white fw-bold" style="width: 100px;">Tarif (%)</th>
                                <th class="py-2 text-center text-white fw-bold" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.8rem;">
                            @foreach($rates as $index => $r)
                            <tr>
                                <td class="text-center text-muted px-3 py-2">{{ $index + 1 }}</td>
                                <td class="text-end fw-bold py-2">Rp {{ number_format($r->pkp_dari, 0, ',', '.') }}</td>
                                <td class="text-end py-2">
                                    @if($r->pkp_sampai)
                                        Rp {{ number_format($r->pkp_sampai, 0, ',', '.') }}
                                    @else
                                        <span class="text-muted">Tak Terbatas</span>
                                    @endif
                                </td>
                                <td class="text-center py-2">
                                    <span class="badge bg-label-primary fw-bold" style="font-size: 0.85rem;">{{ number_format($r->tarif_persen, 0) }}%</span>
                                </td>
                                <td class="text-center py-2">
                                    <button class="btn btn-xs btn-icon border btnEdit" 
                                        data-id="{{ $r->id }}" 
                                        data-tarif="{{ $r->tarif_persen }}"
                                        data-dari="{{ $r->pkp_dari }}"
                                        data-sampai="{{ $r->pkp_sampai }}"
                                        style="background: #fff; width: 28px; height: 28px;">
                                        <i class="ti ti-edit text-primary fs-6"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                <h6 class="modal-title">Edit Tarif Progresif</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUpdateProgresif" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">PKP Dari</label>
                        <input type="number" name="pkp_dari" id="edit_dari" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">PKP Sampai (Kosongkan jika tak terbatas)</label>
                        <input type="number" name="pkp_sampai" id="edit_sampai" class="form-control">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Tarif (%)</label>
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
        let dari = $(this).data('dari');
        let sampai = $(this).data('sampai');
        
        $('#edit_tarif').val(tarif);
        $('#edit_dari').val(dari);
        $('#edit_sampai').val(sampai);
        $('#formUpdateProgresif').attr('action', `/pph21/progresif/${id}`);
        $('#modalEdit').modal('show');
    });
});
</script>
@endpush
