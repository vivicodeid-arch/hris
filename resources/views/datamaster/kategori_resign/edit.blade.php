<form action="{{ route('kategori_resign.update', Crypt::encrypt($kategori_resign->kode_kategori)) }}" method="POST" id="formKategoriResign">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-12">
            <x-input-with-icon label="Kode Kategori" name="kode_kategori" icon="ti ti-barcode" maxlength="5" value="{{ $kategori_resign->kode_kategori }}" />
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <x-input-with-icon label="Nama Kategori Resign" name="nama_kategori" icon="ti ti-id-badge" value="{{ $kategori_resign->nama_kategori }}" />
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <button class="btn btn-primary w-100" type="submit">
                <i class="ti ti-rotate me-1"></i> Update
            </button>
        </div>
    </div>
</form>

<script>
    $(function() {
        $("#formKategoriResign").submit(function() {
            const kode_kategori = $("#kode_kategori").val();
            const nama_kategori = $("#nama_kategori").val();
            if (kode_kategori == "") {
                Swal.fire({
                    title: 'Oops!',
                    text: 'Kode Kategori Harus Diisi!',
                    icon: 'warning',
                    showConfirmButton: true
                });
                return false;
            } else if (nama_kategori == "") {
                Swal.fire({
                    title: 'Oops!',
                    text: 'Nama Kategori Harus Diisi!',
                    icon: 'warning',
                    showConfirmButton: true
                });
                return false;
            }
        });
    });
</script>
