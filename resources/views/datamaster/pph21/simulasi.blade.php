@extends('layouts.app')
@section('titlepage', 'Simulasi PPh 21')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Simulasi Kalkulator PPh 21
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Hitung estimasi PPh 21 secara interaktif berdasarkan konfigurasi aktif
            </div>
        </div>
    </div>
@endsection

{{-- Navigasi Sub-menu --}}
<div class="row mb-3">
    <div class="col-12 d-flex gap-2 flex-wrap">
        <a href="{{ route('pph21.index') }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-settings me-1"></i> Pengaturan</a>
        <a href="{{ route('pph21.formula') }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-function me-1"></i> Formula</a>
        <a href="{{ route('pph21.ter') }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-table me-1"></i> Tabel TER</a>
        <a href="{{ route('pph21.progresif') }}" class="btn btn-outline-primary btn-sm"><i class="ti ti-chart-bar me-1"></i> Progresif</a>
        <a href="{{ route('pph21.simulasi') }}" class="btn btn-primary btn-sm"><i class="ti ti-calculator me-1"></i> Simulasi</a>
    </div>
</div>

<div class="row">
    {{-- Form Input Simulasi --}}
    <div class="col-lg-5 col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header py-3 px-4 border-bottom" style="background-color: var(--theme-color-1) !important;">
                <h6 class="card-title mb-0 text-white"><i class="ti ti-calculator me-2"></i>Input Data Simulasi</h6>
            </div>
            <div class="card-body p-4">
                <form id="formSimulasi">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Bulan Perhitungan</label>
                            <select name="bulan" class="form-select" id="selectBulan">
                                @foreach(config('global.list_bulan') as $b)
                                <option value="{{ $b['kode_bulan'] }}" {{ $b['kode_bulan'] == date('n') ? 'selected' : '' }}>
                                    {{ $b['nama_bulan'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Status PTKP</label>
                            <select name="kode_status_kawin" class="form-select">
                                @foreach($statuskawin as $sk)
                                <option value="{{ $sk->kode_status_kawin }}">
                                    {{ $sk->kode_status_kawin }} ({{ $sk->kategori_ter ?? '-' }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2 mb-3" style="font-size: 0.8rem; color: var(--theme-color-1);">
                                <i class="ti ti-coins me-1"></i> Komponen Penghasilan
                            </h6>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="gaji_pokok" class="form-control input-rupiah fw-bold" placeholder="0" required>
                            </div>
                        </div>

                        @foreach($jenistunjangan as $jt)
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-truncate d-block" title="{{ $jt->jenis_tunjangan }}">
                                {{ $jt->jenis_tunjangan }}
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="tunjangan[{{ $jt->kode_jenis_tunjangan }}]" class="form-control input-rupiah" placeholder="0">
                            </div>
                        </div>
                        @endforeach

                        <div class="col-12 mt-3">
                            <h6 class="border-bottom pb-2 mb-3" style="font-size: 0.8rem; color: #dc3545;">
                                <i class="ti ti-cut me-1"></i> Potongan / BPJS
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">BPJS Kesehatan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="bpjs_kesehatan" class="form-control input-rupiah" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">BPJS TK (JHT/JP)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="bpjs_tenagakerja" class="form-control input-rupiah" placeholder="0">
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="btnHitung">
                                <i class="ti ti-calculator-check me-1 fs-5"></i> HITUNG ESTIMASI PAJAK
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Hasil Simulasi --}}
    <div class="col-lg-7 col-md-12 mb-4">
        <div class="card shadow-sm border-0 h-100" id="cardHasil" style="display: none !important;">
            <div class="card-header py-3 px-4 border-bottom" style="background-color: #198754 !important;">
                <h6 class="card-title mb-0 text-white"><i class="ti ti-receipt-tax me-2"></i>Hasil Simulasi Perhitungan</h6>
            </div>
            <div class="card-body p-0" id="hasilSimulasi">
                {{-- diisi oleh JS --}}
            </div>
        </div>

        <div class="card shadow-sm border-0" id="cardLoading" style="display: none !important;">
            <div class="card-body text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Sedang mengkalkulasi...</p>
            </div>
        </div>

        {{-- Placeholder --}}
        <div class="card shadow-sm border-0 h-100" id="cardPlaceholder">
            <div class="card-body d-flex flex-column align-items-center justify-content-center" style="min-height: 400px; background: #fafafa;">
                <div class="rounded-circle bg-light p-4 mb-3">
                    <i class="ti ti-calculator" style="font-size: 3.5rem; color: #cbd5e1;"></i>
                </div>
                <h5 class="text-dark fw-bold mb-1">Siap Menghitung</h5>
                <p class="text-muted text-center px-4" style="font-size: 0.85rem;">
                    Silakan isi data penghasilan di form sebelah kiri untuk melihat estimasi potongan PPh 21.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
$(function () {
    // Format rupiah pada input
    $('.input-rupiah').on('input', function () {
        let val = $(this).val().replace(/\D/g, '');
        $(this).val(val ? parseInt(val).toLocaleString('id-ID') : '');
    });

    function formatRp(num) {
        return 'Rp ' + parseInt(num || 0).toLocaleString('id-ID');
    }

    $('#formSimulasi').on('submit', function (e) {
        e.preventDefault();

        let formData = {};
        $(this).serializeArray().forEach(function (item) {
            let val = item.value.replace(/\./g, '').replace(/,/g, '.');
            formData[item.name] = isNaN(val) || val === '' ? item.value : val;
        });

        $('#cardPlaceholder').hide();
        $('#cardHasil').hide();
        $('#cardLoading').show();

        $.ajax({
            url: '{{ route("pph21.simulasi.hitung") }}',
            method: 'POST',
            data: Object.assign(formData, { _token: '{{ csrf_token() }}' }),
            success: function (res) {
                $('#cardLoading').hide();
                if (!res.success) {
                    alert('Gagal menghitung: ' + (res.message || 'Error tidak diketahui'));
                    $('#cardPlaceholder').show();
                    return;
                }

                const d = res.data;
                const isDesember = d.bulan == 12;

                let kompHTML = '';
                (d.detail_komponen || []).forEach(function (k) {
                    kompHTML += `
                    <tr>
                        <td class="ps-4 py-2 text-muted">${k.nama_komponen}</td>
                        <td class="pe-4 py-2 text-end font-monospace ${k.tipe==='penambah'?'text-success':'text-danger'}">
                            ${k.tipe==='penambah'?'+':'-'} ${formatRp(k.nilai)}
                        </td>
                    </tr>`;
                });

                let html = `
                <div class="bg-light p-4 border-bottom">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size:0.65rem; letter-spacing:1px;">Metode</div>
                            <div class="fw-bold text-dark h5 mb-0">${d.metode} ${isDesember ? '<span class="badge bg-danger ms-1" style="font-size:0.6rem;">DES</span>' : ''}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size:0.65rem; letter-spacing:1px;">Kategori TER</div>
                            <div class="fw-bold text-success h5 mb-0">Kategori ${d.kategori_ter}</div>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge badge-dot bg-primary me-2"></span> Rincian Penghasilan Bruto
                    </h6>
                    <table class="table table-sm mb-4">
                        <tbody class="border-top-0">${kompHTML}</tbody>
                        <tfoot class="border-top-2">
                            <tr class="table-light fw-bold">
                                <td class="ps-4 py-3">TOTAL PENGHASILAN BRUTO</td>
                                <td class="pe-4 py-3 text-end fs-6">${formatRp(d.bruto)}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <h6 class="fw-bold mb-3 d-flex align-items-center">
                        <span class="badge badge-dot bg-danger me-2"></span> Kalkulasi Pajak Terutang
                    </h6>
                    <div class="table-responsive rounded border mb-4">
                        <table class="table table-sm table-borderless mb-0">
                            <tr class="border-bottom">
                                <td class="ps-3 py-2 text-muted small">Penghasilan Bruto Bulan Ini</td>
                                <td class="pe-3 py-2 text-end fw-bold">${formatRp(d.bruto)}</td>
                            </tr>
                            ${!isDesember ? `
                            <tr class="border-bottom">
                                <td class="ps-3 py-2">Tarif TER (Kategori ${d.kategori_ter})</td>
                                <td class="pe-3 py-2 text-end text-success fw-bold">${d.tarif_ter_persen}%</td>
                            </tr>` : `
                            <tr class="border-bottom">
                                <td class="ps-3 py-2">Penghasilan Neto Setahun (Estimasi)</td>
                                <td class="pe-3 py-2 text-end fw-bold">${formatRp(d.pkp_setahun + d.ptkp)}</td>
                            </tr>
                            <tr class="border-bottom text-danger">
                                <td class="ps-3 py-2">PTKP (${d.kode_status_kawin})</td>
                                <td class="pe-3 py-2 text-end">- ${formatRp(d.ptkp)}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="ps-3 py-2">Penghasilan Kena Pajak (PKP)</td>
                                <td class="pe-3 py-2 text-end fw-bold text-primary">${formatRp(d.pkp_setahun)}</td>
                            </tr>`}
                            <tr class="bg-success bg-opacity-10 text-success fw-bold" style="font-size: 1.1rem;">
                                <td class="ps-3 py-3">ESTIMASI PPh 21 TERUTANG</td>
                                <td class="pe-3 py-3 text-end">${formatRp(d.pph21_terutang)}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="alert ${d.pph21_terutang > 0 ? 'alert-warning' : 'alert-success'} border-0 d-flex align-items-center mb-0 p-3 shadow-xs">
                        <i class="ti ${d.pph21_terutang > 0 ? 'ti-alert-circle' : 'ti-circle-check'} fs-4 me-2"></i>
                        <div style="font-size: 0.85rem;">
                            Estimasi potongan pajak bulan ini adalah <strong>${formatRp(d.pph21_terutang)}</strong>. 
                            Nilai ini dihitung berdasarkan status <strong>${d.kode_status_kawin}</strong> (${d.nama_status_kawin}).
                        </div>
                    </div>
                </div>`;

                $('#hasilSimulasi').html(html);
                $('#cardHasil').show();
            },
            error: function (xhr) {
                $('#cardLoading').hide();
                $('#cardPlaceholder').show();
                let msg = 'Terjadi kesalahan sistem';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });
});
</script>
@endpush
