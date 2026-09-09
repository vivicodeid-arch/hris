@extends('layouts.app')
@section('titlepage', 'Konfigurasi Template Kontrak')
@section('content')
@section('navigasi')
    <span>Konfigurasi Template Kontrak</span>
@endsection
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-pills card-header-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $type == 'PKWT' ? 'active' : '' }}" href="{{ route('kontrak.template', ['type' => 'PKWT']) }}">
                            <i class="ti ti-file-text me-1"></i> Template PKWT
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $type == 'PKWTT' ? 'active' : '' }}" href="{{ route('kontrak.template', ['type' => 'PKWTT']) }}">
                            <i class="ti ti-file-certificate me-1"></i> Template PKWTT
                        </a>
                    </li>
                </ul>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <h4>Edit Template {{ $type }}</h4>
                    <form action="{{ route('kontrak.updateTemplate') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset template ini ke default? Perubahan Anda akan hilang.');">
                        @csrf
                        <input type="hidden" name="reset" value="true">
                        <input type="hidden" name="kode_dokumen" value="{{ $type }}">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="ti ti-refresh me-1"></i> Reset ke Default
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('kontrak.updateTemplate') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kode_dokumen" value="{{ $type }}">
                    <div class="form-group mb-3">
                        <label for="konten" class="form-label">Isi Kontrak ({{ $type }})</label>
                        <textarea class="form-control" name="konten" id="konten">{{ $template->konten }}</textarea>
                    </div>
                    <div class="alert alert-info">
                        <strong>Kamus Kode (Placeholder):</strong><br>
                        Gunakan kode berikut agar data otomatis terisi saat dicetak:
                        <ul>
                            <li>@{{no_kontrak}} - Nomor Kontrak</li>
                            <li>@{{no_dokumen}} - Nomor Dokumen</li>
                            <li>@{{nama_karyawan}} - Nama Karyawan</li>
                            <li>@{{tempat_lahir}} - Tempat Lahir</li>
                            <li>@{{tanggal_lahir}} - Tanggal Lahir</li>
                            <li>@{{jenis_kelamin}} - Jenis Kelamin</li>
                            <li>@{{alamat_karyawan}} - Alamat Karyawan</li>
                            <li>@{{no_ktp}} - No KTP</li>
                            <li>@{{no_hp}} - No HP / Telepon</li>
                            <li>@{{pendidikan_terakhir}} - Pendidikan Terakhir</li>
                            <li>@{{jabatan}} - Jabatan Karyawan</li>
                            <li>@{{cabang}} - Cabang</li>
                            <li>@{{nama_perusahaan}} - Nama Perusahaan</li>
                            <li>@{{alamat_perusahaan}} - Alamat Perusahaan</li>
                            <li>@{{nama_hrd}} - Nama HRD / Owner</li>
                            <li>@{{jabatan_hrd}} - Jabatan HRD / Owner</li>
                            <li>@{{gaji_pokok}} - Gaji Pokok (Format Rupiah)</li>
                            <li>@{{total_gaji}} - Total Gaji (Gaji Pokok + Tunjangan)</li>
                            <li>@{{tabel_tunjangan}} - Tabel List Tunjangan</li>
                            <li>@{{tanggal_mulai}} - Tanggal Mulai Kontrak</li>
                            <li>@{{tanggal_selesai}} - Tanggal Selesai Kontrak</li>
                            <li>@{{hari_ini}} - Hari Cetak</li>
                            <li>@{{tanggal_hari_ini}} - Tanggal Cetak</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Summernote Assets --}}
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
@push('myscript')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    $(document).ready(function() {
        $('#konten').summernote({
            placeholder: 'Tulis isi kontrak disini...',
            tabsize: 2,
            height: 600,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
@endpush
@endsection
