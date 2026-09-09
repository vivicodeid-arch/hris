<form action="{{ route('tunjangan.import_proses') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h6 class="alert-heading fw-bold mb-1">Informasi Import Data</h6>
                <p class="mb-0">Silakan unduh template Excel terlebih dahulu, isi datanya, lalu unggah kembali di sini. Kolom tunjangan akan menyesuaikan dengan jenis tunjangan yang terdaftar.</p>
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <a href="{{ route('tunjangan.download_template') }}" class="btn btn-outline-success w-100 mb-3">
                <i class="ti ti-download me-1"></i> Unduh Template Excel
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="form-group mb-3">
                <label for="file" class="form-label">Pilih File Excel</label>
                <input type="file" name="file" id="file" class="form-control" required accept=".xlsx, .xls">
                <small class="text-muted">Format file: .xlsx, .xls</small>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn btn-primary w-100">
                <i class="ti ti-upload me-1"></i> Mulai Import Data
            </button>
        </div>
    </div>
</form>
