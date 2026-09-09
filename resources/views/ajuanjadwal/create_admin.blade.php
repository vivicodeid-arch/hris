<form action="{{ route('ajuanjadwal.store') }}" method="POST" id="formAjuanJadwal">
    @csrf
    @if(isset($karyawan) && count($karyawan) > 0)
    <div class="row">
        <div class="col-12">
            <div class="form-group mb-3">
                <label class="form-label"><b>Karyawan <span class="text-danger">*</span></b></label>
                <select name="nik" id="nik" class="form-select select2">
                    <option value="">Pilih Karyawan</option>
                    @foreach ($karyawan as $d)
                        <option value="{{ $d->nik }}">{{ $d->nama_karyawan }} ({{ $d->nik }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <x-input-with-icon label="Tanggal Perubahan" name="tanggal" icon="ti ti-calendar" datepicker="flatpickr-date" required />
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-group mb-3">
                <label class="form-label"><b>Shift Tujuan <span class="text-danger">*</span></b></label>
                <select name="kode_jam_kerja_tujuan" id="kode_jam_kerja_tujuan" class="form-select">
                    <option value="">Pilih Shift</option>
                    @foreach ($jamkerja as $d)
                        <option value="{{ $d->kode_jam_kerja }}">{{ $d->nama_jam_kerja }} ({{ $d->jam_masuk }} - {{ $d->jam_pulang }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-group mb-3">
                <label class="form-label"><b>Keterangan / Alasan <span class="text-danger">*</span></b></label>
                <textarea class="form-control" name="keterangan" id="keterangan" placeholder="Keterangan / Alasan" rows="2"></textarea>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <button type="submit" class="btn btn-primary w-100" id="btnSimpanAjuan">
                <i class="ti ti-send me-1"></i> Simpan Pengajuan
            </button>
        </div>
    </div>
</form>

<script>
    $(function() {
        $(".select2").select2({
            dropdownParent: $("#mdlCreateAjuanJadwal")
        });

        $(".flatpickr-date").flatpickr({
            dateFormat: "Y-m-d",
        });

        $("#formAjuanJadwal").submit(function(e) {
            var nik = $("#nik").val();
            var tanggal = $("#tanggal").val();
            var kode_jam_kerja_tujuan = $("#kode_jam_kerja_tujuan").val();
            var keterangan = $("#keterangan").val();

            if (nik == "" && $("#nik").length > 0) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Karyawan Harus Dipilih',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return false;
            } else if (tanggal == "") {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Tanggal Harus Diisi',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return false;
            } else if (kode_jam_kerja_tujuan == "") {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Shift Tujuan Harus Dipilih',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return false;
            } else if (keterangan == "") {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Keterangan Harus Diisi',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            $("#btnSimpanAjuan").attr("disabled", true);
            $("#btnSimpanAjuan").html("<span class='spinner-border spinner-border-sm me-1' role='status' aria-hidden='true'></span> Memproses...");
        });
    });
</script>
