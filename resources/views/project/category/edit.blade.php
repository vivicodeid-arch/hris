<form action="{{ route('projectcategory.update', Crypt::encrypt($category->id)) }}" method="POST" id="formEditProjectCategory">
    @csrf
    @method('PUT')
    
    <div class="form-group mb-3">
        <label class="form-label" for="nama_kategori">Nama Kategori</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="ti ti-tag"></i></span>
            <input type="text" class="form-control" name="nama_kategori" id="nama_kategori" value="{{ $category->nama_kategori }}" placeholder="Contoh: IT, Marketing, dll" required maxlength="100" />
        </div>
    </div>

    <div class="form-group mb-3">
        <label class="form-label" for="deskripsi">Deskripsi</label>
        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3" placeholder="Masukkan deskripsi singkat kategori...">{{ $category->deskripsi }}</textarea>
    </div>

    <div class="form-group mb-3">
        <label class="form-label" for="warna">Warna Label (Hex)</label>
        <div class="d-flex align-items-center">
            <input type="color" class="form-control form-control-color me-3" id="edit_warna_picker" value="{{ $category->warna }}" style="width: 50px; height: 38px; padding: 2px;" />
            <input type="text" class="form-control" name="warna" id="edit_warna_hex" value="{{ $category->warna }}" maxlength="7" placeholder="#696cff" required />
        </div>
    </div>

    <div class="form-group mb-1">
        <button type="submit" class="btn btn-primary w-100"><i class="ti ti-device-floppy me-1"></i> Update Kategori</button>
    </div>
</form>

<script>
    $(function() {
        // Sync color picker with text input
        $('#edit_warna_picker').on('input', function() {
            $('#edit_warna_hex').val($(this).val());
        });
        $('#edit_warna_hex').on('input', function() {
            var val = $(this).val();
            if(val.startsWith('#') && val.length === 7) {
                $('#edit_warna_picker').val(val);
            }
        });
    });
</script>
