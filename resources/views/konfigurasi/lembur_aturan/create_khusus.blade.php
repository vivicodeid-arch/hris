<form action="{{ route('lemburaturan.storekhusus') }}" method="POST" id="formLemburKhusus">
    @csrf
    <div class="mb-3">
        <label class="form-label">Pilih Karyawan</label>
        <select name="nik" id="nik_khusus" class="form-select select2-khusus" style="width: 100%" required>
            <option value="">Pilih Karyawan</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Upah Lembur / Jam (Nominal)</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text">Rp</span>
            <input type="text" name="upah_perjam" id="upah_perjam_khusus" class="form-control money" placeholder="Contoh: 25.000" required>
        </div>
        <small class="text-muted" style="font-size: 0.7rem;">Nominal ini akan dikalikan langsung dengan JAM AKTUAL lembur.</small>
    </div>

    <div class="mb-3">
        <label class="form-label">Keterangan / Alasan</label>
        <textarea name="keterangan" id="keterangan" class="form-control" rows="2" placeholder="Contoh: Kesepakatan Khusus HRD"></textarea>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <button class="btn btn-primary w-100" id="btnSimpanKhusus" type="submit">
                <i class="ti ti-send me-1"></i> Simpan Lembur Khusus
            </button>
        </div>
    </div>
</form>

<script>
    $(function() {
        // Initialize Select2 with AJAX
        $(".select2-khusus").select2({
            dropdownParent: $("#modal"),
            placeholder: 'Pilih Karyawan',
            allowClear: true,
            ajax: {
                url: "{{ route('karyawan.getkaryawan') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        nama_karyawan: params.term, // search term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function (item) {
                            return {
                                id: item.nik,
                                text: item.nik + ' - ' + item.nama_karyawan
                            }
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 3
        });

        // Initialize MaskMoney (standard in this app)
        if ($.isFunction($.fn.maskMoney)) {
            $('.money').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0,
                allowZero: true,
                suffix: ''
            });
        }

        $("#formLemburKhusus").submit(function() {
            const nik = $("#nik_khusus").val();
            const upah = $("#upah_perjam_khusus").val();

            if (nik === "") {
                Swal.fire('Peringatan', 'Silakan pilih karyawan terlebih dahulu', 'warning');
                return false;
            } else if (upah === "") {
                Swal.fire('Peringatan', 'Upah per jam harus diisi', 'warning');
                return false;
            }

            $("#btnSimpanKhusus").prop('disabled', true).text("Loading...");
        });
    });
</script>
