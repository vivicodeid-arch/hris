@extends('layouts.app')
@section('titlepage', 'Buat Pengajuan Reimbursement')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Buat Pengajuan
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Input rincian biaya yang akan diklaim.
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}"><i class="ti ti-home-2 ti-xs"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('reimbursement.index') }}">Reimbursement</a></li>
                <li class="breadcrumb-item active">Buat Pengajuan</li>
            </ol>
        </nav>
    </div>
@endsection

<form action="{{ route('reimbursement.store') }}" method="POST" enctype="multipart/form-data" id="formReimbursement">
    @csrf
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header py-3" style="background: var(--theme-color-1); color: white;">
                    <h6 class="card-title mb-0 text-white">Data Pengajuan</h6>
                </div>
                <div class="card-body pt-4">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Tanggal Pengajuan <span class="text-danger">*</span></label>
                        <input type="text" name="tanggal_pengajuan" class="form-control flatpickr-date" value="{{ date('Y-m-d') }}" placeholder="YYYY-MM-DD">
                    </div>

                    @if($nik)
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Nama Karyawan</label>
                            <input type="text" class="form-control bg-light" value="{{ $karyawan_selected->nama_karyawan }}" readonly>
                            <input type="hidden" name="nik" value="{{ $nik }}">
                        </div>
                    @else
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Pilih Karyawan <span class="text-danger">*</span></label>
                            <select name="nik" id="nik" class="form-select select2">
                                <option value="">Pilih Karyawan</option>
                                @foreach($karyawan as $k)
                                    <option value="{{ $k->nik }}">{{ $k->nik_show }} - {{ $k->nama_karyawan }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Keterangan Global</label>
                        <textarea name="catatan_global" class="form-control" rows="3" placeholder="Contoh: Operasional Perjalanan Dinas Jakarta"></textarea>
                    </div>

                    <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 0.85rem;">
                        <i class="ti ti-info-circle me-1"></i> Pastikan semua nominal nota sudah benar sebelum disimpan.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center py-3" style="background: var(--theme-color-1); color: white;">
                    <h6 class="card-title mb-0 text-white">Rincian Item Nota</h6>
                    <button type="button" class="btn btn-white btn-sm" id="btnAddRow">
                        <i class="ti ti-plus me-1"></i> Tambah Baris
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="tableItems">
                            <thead style="background: var(--theme-color-1) !important;">
                                <tr>
                                    <th style="width: 150px;" class="text-white border-0 py-3 px-3">TGL NOTA</th>
                                    <th class="text-white border-0 py-3">JENIS & DESKRIPSI</th>
                                    <th style="width: 180px;" class="text-end text-white border-0 py-3 px-3">NOMINAL (RP)</th>
                                    <th style="width: 120px;" class="text-center text-white border-0 py-3">BUKTI</th>
                                    <th style="width: 50px;" class="border-0"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="item-row">
                                    <td class="p-2">
                                        <input type="text" name="tgl_item[]" class="form-control form-control-sm flatpickr-date" value="{{ date('Y-m-d') }}">
                                    </td>
                                    <td class="p-2">
                                        <select name="kode_jenis_reimburse[]" class="form-select form-select-sm mb-1 select-jenis" required>
                                            <option value="">Pilih Jenis</option>
                                            @foreach($jenis_reimburse as $j)
                                                <option value="{{ $j->kode_jenis_reimburse }}" data-limit="{{ $j->limit_nominal }}" data-wajib-bukti="{{ $j->wajib_bukti }}">{{ $j->nama_jenis }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="keterangan[]" class="form-control form-control-sm" placeholder="Keterangan item...">
                                    </td>
                                    <td class="p-2 text-end">
                                        <input type="text" name="nominal[]" class="form-control form-control-sm text-end money nominal-field" placeholder="0" required>
                                        <div class="limit-info text-muted small mt-1" style="font-size: 0.7rem;"></div>
                                    </td>
                                    <td class="p-2 text-center">
                                        <label class="btn btn-xs btn-outline-primary mb-0 w-100 p-1">
                                            <i class="ti ti-upload"></i> <small>File</small>
                                            <input type="file" name="file[]" class="d-none file-input" accept="image/*,.pdf">
                                        </label>
                                        <div class="file-name mt-1 small text-muted text-truncate" style="max-width: 100px;"></div>
                                    </td>
                                    <td class="p-2 text-center">
                                        <button type="button" class="btn btn-xs btn-danger btnRemoveRow d-none"><i class="ti ti-x"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-white" style="border-top: 2px solid #eee;">
                                <tr>
                                    <th colspan="2" class="text-end py-4 border-0 text-muted fw-normal" style="letter-spacing: 1px;">GRAND TOTAL</th>
                                    <th class="text-end py-4 border-0 text-success fs-4 fw-bold" id="grandTotalLabel" style="font-family: 'Public Sans', sans-serif;">Rp 0</th>
                                    <th colspan="2" class="border-0"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer py-3 text-end bg-white border-top">
                    <a href="{{ route('reimbursement.index') }}" class="btn btn-label-secondary me-2">Batal</a>
                    <button type="submit" class="btn text-white" id="btnSubmit" style="background: var(--theme-color-1) !important;">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Pengajuan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('myscript')
<script>
    $(function() {
        $(".select2").select2();

        function calculateTotal() {
            let grandTotal = 0;
            $(".nominal-field").each(function() {
                let val = $(this).val().replace(/\./g, '').replace(/,/g, '.');
                if (val && !isNaN(val)) {
                    grandTotal += parseFloat(val);
                }
            });
            $("#grandTotalLabel").text("Rp " + number_format(grandTotal, 0, ',', '.'));
        }

        // Show limit info when jenis changed
        $(document).on("change", ".select-jenis", function() {
            let limit = $(this).find(":selected").data("limit");
            let info = $(this).closest("tr").find(".limit-info");
            if (limit && limit > 0) {
                info.html(`Max: Rp ${number_format(limit, 0, ',', '.')}`);
            } else if (limit === null || limit === 0) {
                info.html(`Max: Unlimited`);
            } else {
                info.html("");
            }
        });

        $(document).on("keyup", ".nominal-field", function() {
            calculateTotal();
        });

        // Add Row
        $("#btnAddRow").click(function() {
            let newRow = $(".item-row:first").clone();
            newRow.find("input").val("");
            newRow.find("select").val("");
            newRow.find(".file-name").text("");
            newRow.find(".limit-info").html("");
            newRow.find(".btnRemoveRow").removeClass("d-none");
            
            // Re-initialize flatpickr and money mask for the new row
            newRow.find(".flatpickr-date").removeClass("flatpickr-input").flatpickr();
            newRow.find(".money").maskMoney({
                thousands: '.', decimal: ',', precision: 0, allowZero: true
            });

            $("#tableItems tbody").append(newRow);
            calculateTotal();
        });

        // Remove Row
        $(document).on("click", ".btnRemoveRow", function() {
            $(this).closest("tr").remove();
            calculateTotal();
        });

        // File name preview
        $(document).on("change", ".file-input", function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).parent().siblings(".file-name").text(fileName);
        });

        $("#formReimbursement").submit(function(e) {
            let hasError = false;
            let errorMessage = "";
            let focusTarget = null; // Element yang akan di-focus setelah alert ditutup

            // 1. Validasi Tanggal Pengajuan
            let tglInput = $("input[name='tanggal_pengajuan']");
            if (!tglInput.val()) {
                hasError = true;
                errorMessage = "Tanggal pengajuan wajib diisi.";
                focusTarget = tglInput;
            }

            // 2. Validasi Karyawan (Jika diakses oleh Admin)
            if (!hasError && $("#nik").length) {
                if (!$("#nik").val()) {
                    hasError = true;
                    errorMessage = "Silakan pilih karyawan terlebih dahulu.";
                    focusTarget = $("#nik");
                }
            }

            // 3. Validasi Tiap Baris Item
            if (!hasError) {
                if ($(".item-row").length === 0) {
                    hasError = true;
                    errorMessage = "Minimal harus ada 1 item nota yang diajukan.";
                }

                $(".item-row").each(function(index) {
                    if (hasError) return false; // Skip jika sudah ada error

                    let row = $(this);
                    let selectJenis = row.find(".select-jenis");
                    let nominalField = row.find(".nominal-field");
                    let fileInput = row.find(".file-input");
                    let option = selectJenis.find("option:selected");

                    // Cek jenis dipilih
                    if (!selectJenis.val()) {
                        hasError = true;
                        errorMessage = `Jenis reimbursement pada baris ke-${index + 1} belum dipilih.`;
                        focusTarget = selectJenis;
                        return false;
                    }

                    // Cek nominal
                    let nominalRaw = nominalField.val();
                    if (!nominalRaw || nominalRaw === "0") {
                        hasError = true;
                        errorMessage = `Nominal pada baris ke-${index + 1} belum diisi.`;
                        focusTarget = nominalField;
                        return false;
                    }

                    let nominal = parseFloat(nominalRaw.replace(/\./g, '').replace(/,/g, '.'));
                    let jenisName = option.text();
                    let limit = option.data("limit");
                    let wajibBukti = option.data("wajib-bukti");

                    // Cek wajib bukti
                    if (wajibBukti == 1 && (!fileInput[0].files || fileInput[0].files.length === 0)) {
                        hasError = true;
                        errorMessage = `Jenis "${jenisName}" (Baris ${index + 1}) wajib melampirkan bukti file.`;
                        focusTarget = fileInput;
                        return false;
                    }

                    // Cek plafon
                    if (limit !== undefined && limit !== "" && limit !== null && parseFloat(limit) > 0) {
                        if (nominal > parseFloat(limit)) {
                            hasError = true;
                            errorMessage = `Nominal klaim "${jenisName}" (Baris ${index + 1}) sebesar Rp ${number_format(nominal, 0, ',', '.')} melebihi plafon maksimal Rp ${number_format(limit, 0, ',', '.')}.`;
                            focusTarget = nominalField;
                            return false;
                        }
                    }
                });
            }

            if (hasError) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validasi Belum Lengkap!',
                    text: errorMessage,
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    // Auto-focus ke field yang bermasalah setelah alert ditutup
                    if (focusTarget) {
                        // Scroll ke elemen yang bermasalah
                        $('html, body').animate({
                            scrollTop: focusTarget.closest('.form-group, td').offset().top - 100
                        }, 300);

                        // Jika select2, buka dropdown-nya
                        if (focusTarget.hasClass('select2')) {
                            focusTarget.select2('open');
                        } else if (focusTarget.hasClass('file-input')) {
                            // Untuk file input, trigger click pada label-nya
                            focusTarget.closest('label').addClass('border-danger');
                            setTimeout(() => focusTarget.closest('label').removeClass('border-danger'), 3000);
                        } else {
                            focusTarget.focus();
                        }
                    }
                });
                return false;
            }

            // Jika lolos validasi, tampilkan loading
            $("#btnSubmit").html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...').attr('disabled', true);
            return true;
        });
    });

    function number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>
@endpush
