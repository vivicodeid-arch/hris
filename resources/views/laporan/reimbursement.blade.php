@extends('layouts.app')
@section('titlepage', 'Laporan Reimbursement')
@section('content')
@section('navigasi')
    <span>Laporan Reimbursement</span>
@endsection
<div class="row">
    <div class="col-lg-6 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2" style="background-color: var(--theme-color-1) !important; color: white !important; min-height: 50px;">
                <div class="d-flex align-items-center">
                    <i class="ti ti-printer me-2 fs-5"></i>
                    <h6 class="card-title mb-0 text-white">Cetak Laporan Reimbursement</h6>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('laporan.cetakreimbursement') }}" method="POST" target="_blank" id="formReimbursement" class="mt-2">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="kode_cabang" id="kode_cabang_reimburse" class="form-select select2">
                            <option value="">Semua Cabang</option>
                            @foreach ($cabang as $d)
                                <option value="{{ $d->kode_cabang }}">{{ textUpperCase($d->nama_cabang) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <select name="kode_dept" id="kode_dept_reimburse" class="form-select select2">
                            <option value="">Semua Departemen</option>
                            @foreach ($departemen as $d)
                                <option value="{{ $d->kode_dept }}">{{ textUpperCase($d->nama_dept) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <select name="nik" id="nik_reimburse" class="form-select select2">
                            <option value="">Semua Karyawan</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <select name="status" id="status_reimburse" class="form-select select2">
                            <option value="">Semua Status</option>
                            <option value="P">Pending</option>
                            <option value="A">Approved</option>
                            <option value="R">Rejected</option>
                            <option value="D">Dibayar</option>
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
            dropdownParent: $('#formReimbursement')
        });

        function loadKaryawan() {
            const kode_cabang = $("#kode_cabang_reimburse").val();
            const kode_dept = $("#kode_dept_reimburse").val();
            
            $.ajax({
                type: "GET",
                url: "{{ route('karyawan.getkaryawan') }}",
                data: {
                    kode_cabang: kode_cabang,
                    kode_dept: kode_dept
                },
                cache: false,
                success: function(respond) {
                    $("#nik_reimburse").empty();
                    $("#nik_reimburse").append("<option value=''>Semua Karyawan</option>");
                    respond.forEach(function(item) {
                        $("#nik_reimburse").append("<option value='" + item.nik + "'>" + item.nik + " - " + item
                            .nama_karyawan +
                            "</option>");
                    });
                }
            });
        }

        $("#kode_cabang_reimburse, #kode_dept_reimburse").change(function() {
            loadKaryawan();
        });

        loadKaryawan();

        $("#formReimbursement").submit(function(e) {
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
        });
    });
</script>
@endpush
