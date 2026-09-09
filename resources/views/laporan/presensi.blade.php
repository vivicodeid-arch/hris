@extends('layouts.app')
@section('titlepage', 'Laporan Presensi')

@section('content')
@section('navigasi')
    <span>Laporan Presensi</span>
@endsection
<div class="row">
    <div class="col-lg-6 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-printer me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Laporan Presensi Karyawan</h6>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('laporan.cetakpresensi') }}" method="POST" target="_blank" id="formPresensi" class="mt-2">
                    @csrf
                    <input type="hidden" name="format_laporan" value="1">
                    <div class="form-group mb-3">
                        <select name="kode_cabang" id="kode_cabang" class="form-select select2">
                            <option value="">Semua Cabang</option>
                            @foreach ($cabang as $d)
                                <option value="{{ $d->kode_cabang }}">{{ textUpperCase($d->nama_cabang) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <select name="kode_dept" id="kode_dept" class="form-select select2">
                            <option value="">Semua Departemen</option>
                            @foreach ($departemen as $d)
                                <option value="{{ $d->kode_dept }}">{{ textUpperCase($d->nama_dept) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <select name="nik" id="nik" class="form-select select2">
                            <option value="">Semua Karyawan</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <select name="periode_laporan" id="periode_laporan" class="form-select">
                            <option value="">Periode Laporan</option>
                            <option value="1" selected>Periode Gaji</option>
                            <option value="2">Bulan Berjalan</option>
                            <option value="3">Range Tanggal</option>
                        </select>
                    </div>

                    <div class="row" id="baris_tanggal">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <input type="text" name="dari" id="dari" class="form-control flatpickr-date" placeholder="Dari">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <input type="text" name="sampai" id="sampai" class="form-control flatpickr-date" placeholder="Sampai">
                            </div>
                        </div>
                    </div>

                    <div class="row" id="baris_bulan">
                        <div class="col">
                            <div class="form-group mb-3">
                                <select name="bulan" id="bulan" class="form-select">
                                    <option value="">Bulan</option>
                                    @foreach ($list_bulan as $d)
                                        <option {{ date('m') == $d['kode_bulan'] ? 'selected' : '' }} value="{{ $d['kode_bulan'] }}">
                                            {{ $d['nama_bulan'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="baris_tahun">
                        <div class="col">
                            <div class="form-group mb-3">
                                <select name="tahun" id="tahun" class="form-select">
                                    <option value="">Tahun</option>
                                    @for ($t = $start_year; $t <= date('Y'); $t++)
                                        <option {{ date('Y') == $t ? 'selected' : '' }} value="{{ $t }}">{{ $t }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <select name="format_rekap" id="format_rekap" class="form-select">
                            <option value="1">Format 1 (Default)</option>
                            <option value="2">Format 2 (Struktur Baru)</option>
                        </select>
                    </div>

                    <div class="row mt-2">
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                            <button type="submit" name="submitButton" class="btn btn-primary w-100">
                                <i class="ti ti-printer me-1"></i> Cetak
                            </button>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                            <button type="submit" name="exportButton" class="btn btn-success w-100">
                                <i class="ti ti-download me-1"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
    $(function() {
        // Initialize Select2
        const initSelect2 = (selector) => {
            $(selector).each(function() {
                var $this = $(this);
                var placeholder = $this.find("option:first").text();
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: placeholder,
                    allowClear: true,
                    dropdownParent: $this.parent()
                });
            });
        };

        initSelect2(".select2");

        // Load Karyawan Function
        function loadKaryawan() {
            const kode_cabang = $("#kode_cabang").val();
            const kode_dept = $("#kode_dept").val();
            const dari = $("#dari").val();
            const targetSelect = $("#nik");

            $.ajax({
                type: "GET",
                url: "{{ route('karyawan.getkaryawan') }}",
                data: {
                    kode_cabang: kode_cabang,
                    kode_dept: kode_dept,
                    tanggal: dari // Optional use
                },
                cache: false,
                success: function(respond) {
                    targetSelect.empty();
                    targetSelect.append(`<option value=''>Semua Karyawan</option>`);
                    respond.forEach(function(item) {
                        targetSelect.append(`<option value="${item.nik}">${item.nik} - ${item.nama_karyawan}</option>`);
                    });
                }
            });
        }

        $("#kode_cabang, #kode_dept, #dari").change(function() {
            loadKaryawan();
        });

        loadKaryawan();

        // Toggle logic for Periode
        function togglePeriode() {
            const periode = $("#periode_laporan").val();
            if (periode == "3") {
                $("#baris_tanggal").show();
                $("#baris_bulan, #baris_tahun").hide();
            } else {
                $("#baris_tanggal").hide();
                $("#baris_bulan, #baris_tahun").show();
            }
        }

        $("#periode_laporan").change(togglePeriode);
        togglePeriode();

        $("#formPresensi").submit(function(e) {
            const periode = $("#periode_laporan").val();
            if (periode === "") {
                Swal.fire('Warning', 'Periode Laporan harus diisi!', 'warning');
                e.preventDefault();
                return false;
            }
            if (periode === "3") {
                if ($("#dari").val() === "" || $("#sampai").val() === "") {
                    Swal.fire('Warning', 'Range Tanggal harus diisi!', 'warning');
                    e.preventDefault();
                    return false;
                }
            } else {
                if ($("#bulan").val() === "" || $("#tahun").val() === "") {
                    Swal.fire('Warning', 'Bulan/Tahun harus diisi!', 'warning');
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>
@endpush
