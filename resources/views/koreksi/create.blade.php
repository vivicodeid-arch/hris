<form action="{{ route('koreksi.store') }}" method="POST" id="formKoreksi">
    @csrf
    <x-input-with-icon icon="ti ti-barcode" label="Auto" name="kode_koreksi" disabled="true" />
    <div class="form-group mb-3">
        <select name="nik" id="nik" class="form-select select2Nik">
            <option value="">Pilih Karyawan</option>
            @foreach ($karyawan as $d)
                <option value="{{ $d->nik }}">{{ $d->nik_show ?? $d->nik }} - {{ $d->nama_karyawan }}</option>
            @endforeach
        </select>
    </div>
    <x-input-with-icon icon="ti ti-calendar" label="Tanggal Absen" name="tanggal" datepicker="flatpickr-date" />
    <div class="form-group mb-3">
        <select name="kode_jam_kerja" id="kode_jam_kerja" class="form-select">
            <option value="">Pilih Jadwal / Jam Kerja</option>
            @foreach ($jamkerja as $d)
                <option value="{{ $d->kode_jam_kerja }}">{{ $d->kode_jam_kerja }} - {{ $d->nama_jam_kerja }} ({{ $d->jam_masuk }} - {{ $d->jam_pulang }})</option>
            @endforeach
        </select>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="form-group mb-3">
                <label class="form-label">Jam Masuk</label>
                <input type="time" name="jam_in" class="form-control">
            </div>
        </div>
        <div class="col-6">
            <div class="form-group mb-3">
                <label class="form-label">Jam Pulang</label>
                <input type="time" name="jam_out" class="form-control">
            </div>
        </div>
    </div>
    <x-textarea label="Alasan Koreksi" name="keterangan" />
    <div class="form-group mb-3">
        <button class="btn btn-primary w-100" id="btnSimpan"><i class="ti ti-send me-1"></i>Kirim Pengajuan</button>
    </div>
</form>

<script>
    $(function() {
        $(".flatpickr-date").flatpickr();
        const select2Nik = $('.select2Nik');
        if (select2Nik.length) {
            select2Nik.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Pilih Karyawan',
                    allowClear: true,
                    dropdownParent: $this.parent()
                });
            });
        }

        $("#formKoreksi").submit(function(e) {
            const nik = $("#nik").val();
            const tanggal = $("#tanggal").val();
            const jam_in = $("input[name='jam_in']").val();
            const jam_out = $("input[name='jam_out']").val();
            const keterangan = $("#keterangan").val();

            if (nik == "") {
                Swal.fire({ title: "Oops!", text: "Karyawan harus diisi!", icon: "warning" });
                return false;
            } else if (tanggal == "") {
                Swal.fire({ title: "Oops!", text: "Tanggal harus diisi!", icon: "warning" });
                return false;
            } else if ($("#kode_jam_kerja").val() == "") {
                Swal.fire({ title: "Oops!", text: "Jadwal / Jam Kerja harus diisi!", icon: "warning" });
                return false;
            } else if (jam_in == "" && jam_out == "") {
                Swal.fire({ title: "Oops!", text: "Jam Masuk atau Jam Pulang harus diisi salah satu!", icon: "warning" });
                return false;
            } else if (keterangan == "") {
                Swal.fire({ title: "Oops!", text: "Alasan harus diisi!", icon: "warning" });
                return false;
            }
        });
    });
</script>
