@extends('layouts.app')
@section('titlepage', 'Pengaturan PPh 21')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Pengaturan PPh 21
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Konfigurasi perhitungan Pajak Penghasilan Pasal 21 (PMK 168/2023 & PP 58/2023)
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard.index') }}"><i class="ti ti-home-2 ti-xs"></i></a>
                </li>
                <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-database ti-xs me-1"></i> Data Master</a></li>
                <li class="breadcrumb-item active"><i class="ti ti-receipt-tax ti-xs me-1"></i> PPh 21</li>
            </ol>
        </nav>
    </div>
@endsection

{{-- Navigasi Sub-menu --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('pph21.index') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-settings me-1"></i> Pengaturan
            </a>
            <a href="{{ route('pph21.formula') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-function me-1"></i> Formula Komponen
            </a>
            <a href="{{ route('pph21.ter') }}" class="btn btn-outline-primary btn-sm">
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
    {{-- Kartu Setting Utama --}}
    <div class="col-lg-8 col-md-12 mb-3">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3"
                style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-receipt-tax me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Konfigurasi PPh 21</h6>
                </div>
                {{-- Status Badge --}}
                <span class="badge {{ $setting->status_aktif ? 'bg-success' : 'bg-danger' }} px-2 py-1 shadow-sm" style="font-size: 0.7rem;">
                    {{ $setting->status_aktif ? '✓ AKTIF' : '✕ NONAKTIF' }}
                </span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('pph21.setting.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Toggle Aktif --}}
                    <div class="mb-3 p-3 rounded-3 border-start border-3" style="background: #fcfcfc; border-color: var(--theme-color-1) !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1 fw-bold" style="color: #444;">Aktifkan Fitur PPh 21</h6>
                                <p class="text-muted mb-0" style="font-size: 0.75rem;">
                                    PPh 21 otomatis dihitung di laporan & slip gaji karyawan.
                                </p>
                            </div>
                            <div class="form-check form-switch ms-3">
                                <input class="form-check-input" type="checkbox" name="status_aktif" id="status_aktif"
                                    {{ $setting->status_aktif ? 'checked' : '' }} style="width: 2.8rem; height: 1.4rem; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Metode Perhitungan --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">Metode Perhitungan</label>
                            <select name="metode" class="form-select" id="selectMetode">
                                <option value="TER" {{ $setting->metode === 'TER' ? 'selected' : '' }}>
                                    TER (Tarif Efektif Rata-rata) — PP 58/2023
                                </option>
                                <option value="PROGRESIF" {{ $setting->metode === 'PROGRESIF' ? 'selected' : '' }}>
                                    Progresif Manual — Pasal 17 UU HPP
                                </option>
                            </select>
                            <div class="form-text text-muted mt-1" style="font-size: 0.7rem; line-height: 1.2;">
                                <strong>TER:</strong> Jan–Nov pakai tarif flat, Desember pakai progresif.<br>
                                <strong>Progresif:</strong> Setiap bulan anualisasi tarif Pasal 17.
                            </div>
                        </div>

                        {{-- Metode Tanggungan --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">Siapa yang Menanggung PPh?</label>
                            <select name="metode_tanggungan" class="form-select">
                                <option value="GROSS" {{ $setting->metode_tanggungan === 'GROSS' ? 'selected' : '' }}>
                                    GROSS — PPh ditanggung karyawan (dipotong dari gaji)
                                </option>
                                <option value="GROSS_UP" {{ $setting->metode_tanggungan === 'GROSS_UP' ? 'selected' : '' }}>
                                    GROSS UP — PPh ditanggung perusahaan (tunjangan pajak)
                                </option>
                            </select>
                            <div class="form-text text-muted mt-1" style="font-size: 0.7rem; line-height: 1.2;">
                                <strong>GROSS:</strong> PPh langsung dipotong dari gaji karyawan.<br>
                                <strong>GROSS UP:</strong> Take-home pay tetap karena ada tunjangan pajak.
                            </div>
                        </div>

                        {{-- Biaya Jabatan --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">Biaya Jabatan (%)</label>
                            <div class="input-group">
                                <input type="number" name="biaya_jabatan_persen" class="form-control"
                                    value="{{ $setting->biaya_jabatan_persen }}" step="0.01" min="0" max="100">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text mt-1" style="font-size: 0.7rem;">Standar: 5% dari bruto</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">Maks. Biaya Jabatan per Bulan (Rp)</label>
                            <input type="text" name="biaya_jabatan_max_bulan" class="form-control formatAngka"
                                value="{{ number_format($setting->biaya_jabatan_max_bulan, 0, ',', '.') }}">
                            <div class="form-text mt-1" style="font-size: 0.7rem;">Standar: Rp 500.000 per bulan</div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Kartu Info PTKP & Kategori TER --}}
    <div class="col-lg-4 col-md-12 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-header py-2 px-3"
                style="background-color: var(--theme-color-1) !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-users me-2 fs-5 text-white"></i>
                    <h6 class="card-title mb-0 text-white">Status Kawin & Kategori TER</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th class="py-2 px-3">Kode</th>
                                <th class="py-2">Nilai PTKP</th>
                                <th class="py-2 text-center">TER</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.8rem;">
                            @foreach($statuskawin as $sk)
                            <tr>
                                <td class="px-3 py-1 fw-bold">{{ $sk->kode_status_kawin }}</td>
                                <td class="py-1">
                                    Rp {{ number_format($sk->nilai_ptkp ?? 54000000, 0, ',', '.') }}
                                </td>
                                <td class="py-1 text-center">
                                    @if($sk->kategori_ter === 'A')
                                        <span class="badge bg-label-primary px-2">A</span>
                                    @elseif($sk->kategori_ter === 'B')
                                        <span class="badge bg-label-warning px-2">B</span>
                                    @elseif($sk->kategori_ter === 'C')
                                        <span class="badge bg-label-danger px-2">C</span>
                                    @else
                                        <span class="badge bg-label-secondary px-2">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-2 border-top" style="font-size: 0.7rem; color: #6c757d;">
                    <i class="ti ti-info-circle me-1"></i> TER ditentukan otomatis dari status kawin.
                </div>
            </div>
        </div>
    </div>

    {{-- Panduan Regulasi --}}
    <div class="col-12 mt-1">
        <div class="card shadow-sm border-0">
            <div class="card-header py-2 px-3" style="background-color: #f0f7ff; border-left: 4px solid #0d6efd;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-book me-2 fs-5 text-primary"></i>
                    <h6 class="card-title mb-0 text-primary fw-bold" style="font-size: 0.85rem;">Ringkasan Aturan PPh 21 (PMK 168/2023)</h6>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded border h-100" style="background: #ffffff;">
                            <h6 class="text-primary fw-bold mb-2" style="font-size: 0.8rem;"><i class="ti ti-calendar me-1"></i>Januari – November</h6>
                            <p class="mb-2 text-muted" style="font-size: 0.75rem;">Metode <strong>TER</strong>:</p>
                            <div class="p-2 rounded text-center" style="background-color: #eef2ff; border: 1px dashed #adc4ff; color: #3b66f5; font-weight: 600; font-size: 0.75rem;">
                                Bruto × Tarif TER
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border h-100" style="background: #ffffff;">
                            <h6 class="text-danger fw-bold mb-2" style="font-size: 0.8rem;"><i class="ti ti-calendar-event me-1"></i>Desember</h6>
                            <p class="mb-2 text-muted" style="font-size: 0.75rem;">Metode <strong>Pasal 17</strong>:</p>
                            <div class="p-2 rounded text-center" style="background-color: #fff1f2; border: 1px dashed #fda4af; color: #e11d48; font-weight: 600; font-size: 0.75rem;">
                                PPh Setahun − Terbayar Jan-Nov
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border h-100" style="background: #ffffff;">
                            <h6 class="text-success fw-bold mb-2" style="font-size: 0.8rem;"><i class="ti ti-receipt me-1"></i>Tarif Pasal 17</h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 0.7rem; line-height: 1;">
                                <tr class="border-bottom"><td class="py-1">≤ 60 jt: 5%</td><td class="py-1">60-250 jt: 15%</td></tr>
                                <tr class="border-bottom"><td class="py-1">250-500 jt: 25%</td><td class="py-1">500jt-5M: 30%</td></tr>
                                <tr><td colspan="2" class="py-1">> 5 M: 35%</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('myscript')
<script>
$(function () {
    // Format angka otomatis pada input biaya jabatan max
    $('.formatAngka').on('input', function () {
        let val = $(this).val().replace(/\D/g, '');
        $(this).val(val.replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
    });

    $('.formatAngka').on('focus', function () {
        let val = $(this).val().replace(/\./g, '');
        $(this).val(val);
    });

    $('.formatAngka').on('blur', function () {
        let val = parseInt($(this).val().replace(/\D/g, '')) || 0;
        $(this).val(val.toLocaleString('id-ID'));
    });

    // Sebelum submit, convert angka kembali ke angka murni
    $('form').on('submit', function () {
        $('.formatAngka').each(function () {
            $(this).val($(this).val().replace(/\./g, ''));
        });
    });
});
</script>
@endpush
