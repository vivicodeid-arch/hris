<form action="{{ route('resign.store') }}" method="POST" id="formResign" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-12">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">Pilih Karyawan Aktif</label>
                <select name="nik" id="nik" class="form-select select2-resign" required>
                    <option value="">Pilih Karyawan</option>
                    @foreach ($karyawan as $k)
                        <option value="{{ $k->nik }}">{{ $k->nik }} - {{ $k->nama_karyawan }} ({{ $k->cabang->nama_cabang ?? '' }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">Kategori Resign / Non-Aktif</label>
                <select name="kode_kategori" id="kode_kategori" class="form-select" required>
                    <option value="">Pilih Kategori</option>
                    @foreach ($kategori as $kat)
                        <option value="{{ $kat->kode_kategori }}">{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">Tanggal Resign / Non-Aktif</label>
                <input type="text" id="tanggal_resign" name="tanggal_resign" class="form-control flatpickr-date-resign" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">Alasan Resign</label>
                <textarea name="alasan" class="form-control" rows="3" placeholder="Masukkan alasan pengunduran diri..."></textarea>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-group mb-3">
                <label class="form-label fw-bold">Upload Dokumen Pendukung (Opsional, PDF/Gambar/Word)</label>
                <input type="file" name="dokumen" class="form-control" accept=".pdf,image/*,.doc,.docx">
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="ti ti-alert-triangle me-2"></i>
                <div style="font-size: 13px;">
                    <strong>Perhatian:</strong> Menyimpan data ini akan otomatis mengubah status karyawan menjadi <strong>Non Aktif</strong> pada Data Master Karyawan.
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <button type="submit" class="btn btn-danger w-100"><i class="ti ti-user-x me-1"></i> Simpan & Non-Aktifkan Karyawan</button>
        </div>
    </div>
</form>

<script>
    $(function() {
        $(".select2-resign").select2({
            dropdownParent: $('#modalInput')
        });

        $(".flatpickr-date-resign").flatpickr({
            dateFormat: "Y-m-d"
        });
    });
</script>
