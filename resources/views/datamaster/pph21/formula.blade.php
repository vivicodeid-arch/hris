@extends('layouts.app')
@section('titlepage', 'Formula Komponen PPh 21')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Formula PPh 21
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Tentukan komponen penghasilan yang masuk dalam perhitungan Bruto PPh 21
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}"><i class="ti ti-home-2 ti-xs"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('pph21.index') }}">PPh 21</a></li>
                <li class="breadcrumb-item active">Formula</li>
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
            <a href="{{ route('pph21.formula') }}" class="btn btn-primary btn-sm">
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
    {{-- Form Tambah Komponen --}}
    <div class="col-lg-4 col-md-12 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header py-2 px-3" style="background-color: var(--theme-color-1) !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-plus me-2 fs-5 text-white"></i>
                    <h6 class="card-title mb-0 text-white fw-bold">Tambah Komponen</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('pph21.formula.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">Nama Komponen</label>
                        <input type="text" name="nama_komponen" class="form-control" placeholder="contoh: Tunjangan Makan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">Tipe</label>
                        <select name="tipe" class="form-select">
                            <option value="penambah">➕ Penambah (masuk bruto)</option>
                            <option value="pengurang">➖ Pengurang (dikurangi dari bruto)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">Sumber Data</label>
                        <select name="sumber" class="form-select" id="selectSumber">
                            <option value="gaji_pokok">Gaji Pokok</option>
                            <option value="tunjangan">Tunjangan (pilih jenis)</option>
                            <option value="bpjs_kesehatan">BPJS Kesehatan</option>
                            <option value="bpjs_tenagakerja">BPJS Tenaga Kerja</option>
                            <option value="lembur">Lembur</option>
                        </select>
                    </div>
                    <div class="mb-3" id="wrapKodeSumber" style="display: none;">
                        <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">Jenis Tunjangan</label>
                        <select name="kode_sumber" class="form-select">
                            <option value="">-- Semua Tunjangan --</option>
                            @foreach($jenistunjangan as $jt)
                                <option value="{{ $jt->kode_jenis_tunjangan }}">{{ $jt->jenis_tunjangan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold shadow-sm">
                        <i class="ti ti-plus me-1"></i> Tambah Komponen
                    </button>
                </form>
            </div>
        </div>

        {{-- Info Alert --}}
        <div class="alert alert-info d-flex align-items-start border-0 shadow-xs p-2 mb-3 text-white" 
            style="background-color: var(--theme-color-1) !important; border-left: 3px solid #00cfe8 !important; opacity: 0.9; font-size: 0.7rem;">
            <i class="ti ti-info-circle me-2 fs-5 text-white"></i>
            <div>
                <strong>Info:</strong> Bruto PPh 21 = Σ Penambah − Σ Pengurang. <br>
                Hanya komponen <strong>aktif</strong> yang dihitung.
            </div>
        </div>
    </div>

    {{-- Daftar Komponen --}}
    <div class="col-lg-8 col-md-12 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header py-2 px-3" style="background-color: var(--theme-color-1) !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-list me-2 fs-5 text-white"></i>
                    <h6 class="card-title mb-0 text-white fw-bold">Daftar Komponen</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: var(--theme-color-1) !important;">
                            <tr>
                                <th class="py-2 px-3 text-white" style="width: 40px;">#</th>
                                <th class="py-2 text-white">Nama Komponen</th>
                                <th class="py-2 text-center text-white">Tipe</th>
                                <th class="py-2 text-white">Sumber</th>
                                <th class="py-2 text-center text-white">Status</th>
                                <th class="py-2 text-center text-white" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.8rem;">
                            @forelse($komponens as $k)
                            <tr class="{{ !$k->status_aktif ? 'table-light text-muted' : '' }}">
                                <td class="py-1 px-3 text-muted">{{ $k->urutan }}</td>
                                <td class="py-1">
                                    <div class="fw-bold text-dark">{{ $k->nama_komponen }}</div>
                                </td>
                                <td class="py-1 text-center">
                                    <span class="badge {{ $k->tipe === 'penambah' ? 'bg-label-success' : 'bg-label-danger' }} px-2">
                                        {{ $k->tipe === 'penambah' ? 'Penambah' : 'Pengurang' }}
                                    </span>
                                </td>
                                <td class="py-1">
                                    <div class="d-flex align-items-center" style="font-size: 0.75rem;">
                                        @if($k->sumber === 'gaji_pokok') Gaji Pokok
                                        @elseif($k->sumber === 'tunjangan') Tunjangan{{ $k->kode_sumber ? ' (' . $k->kode_sumber . ')' : ' (All)' }}
                                        @elseif($k->sumber === 'bpjs_kesehatan') BPJS Kesehatan
                                        @elseif($k->sumber === 'bpjs_tenagakerja') BPJS TK
                                        @elseif($k->sumber === 'lembur') Lembur
                                        @endif
                                    </div>
                                </td>
                                <td class="py-1 text-center">
                                    <span class="badge {{ $k->status_aktif ? 'bg-success' : 'bg-secondary' }} rounded-pill" style="font-size: 0.65rem;">
                                        {{ $k->status_aktif ? 'Aktif' : 'Off' }}
                                    </span>
                                </td>
                                <td class="py-1 text-center">
                                    <div class="d-inline-flex gap-1">
                                        <form method="POST" action="{{ route('pph21.formula.toggle', $k->id) }}" class="m-0">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-xs btn-icon border" 
                                                title="Toggle Status" style="background: #fff; width: 26px; height: 26px;">
                                                <i class="ti {{ $k->status_aktif ? 'ti-toggle-right text-success' : 'ti-toggle-left text-secondary' }} fs-6"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('pph21.formula.destroy', $k->id) }}" class="deleteform m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-icon border delete-confirm"
                                                title="Hapus" style="background: #fff; width: 26px; height: 26px;">
                                                <i class="ti ti-trash text-danger fs-6"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('myscript')
<script>
$(function () {
    function toggleSumber() {
        if ($('#selectSumber').val() === 'tunjangan') {
            $('#wrapKodeSumber').show();
        } else {
            $('#wrapKodeSumber').hide();
        }
    }
    
    $('#selectSumber').on('change', toggleSumber);
    toggleSumber();

    $('.delete-confirm').click(function(e) {
        let form = $(this).closest('form');
        e.preventDefault();
        Swal.fire({
            title: 'Hapus Komponen?',
            text: "Komponen ini tidak akan dihitung lagi dalam PPh 21.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
