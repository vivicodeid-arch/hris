@extends('layouts.app')
@section('titlepage', 'Laporan Pinjaman')
@section('content')
@section('navigasi')
    <span>Laporan Pinjaman</span>
@endsection
<div class="row">
    <div class="col-lg-6 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-printer me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Cetak Laporan Pinjaman Karyawan</h6>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('laporan.cetakpinjaman') }}" method="POST" target="_blank" id="formPinjaman" class="mt-2">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="jenis_laporan" id="jenis_laporan" class="form-select select2">
                            <option value="detail">Detail Pinjaman</option>
                            <option value="rekap">Rekap Bulanan</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <select name="kode_cabang" id="kode_cabang_pinjaman" class="form-select select2">
                            <option value="">Semua Cabang</option>
                            @foreach ($cabang as $d)
                                <option value="{{ $d->kode_cabang }}">{{ textUpperCase($d->nama_cabang) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <select name="kode_dept" id="kode_dept_pinjaman" class="form-select select2">
                            <option value="">Semua Departemen</option>
                            @foreach ($departemen as $d)
                                <option value="{{ $d->kode_dept }}">{{ textUpperCase($d->nama_dept) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <select name="nik" id="nik_pinjaman" class="form-select select2">
                            <option value="">Semua Karyawan</option>
                        </select>
                    </div>
                    <div class="form-group mb-3" id="group_status">
                        <select name="status" id="status_pinjaman" class="form-select select2">
                            <option value="">Semua Status</option>
                            <option value="A">Aktif</option>
                            <option value="L">Lunas</option>
                            <option value="B">Batal</option>
                        </select>
                    </div>
                    
                    <div class="row" id="baris_tanggal">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <input type="text" name="dari" id="dari" class="form-control flatpickr-date"
                                    placeholder="Dari" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <input type="text" name="sampai" id="sampai" class="form-control flatpickr-date"
                                    placeholder="Sampai" required>
                            </div>
                        </div>
                    </div>

                    <div class="row d-none" id="baris_bulan_tahun">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <select name="bulan" id="bulan" class="form-select">
                                    <option value="">Pilih Bulan</option>
                                @foreach ($list_bulan as $d)
                                    <option {{ date('m') == $d['kode_bulan'] ? 'selected' : '' }} value="{{ $d['kode_bulan'] }}">{{ $d['nama_bulan'] }}</option>
                                @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <select name="tahun" id="tahun" class="form-select">
                                    <option value="">Pilih Tahun</option>
                                    @for ($t = $start_year; $t <= date('Y'); $t++)
                                        <option {{ date('Y') == $t ? 'selected' : '' }} value="{{ $t }}">{{ $t }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                            <button type="submit" name="submitButton" class="btn btn-primary w-100" id="submitButton">
                                <i class="ti ti-printer me-1"></i> Cetak Laporan
                            </button>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-2">
                            <button type="submit" name="exportButton" class="btn btn-success w-100" id="exportButton">
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
        $(".select2").select2({
            width: '100%',
            dropdownParent: $('#formPinjaman')
        });

        function loadKaryawan() {
            const kode_cabang = $("#kode_cabang_pinjaman").val();
            const kode_dept = $("#kode_dept_pinjaman").val();
            
            $.ajax({
                type: "GET",
                url: "{{ route('karyawan.getkaryawan') }}",
                data: {
                    kode_cabang: kode_cabang,
                    kode_dept: kode_dept
                },
                cache: false,
                success: function(respond) {
                    $("#nik_pinjaman").empty();
                    $("#nik_pinjaman").append("<option value=''>Semua Karyawan</option>");
                    respond.forEach(function(item) {
                        $("#nik_pinjaman").append("<option value='" + item.nik + "'>" + item.nik + " - " + item
                            .nama_karyawan +
                            "</option>");
                    });
                }
            });
        }

        $("#kode_cabang_pinjaman, #kode_dept_pinjaman").change(function() {
            loadKaryawan();
        });

        loadKaryawan();

        function toggleJenisLaporan() {
            const jenis = $("#jenis_laporan").val();
            if (jenis === 'rekap') {
                $("#baris_tanggal").addClass('d-none');
                $("#dari").removeAttr('required');
                $("#sampai").removeAttr('required');
                
                $("#group_status").addClass('d-none');
                
                $("#baris_bulan_tahun").removeClass('d-none');
                $("#bulan").attr('required', 'required');
                $("#tahun").attr('required', 'required');
            } else {
                $("#baris_tanggal").removeClass('d-none');
                $("#dari").attr('required', 'required');
                $("#sampai").attr('required', 'required');
                
                $("#group_status").removeClass('d-none');
                
                $("#baris_bulan_tahun").addClass('d-none');
                $("#bulan").removeAttr('required');
                $("#tahun").removeAttr('required');
            }
        }

        $("#jenis_laporan").change(function() {
            toggleJenisLaporan();
        });
        
        toggleJenisLaporan();

        $("#formPinjaman").submit(function(e) {
            const jenis = $("#jenis_laporan").val();
            if (jenis === 'detail') {
                const dari = $("#dari").val();
                const sampai = $("#sampai").val();
                
                if (dari == "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Tanggal Dari harus diisi!',
                        showConfirmButton: true,
                        didClose: () => {
                            $("#dari").focus();
                        }
                    });
                    return false;
                } else if (sampai == "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Tanggal Sampai harus diisi!',
                        showConfirmButton: true,
                        didClose: () => {
                            $("#sampai").focus();
                        }
                    });
                    return false;
                }
            } else {
                const bulan = $("#bulan").val();
                const tahun = $("#tahun").val();
                
                if (bulan == "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Bulan harus dipilih!',
                        showConfirmButton: true,
                        didClose: () => {
                            $("#bulan").focus();
                        }
                    });
                    return false;
                } else if (tahun == "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Tahun harus dipilih!',
                        showConfirmButton: true,
                        didClose: () => {
                            $("#tahun").focus();
                        }
                    });
                    return false;
                }
            }
        });
    });
</script>
@endpush
