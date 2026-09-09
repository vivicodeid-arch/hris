<form action="{{ route('jenisreimbursement.store') }}" method="POST" id="formJenisReimburse">
    @csrf
    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">Kode <span class="text-danger">*</span></label>
                <input type="text" name="kode_jenis_reimburse" class="form-control text-uppercase" placeholder="JR001" maxlength="5">
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">Nama Jenis <span class="text-danger">*</span></label>
                <input type="text" name="nama_jenis" class="form-control" placeholder="E.g. Transportasi">
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label class="form-label fw-bold">Deskripsi</label>
        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Penjelasan singkat mengenai jenis ini..."></textarea>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">Plafon per Klaim</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="batas_nominal" class="form-control money text-end fw-bold" placeholder="Unlimited">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">Plafon per Bulan</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="batas_nominal_bulanan" class="form-control money text-end" placeholder="Unlimitied">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">Plafon per Tahun</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="batas_nominal_tahunan" class="form-control money text-end" placeholder="Unlimitied">
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="wajib_bukti" value="1" id="wajib_bukti" checked>
                <label class="form-check-label fw-bold" for="wajib_bukti">Wajib Upload Bukti Nota</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="status" value="1" id="status" checked>
                <label class="form-check-label fw-bold" for="status">Status Aktif</label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-end">
            <button type="button" class="btn btn-label-secondary me-2" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Simpan
            </button>
        </div>
    </div>
</form>

<script>
    $(function() {
        $(".money").maskMoney({
            thousands: '.',
            decimal: ',',
            precision: 0,
            allowZero: true
        });

        $("#formJenisReimburse").submit(function(e) {
            let kode = $("input[name='kode_jenis_reimburse']").val();
            let nama = $("input[name='nama_jenis']").val();

            if (kode == "" || nama == "") {
                Swal.fire({
                    title: 'Oops!',
                    text: 'Harap isi semua field bertanda bintang (*)',
                    icon: 'warning'
                });
                return false;
            }
        });
    });
</script>
