<form action="{{ route('pelatihan.store') }}" method="POST" id="formPelatihan" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="nik" value="{{ $nik }}">
    <x-input-with-icon-label icon="ti ti-school" label="Nama Pelatihan" name="nama_pelatihan" />
    <x-input-with-icon-label icon="ti ti-building" label="Penyelenggara" name="penyelenggara" />
    <x-input-with-icon-label icon="ti ti-calendar" label="Tanggal Pelatihan" name="tanggal_pelatihan" datepicker="flatpickr-date" />
    <x-input-file name="foto" label="Sertifikat/Foto (Optional)" />
    <div class="row mt-2">
        <div class="col-12">
            <button type="submit" class="btn btn-primary w-100"><i class="ti ti-send me-1"></i> Simpan Data</button>
        </div>
    </div>
</form>

<script>
    $(function() {
        $(".flatpickr-date").flatpickr();
    });
</script>
