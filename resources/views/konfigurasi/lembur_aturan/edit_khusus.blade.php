<form action="{{ route('lemburaturan.updatekhusus', $khusus->id) }}" method="POST" id="formLemburKhususUpdate">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label">Karyawan</label>
        <input type="text" class="form-control bg-light" value="{{ $khusus->nik }} - {{ $khusus->nama_karyawan }}" readonly>
    </div>

    <div class="mb-3">
        <label class="form-label">Upah Lembur / Jam (Nominal)</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text">Rp</span>
            <input type="text" name="upah_perjam" id="upah_perjam_khusus_edit" class="form-control money" value="{{ $khusus->upah_perjam }}" required>
        </div>
        <small class="text-muted" style="font-size: 0.7rem;">Nominal ini akan dikalikan langsung dengan JAM AKTUAL lembur.</small>
    </div>

    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="1" {{ $khusus->status == 1 ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ $khusus->status == 0 ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Keterangan / Alasan</label>
        <textarea name="keterangan" id="keterangan" class="form-control" rows="2">{{ $khusus->keterangan }}</textarea>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <button class="btn btn-primary w-100" id="btnUpdateKhusus" type="submit">
                <i class="ti ti-refresh me-1"></i> Update Lembur Khusus
            </button>
        </div>
    </div>
</form>

<script>
    $(function() {
        // Initialize MaskMoney (standard in this app)
        if ($.isFunction($.fn.maskMoney)) {
            $('.money').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0,
                allowZero: true,
                suffix: ''
            });
            // Trigger mask after setting value
            $('.money').maskMoney('mask');
        }

        $("#formLemburKhususUpdate").submit(function() {
            const upah = $("#upah_perjam_khusus_edit").val();

            if (upah === "") {
                Swal.fire('Peringatan', 'Upah per jam harus diisi', 'warning');
                return false;
            }

            $("#btnUpdateKhusus").prop('disabled', true).text("Loading...");
        });
    });
</script>
