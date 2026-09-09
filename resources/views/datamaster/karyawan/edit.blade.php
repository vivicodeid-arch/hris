<form action="{{ route('karyawan.update', Crypt::encrypt($karyawan->nik)) }}" id="formcreateKaryawan" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <x-input-with-icon-label icon="ti ti-barcode" label="NIK" name="nik_show" value="{{ $karyawan->nik_show ?? $karyawan->nik }}" />
    <x-input-with-icon-label icon="ti ti-credit-card" label="No. KTP" name="no_ktp" value="{{ $karyawan->no_ktp }}" />
    <x-input-with-icon-label icon="ti ti-credit-card-pay" label="NPWP" name="npwp" value="{{ $karyawan->npwp }}" />
    <x-input-with-icon-label icon="ti ti-user" label="Nama Karyawan" name="nama_karyawan" value="{{ $karyawan->nama_karyawan }}" />
    <div class="row">
        <div class="col-6">
            <x-input-with-icon-label icon="ti ti-map-pin" label="Tempat Lahir" name="tempat_lahir" value="{{ $karyawan->tempat_lahir }}" />
        </div>
        <div class="col-6">
            <x-input-with-icon-label icon="ti ti-calendar" label="Tanggal Lahir" datepicker="flatpickr-date" name="tanggal_lahir"
                value="{{ $karyawan->tanggal_lahir }}" />
        </div>
    </div>
    <x-textarea-label label="Alamat" name="alamat" value="{{ $karyawan->alamat }}" />
    <x-textarea-label label="Alamat Sesuai KTP" name="alamat_sesuai_ktp" value="{{ $karyawan->alamat_sesuai_ktp }}" />
    <div class="form-group mb-3">
        <label for="exampleFormControlInput1" style="font-weight: 600" class="form-label">Jenis Kelamin</label>
        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select">
            <option value="">Jenis Kelamin</option>
            <option value="L" {{ $karyawan->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki - Laki</option>
            <option value="P" {{ $karyawan->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
        </select>
    </div>
    <x-input-with-icon-label icon="ti ti-phone" label="No. HP" name="no_hp" value="{{ $karyawan->no_hp }}" />
    <x-input-with-icon-label icon="ti ti-mail" label="Alamat Email" name="email" value="{{ $karyawan->email }}" />
    <div class="row">
        <div class="col-6">
            <x-input-with-icon-label icon="ti ti-phone-call" label="Kontak Darurat" name="kontak_darurat" value="{{ $karyawan->kontak_darurat }}" />
        </div>
        <div class="col-6">
            <x-input-with-icon-label icon="ti ti-users" label="Hubungan Kontak Darurat" name="hubungan_kontak_darurat" value="{{ $karyawan->hubungan_kontak_darurat }}" />
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <x-input-with-icon-label icon="ti ti-building-bank" label="Nama Bank" name="nama_bank" value="{{ $karyawan->nama_bank }}" />
        </div>
        <div class="col-6">
            <x-input-with-icon-label icon="ti ti-credit-card" label="No. Rekening" name="no_rekening" value="{{ $karyawan->no_rekening }}" />
        </div>
    </div>
    <x-input-with-icon-label icon="ti ti-user" label="Nama Rekening" name="nama_rekening" value="{{ $karyawan->nama_rekening }}" />
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-12">
            <x-select-label label="Status Perkawinan" name="kode_status_kawin" :data="$status_kawin" key="kode_status_kawin" textShow="status_kawin"
                kode="true" selected="{{ $karyawan->kode_status_kawin }}" />
        </div>
        <div class="col-lg-6 col-sm-12 col-md-12">
            <div class="form-group mb-3">
                <label for="exampleFormControlInput1" style="font-weight: 600" class="form-label">Pendidikan
                    Terakhir</label>
                <select name="pendidikan_terakhir" id="pendidikan_terakhir" class="form-select">
                    <option value="">Pendidikan Terakhir</option>
                    <option value="SD" {{ $karyawan->pendidikan_terakhir == 'SD' ? 'selected' : '' }}>SD</option>
                    <option value="SMP" {{ $karyawan->pendidikan_terakhir == 'SMP' ? 'selected' : '' }}>SMP</option>
                    <option value="SMA" {{ $karyawan->pendidikan_terakhir == 'SMA' ? 'selected' : '' }}>SMA</option>
                    <option value="SMK" {{ $karyawan->pendidikan_terakhir == 'SMK"' ? 'selected' : '' }}>SMK</option>
                    <option value="D1" {{ $karyawan->pendidikan_terakhir == 'D1' ? 'selected' : '' }}>D1</option>
                    <option value="D2" {{ $karyawan->pendidikan_terakhir == 'D2' ? 'selected' : '' }}>D2</option>
                    <option value="D3" {{ $karyawan->pendidikan_terakhir == 'D3' ? 'selected' : '' }}>D3</option>
                    <option value="D4" {{ $karyawan->pendidikan_terakhir == 'D4' ? 'selected' : '' }}>D4</option>
                    <option value="S1" {{ $karyawan->pendidikan_terakhir == 'S1' ? 'selected' : '' }}>S1</option>
                    <option value="S2" {{ $karyawan->pendidikan_terakhir == 'S2' ? 'selected' : '' }}>S2</option>
                    <option value="S3" {{ $karyawan->pendidikan_terakhir == 'S3' ? 'selected' : '' }}>S3</option>
                </select>
            </div>
        </div>
    </div>
    <x-input-with-icon-label icon="ti ti-school" label="Jurusan" name="jurusan" value="{{ $karyawan->jurusan }}" />
    <x-select-label label="Kantor Cabang" name="kode_cabang" :data="$cabang" key="kode_cabang" textShow="nama_cabang"
        selected="{{ $karyawan->kode_cabang }}" />
    <x-select-label label="Departemen" name="kode_dept" :data="$departemen" selected="{{ $karyawan->kode_dept }}" key="kode_dept" textShow="nama_dept"
        upperCase="true" />
    </div>
    <x-select-label label="Jabatan" name="kode_jabatan" :data="$jabatan" selected="{{ $karyawan->kode_jabatan }}" key="kode_jabatan"
        textShow="nama_jabatan" upperCase="true" />
    <x-input-with-icon-label icon="ti ti-calendar" datepicker="flatpickr-date" label="Tanggal Masuk" name="tanggal_masuk"
        value="{{ $karyawan->tanggal_masuk }}" />
    <x-select-label label="Status Karyawan" name="status_karyawan" :data="$status_karyawan" key="kode_status_karyawan" textShow="nama_status_karyawan"
        selected="{{ $karyawan->status_karyawan }}" />

    <div class="form-group mb-3">
        <label for="exampleFormControlInput1" style="font-weight: 600" class="form-label">Status Aktif Karyawan</label>
        <select name="status_aktif_karyawan" id="status_aktif_karyawan" class="form-select">
            <option value="">Status Aktif Karyawan</option>
            <option value="1" {{ $karyawan->status_aktif_karyawan == '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ $karyawan->status_aktif_karyawan === '0' ? 'selected' : '' }}>Non Aktif</option>
        </select>
    </div>
    <x-input-file name="foto" label="Foto" />

    <x-input-with-icon-label icon="ti ti-id" label="RFID UID" name="rfid_uid" value="{{ $karyawan->rfid_uid }}" />
    <x-input-with-icon icon="ti ti-fingerprint" label="PIN Finger Print" name="pin" value="{{ $karyawan->pin }}" />
    <div class="form-group mb-3">
        <label style="font-weight: 600" class="form-label">Hitung PPh 21</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="hitung_pph21" id="hitung_pph21_edit" value="1" {{ ($karyawan->hitung_pph21 ?? 1) ? 'checked' : '' }} style="width: 2.8rem; height: 1.4rem; cursor: pointer;">
            <label class="form-check-label" for="hitung_pph21_edit" style="font-size: 0.85rem; margin-top: 2px;">Aktifkan perhitungan PPh 21 untuk karyawan ini</label>
        </div>
    </div>
    <div class="form-group">
        <button class="btn btn-primary w-100" type="submit">
            <ion-icon name="send-outline" class="me-1"></ion-icon>
            Submit
        </button>
    </div>
</form>
<script src="{{ asset('assets/js/pages/karyawan.js') }}"></script>
<script src="{{ asset('assets/js/jquery.mask.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

<script>
    $(function() {

        $(".flatpickr-date").flatpickr();
        // $('#nik_show').mask('00.00.000');
    });
</script>
