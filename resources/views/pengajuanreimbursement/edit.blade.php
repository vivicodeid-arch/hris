@extends('layouts.mobile.app')
@section('content')
    <style>
        #header-section {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        #content-section {
            margin-top: 70px;
            padding-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .item-card {
            border: 1px solid #eee;
            border-radius: 12px;
            background: #fff;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            position: relative;
        }

        .btn-remove-item {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 24px;
            height: 24px;
            background: #ff5252;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>

    <div id="header-section">
        <div class="appHeader bg-primary text-light">
            <div class="left">
                <a href="{{ route('pengajuanreimbursement.show', Crypt::encrypt($reimbursement->id)) }}" class="headerButton goBack">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                </a>
            </div>
            <div class="pageTitle">Edit Reimbursement</div>
            <div class="right"></div>
        </div>
    </div>

    <div id="content-section" class="px-3">
        <form action="{{ route('pengajuanreimbursement.update', Crypt::encrypt($reimbursement->id)) }}" method="POST" enctype="multipart/form-data" id="formReimbursement">
            @csrf
            @method('PUT')
            
            {{-- Global Section --}}
            <div class="card border-0 shadow-sm mb-3 mt-2" style="border-radius: 15px;">
                <div class="card-body p-3">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Tanggal Pengajuan</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ $reimbursement->tanggal_pengajuan }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label fw-bold text-dark" style="font-size: 13px;">Keterangan Global</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Perjalanan dinas Jakarta" required>{{ $reimbursement->catatan }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <h6 class="mb-0 fw-bold text-secondary">Rincian Item Nota</h6>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnAddRow" style="font-size: 11px;">
                    <i class="ti ti-plus me-1"></i> Tambah Item
                </button>
            </div>

            {{-- Items Container --}}
            <div id="items-container">
                @foreach($reimbursement->details as $index => $item)
                    <div class="item-card p-3">
                        @if($index > 0)
                            <button type="button" class="btn-remove-item"><ion-icon name="close"></ion-icon></button>
                        @endif
                        <input type="hidden" name="items[{{ $index }}][old_id]" value="{{ $item->id }}">
                        
                        {{-- Row 1: Kategori --}}
                        <div class="form-group mb-2">
                            <label class="form-label text-muted mb-1" style="font-size: 11px; font-weight: 600;">JENIS REIMBURSE</label>
                            <select name="items[{{ $index }}][item_kategori]" class="form-select border-0 bg-light select-jenis" style="font-size: 14px; height: 42px; border-radius: 10px;" required>
                                <option value="">Pilih Jenis</option>
                                @foreach($jenis_reimburse as $j)
                                    <option value="{{ $j->kode_jenis_reimburse }}" {{ $item->kode_jenis_reimburse == $j->kode_jenis_reimburse ? 'selected' : '' }} data-limit="{{ $j->limit_nominal }}" data-wajib-bukti="{{ $j->wajib_bukti }}">{{ $j->nama_jenis }}</option>
                                @endforeach
                            </select>
                            <div class="limit-info mt-1" style="font-size: 10px; color: #ff9f40; font-weight: 600;">
                                @php
                                    $cur_jenis = $jenis_reimburse->where('kode_jenis_reimburse', $item->kode_jenis_reimburse)->first();
                                    $limit = $cur_jenis ? $cur_jenis->limit_nominal : 0;
                                @endphp
                                @if($limit > 0) Plafon Maksimal: Rp {{ number_format($limit, 0, ',', '.') }} @endif
                            </div>
                        </div>
                        
                        {{-- Row 2: Deskripsi --}}
                        <div class="form-group mb-2">
                            <label class="form-label text-muted mb-1" style="font-size: 11px; font-weight: 600;">KETERANGAN NOTA</label>
                            <input type="text" name="items[{{ $index }}][item_keterangan]" class="form-control border-0 bg-light" style="font-size: 14px; height: 42px; border-radius: 10px;" value="{{ $item->keterangan }}" placeholder="Contoh: Tiket Pesawat / Bensin" required>
                        </div>

                        <div class="row g-2">
                            {{-- Row 3: Nominal --}}
                            <div class="col-7">
                                <div class="form-group">
                                    <label class="form-label text-muted mb-1" style="font-size: 11px; font-weight: 600;">JUMLAH (RP)</label>
                                    <input type="text" name="items[{{ $index }}][item_jumlah]" class="form-control border-0 bg-light money nominal-field" style="font-size: 14px; height: 42px; border-radius: 10px; font-weight: 700;" value="{{ number_format($item->nominal, 0, ',', '.') }}" placeholder="0" required>
                                </div>
                            </div>
                            {{-- Row 4: Upload --}}
                            <div class="col-5">
                                <div class="form-group">
                                    <label class="form-label text-muted mb-1" style="font-size: 11px; font-weight: 600;">FOTO NOTA</label>
                                    <label class="d-flex align-items-center justify-content-center border-0 {{ $item->bukti_file ? 'bg-success text-white' : 'bg-light' }} px-2" style="height: 42px; border-radius: 10px; cursor: pointer; border: 1px dashed #ccc !important;">
                                        <ion-icon name="camera-outline" class="me-1"></ion-icon>
                                        <span class="file-label" style="font-size: 10px; font-weight: 600;">{{ $item->bukti_file ? 'File Ada' : 'Pilih File' }}</span>
                                        <input type="file" name="items[{{ $index }}][item_foto]" class="d-none file-input" accept="image/*" capture="camera">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Summary card --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; background: linear-gradient(135deg, var(--theme-color-1, #2d5a4c), var(--theme-color-2, #3e7b68));">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-white opacity-75 d-block" style="font-size: 11px;">ESTIMASI TOTAL</span>
                        <h4 class="text-white mb-0 fw-bold" id="grandTotalLabel">Rp {{ number_format($reimbursement->total_nominal, 0, ',', '.') }}</h4>
                    </div>
                    <button type="submit" class="btn btn-white rounded-pill px-4 fw-bold" id="btnSubmit" style="color: var(--theme-color-1, #2d5a4c);">
                        Update Pengajuan
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Template for new rows --}}
    <div id="item-template" style="display: none;">
        <div class="item-card p-3">
            <button type="button" class="btn-remove-item"><ion-icon name="close"></ion-icon></button>
            <div class="form-group mb-2">
                <label class="form-label text-muted mb-1" style="font-size: 11px; font-weight: 600;">JENIS REIMBURSE</label>
                <select class="form-select border-0 bg-light select-jenis" style="font-size: 14px; height: 42px; border-radius: 10px;" required>
                    <option value="">Pilih Jenis</option>
                    @foreach($jenis_reimburse as $j)
                        <option value="{{ $j->kode_jenis_reimburse }}" data-limit="{{ $j->limit_nominal }}" data-wajib-bukti="{{ $j->wajib_bukti }}">{{ $j->nama_jenis }}</option>
                    @endforeach
                </select>
                <div class="limit-info mt-1" style="font-size: 10px; color: #ff9f40; font-weight: 600;"></div>
            </div>
            
            <div class="form-group mb-2">
                <label class="form-label text-muted mb-1" style="font-size: 11px; font-weight: 600;">KETERANGAN NOTA</label>
                <input type="text" class="form-control border-0 bg-light" style="font-size: 14px; height: 42px; border-radius: 10px;" placeholder="Contoh: Tiket Pesawat / Bensin" required>
            </div>

            <div class="row g-2">
                <div class="col-7">
                    <div class="form-group">
                        <label class="form-label text-muted mb-1" style="font-size: 11px; font-weight: 600;">JUMLAH (RP)</label>
                        <input type="text" class="form-control border-0 bg-light money nominal-field" style="font-size: 14px; height: 42px; border-radius: 10px; font-weight: 700;" placeholder="0" required>
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        <label class="form-label text-muted mb-1" style="font-size: 11px; font-weight: 600;">FOTO NOTA</label>
                        <label class="d-flex align-items-center justify-content-center border-0 bg-light px-2" style="height: 42px; border-radius: 10px; cursor: pointer; border: 1px dashed #ccc !important;">
                            <ion-icon name="camera-outline" class="me-1"></ion-icon>
                            <span class="file-label" style="font-size: 10px; font-weight: 600;">Pilih File</span>
                            <input type="file" class="d-none file-input" accept="image/*" capture="camera">
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('myscript')
<script src="{{ asset('assets/js/jquery.maskMoney.min.js') }}"></script>
<script>
    $(function() {
        let itemCount = {{ $reimbursement->details->count() }};

        function initMoney() {
            $('.money').maskMoney({
                thousands: '.', decimal: ',', precision: 0, allowZero: true
            });
        }
        initMoney();

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

        $(document).on("keyup", ".nominal-field", function() {
            calculateTotal();
        });

        $(document).on("change", ".select-jenis", function() {
            let limit = $(this).find(":selected").data("limit");
            let info = $(this).closest(".item-card").find(".limit-info");
            if (limit && limit > 0) {
                info.html(`Plafon Maksimal: Rp ${number_format(limit, 0, ',', '.')}`);
            } else {
                info.html("");
            }
        });

        $(document).on("change", ".file-input", function() {
            let file = this.files[0];
            if (file) {
                $(this).siblings(".file-label").text("File Terpilih");
                $(this).closest("label").removeClass("bg-light").addClass("bg-success text-white");
            }
        });

        $("#btnAddRow").click(function() {
            let template = $("#item-template").html();
            let newRow = $(template);
            
            // Set correct names for the new row
            newRow.find("select").attr("name", `items[${itemCount}][item_kategori]`);
            newRow.find("input[type='text']").eq(0).attr("name", `items[${itemCount}][item_keterangan]`);
            newRow.find("input[type='text']").eq(1).attr("name", `items[${itemCount}][item_jumlah]`);
            newRow.find("input[type='file']").attr("name", `items[${itemCount}][item_foto]`);
            
            $("#items-container").append(newRow);
            itemCount++;
            initMoney();
        });

        $(document).on("click", ".btn-remove-item", function() {
            $(this).closest(".item-card").fadeOut(300, function() {
                $(this).remove();
                calculateTotal();
            });
        });

        $("#formReimbursement").submit(function(e) {
            let hasError = false;
            let errorMessage = "";

            $(".item-card:visible").each(function(index) {
                if (hasError) return;
                
                let card = $(this);
                let jenis = card.find(".select-jenis");
                let nominal = card.find(".nominal-field");
                let file = card.find(".file-input");
                let option = jenis.find("option:selected");
                let hasOldFile = card.find("input[name*='old_id']").length > 0 && card.find(".file-label").text().trim() === "File Ada";
                
                if (!jenis.val()) {
                    hasError = true;
                    errorMessage = "Jenis reimbursement harus dipilih!";
                } else if (!nominal.val() || nominal.val() == "0") {
                    hasError = true;
                    errorMessage = "Nominal harus diisi!";
                } else {
                    let valNominal = parseFloat(nominal.val().replace(/\./g, '').replace(/,/g, '.'));
                    let limit = option.data("limit");
                    let wajibBukti = option.data("wajib-bukti");
                    
                    if (wajibBukti == 1 && file[0].files.length == 0 && !hasOldFile) {
                        hasError = true;
                        errorMessage = `Item "${option.text()}" wajib upload foto bukti!`;
                    } else if (limit > 0 && valNominal > limit) {
                        hasError = true;
                        errorMessage = `Item "${option.text()}" melebihi plafon (Max: Rp ${number_format(limit,0,',','.')})!`;
                    }
                }
            });

            if (hasError) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validasi Gagal!',
                    text: errorMessage,
                    icon: 'warning'
                });
                return false;
            }

            $("#btnSubmit").html('<span class="spinner-border spinner-border-sm me-1"></span> Mengupdate...').attr('disabled', true);
            return true;
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
    });
</script>
@endpush
