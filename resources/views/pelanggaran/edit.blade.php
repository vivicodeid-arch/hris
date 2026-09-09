<form action="{{ route('pelanggaran.update', Crypt::encrypt($pelanggaran->no_sp)) }}" method="POST" id="formEditPelanggaran">
    @csrf
    @method('PUT')
    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="nik_edit" class="form-label" style="font-weight: 600;">Karyawan <span class="text-danger">*</span></label>
                                <select name="nik" id="nik_edit" class="form-select select2Nik @error('nik') is-invalid @enderror" required>
                                    <option value="">Pilih Karyawan</option>
                                    @foreach ($karyawans as $karyawan)
                                        <option value="{{ $karyawan->nik }}" {{ old('nik', $pelanggaran->nik) == $karyawan->nik ? 'selected' : '' }}>
                                            {{ $karyawan->nik_show ?? $karyawan->nik }} - {{ $karyawan->nama_karyawan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('nik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="tanggal_edit" class="form-label" style="font-weight: 600;">Tanggal <span class="text-danger">*</span></label>
                                <x-input-with-icon icon="ti ti-calendar" label="" name="tanggal" datepicker="flatpickr-date"
                                    value="{{ old('tanggal', $pelanggaran->tanggal->format('Y-m-d')) }}" id="tanggal_edit" />
                                @error('tanggal')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="dari_edit" class="form-label" style="font-weight: 600;">Dari <span class="text-danger">*</span></label>
                                <x-input-with-icon icon="ti ti-calendar" label="" name="dari" datepicker="flatpickr-date"
                                    value="{{ old('dari', $pelanggaran->dari->format('Y-m-d')) }}" placeholder="Pilih Tanggal Dari" id="dari_edit" />
                                @error('dari')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="sampai_edit" class="form-label" style="font-weight: 600;">Sampai <span class="text-danger">*</span></label>
                                <x-input-with-icon icon="ti ti-calendar" label="" name="sampai" datepicker="flatpickr-date"
                                    value="{{ old('sampai', $pelanggaran->sampai->format('Y-m-d')) }}" placeholder="Pilih Tanggal Sampai" id="sampai_edit" />
                                @error('sampai')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="jenis_sp_edit" class="form-label" style="font-weight: 600;">Jenis SP <span class="text-danger">*</span></label>
                                <select name="jenis_sp" id="jenis_sp_edit" class="form-select @error('jenis_sp') is-invalid @enderror" required>
                                    <option value="">Pilih Jenis SP</option>
                                    <option value="SP1" {{ old('jenis_sp', $pelanggaran->jenis_sp) == 'SP1' ? 'selected' : '' }}>SP1</option>
                                    <option value="SP2" {{ old('jenis_sp', $pelanggaran->jenis_sp) == 'SP2' ? 'selected' : '' }}>SP2</option>
                                    <option value="SP3" {{ old('jenis_sp', $pelanggaran->jenis_sp) == 'SP3' ? 'selected' : '' }}>SP3</option>
                                </select>
                                @error('jenis_sp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="no_dokumen" class="form-label" style="font-weight: 600;">No Dokumen</label>
                                <input type="text" class="form-control @error('no_dokumen') is-invalid @enderror" id="no_dokumen" name="no_dokumen"
                                    value="{{ old('no_dokumen', $pelanggaran->no_dokumen) }}" placeholder="Masukkan nomor dokumen" maxlength="255">
                                @error('no_dokumen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="no_kontrak" class="form-label" style="font-weight: 600;">No. Perjanjian / Kontrak</label>
                                <input type="text" class="form-control @error('no_kontrak') is-invalid @enderror" id="no_kontrak" name="no_kontrak"
                                    value="{{ old('no_kontrak', $pelanggaran->no_kontrak) }}" placeholder="Masukkan nomor perjanjian / kontrak" maxlength="255">
                                @error('no_kontrak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Keterangan Pelanggaran Repeater -->
                        <div class="col-12">
                            <div class="card mb-3 border">
                                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-light">
                                    <span class="fw-bold text-dark" style="font-size: 13px;">Keterangan / Tindakan Pelanggaran <span class="text-danger">*</span></span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-keterangan">
                                        <i class="ti ti-plus me-1"></i> Tambah Pelanggaran
                                    </button>
                                </div>
                                <div class="card-body p-3" id="container-keterangan">
                                    @php
                                        $keteranganList = is_string($pelanggaran->keterangan) 
                                            ? json_decode($pelanggaran->keterangan, true) 
                                            : ($pelanggaran->keterangan ?? []);
                                        if (empty($keteranganList)) {
                                            $keteranganList = !empty($pelanggaran->keterangan) ? [$pelanggaran->keterangan] : [''];
                                        }
                                        if (!is_array($keteranganList)) {
                                            $keteranganList = [$keteranganList];
                                        }
                                    @endphp
                                    @foreach($keteranganList as $index => $k)
                                        <div class="row align-items-end mb-2 keterangan-item">
                                            <div class="col-md-11 col-12 mb-2 mb-md-0">
                                                <textarea name="keterangan[]" class="form-control form-control-sm" rows="2" placeholder="Masukkan keterangan pelanggaran...">{{ $k }}</textarea>
                                            </div>
                                            <div class="col-md-1 col-12 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-keterangan" {{ count($keteranganList) <= 1 ? 'disabled' : '' }}>
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Poin Pelanggaran Repeater -->
                        <div class="col-12">
                            <div class="card mb-3 border">
                                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-light">
                                    <span class="fw-bold text-dark" style="font-size: 13px;">A. Poin Pelanggaran</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-pasal">
                                        <i class="ti ti-plus me-1"></i> Tambah Poin
                                    </button>
                                </div>
                                <div class="card-body p-3" id="container-pasal">
                                    @php
                                        $pasalList = is_string($pelanggaran->pasal_pelanggaran) 
                                            ? json_decode($pelanggaran->pasal_pelanggaran, true) 
                                            : ($pelanggaran->pasal_pelanggaran ?? []);
                                        if (empty($pasalList)) {
                                            $pasalList = [''];
                                        }
                                    @endphp
                                    @foreach($pasalList as $index => $p)
                                        @php
                                            $val = '';
                                            if (is_array($p)) {
                                                $val = ($p['pasal'] ?? '') . (!empty($p['bunyi']) ? ': ' . $p['bunyi'] : '');
                                            } else {
                                                $val = $p;
                                            }
                                        @endphp
                                        <div class="row align-items-end mb-2 pasal-item">
                                            <div class="col-md-11 col-12 mb-2 mb-md-0">
                                                <label class="form-label small fw-bold">Poin Pelanggaran</label>
                                                <textarea name="pasal[]" class="form-control form-control-sm" rows="2" placeholder="Masukkan poin pelanggaran...">{{ $val }}</textarea>
                                            </div>
                                            <div class="col-md-1 col-12 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-pasal" {{ count($pasalList) <= 1 ? 'disabled' : '' }}>
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Dasar Peraturan Perundang-undangan Repeater -->
                        <div class="col-12">
                            <div class="card mb-3 border">
                                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-light">
                                    <span class="fw-bold text-dark" style="font-size: 13px;">B. Dasar Peraturan Perundang-undangan</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-uu">
                                        <i class="ti ti-plus me-1"></i> Tambah Dasar UU
                                    </button>
                                </div>
                                <div class="card-body p-3" id="container-uu">
                                    @php
                                        $uuList = is_string($pelanggaran->dasar_uu) 
                                            ? json_decode($pelanggaran->dasar_uu, true) 
                                            : ($pelanggaran->dasar_uu ?? []);
                                        if (empty($uuList)) {
                                            $uuList = [''];
                                        }
                                    @endphp
                                    @foreach($uuList as $index => $uu)
                                        <div class="row align-items-end mb-2 uu-item">
                                            <div class="col-md-11 col-12 mb-2 mb-md-0">
                                                <label class="form-label small fw-bold">Nama UU / Peraturan & Ketentuannya</label>
                                                <textarea name="dasar_uu[]" class="form-control form-control-sm" rows="2" placeholder="Contoh: Undang-Undang Nomor 13 Tahun 2003 tentang Ketenagakerjaan...">{{ $uu }}</textarea>
                                            </div>
                                            <div class="col-md-1 col-12 text-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-uu" {{ count($uuList) <= 1 ? 'disabled' : '' }}>
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100" id="btnSimpan">
                                <i class="ti ti-device-floppy me-2"></i>Update
                            </button>
                        </div>
                    </div>
</form>

<script>
    $(function() {
        // Initialize select2 for karyawan
        const select2Nik = $(".select2Nik");
        if (select2Nik.length) {
            select2Nik.each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Pilih Karyawan',
                    allowClear: true,
                    dropdownParent: $('#mdlEditPelanggaran')
                });
            });
        }

        // Initialize flatpickr for date inputs
        $('.flatpickr-date').flatpickr({
            dateFormat: 'Y-m-d',
            allowInput: false
        });

        function buttonDisabled() {
            $("#btnSimpan").prop('disabled', true);
            $("#btnSimpan").html(`
            <div class="spinner-border spinner-border-sm text-white me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            Loading..`);
        }

        $("#formEditPelanggaran").submit(function(e) {
            const nik = $("#nik_edit").val();
            const tanggal = $("#tanggal_edit").val();
            const dari = $("#dari_edit").val();
            const sampai = $("#sampai_edit").val();
            const jenis_sp = $("#jenis_sp_edit").val();
            const keterangan = $("#keterangan_edit").val();

            if (nik == '') {
                Swal.fire({
                    title: "Oops!",
                    text: "Karyawan harus diisi !",
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: () => {
                        $("#nik_edit").focus();
                    }
                });
                return false;
            } else if (tanggal == '') {
                Swal.fire({
                    title: "Oops!",
                    text: 'Tanggal Harus Diisi !',
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: () => {
                        $("#tanggal_edit").focus();
                    }
                });
                return false;
            } else if (dari == '') {
                Swal.fire({
                    title: "Oops!",
                    text: 'Dari Harus Diisi !',
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: () => {
                        $("#dari_edit").focus();
                    }
                });
                return false;
            } else if (sampai == '') {
                Swal.fire({
                    title: "Oops!",
                    text: 'Sampai Harus Diisi !',
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: () => {
                        $("#sampai_edit").focus();
                    }
                });
                return false;
            } else if (sampai < dari) {
                Swal.fire({
                    title: "Oops!",
                    text: 'Tanggal Sampai Tidak Boleh Lebih Kecil Dari Tanggal Dari !',
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: () => {
                        $("#sampai_edit").focus();
                    }
                });
                return false;
            } else if ($("textarea[name='keterangan[]']").first().val() == '') {
                Swal.fire({
                    title: "Oops!",
                    text: 'Keterangan / Tindakan Pelanggaran Harus Diisi !',
                    icon: "warning",
                    showConfirmButton: true,
                    didClose: () => {
                        $("textarea[name='keterangan[]']").first().focus();
                    }
                });
                return false;
            } else {
                buttonDisabled();
            }
        });

        // Repeater Keterangan
        $('#btn-tambah-keterangan').click(function(e) {
            e.preventDefault();
            var item = `
                <div class="row align-items-end mb-2 keterangan-item">
                    <div class="col-md-11 col-12 mb-2 mb-md-0">
                        <textarea name="keterangan[]" class="form-control form-control-sm" rows="2" placeholder="Masukkan keterangan pelanggaran..."></textarea>
                    </div>
                    <div class="col-md-1 col-12 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-keterangan">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#container-keterangan').append(item);
            toggleHapusKeteranganBtn();
        });

        $(document).on('click', '.btn-hapus-keterangan', function(e) {
            e.preventDefault();
            $(this).closest('.keterangan-item').remove();
            toggleHapusKeteranganBtn();
        });

        function toggleHapusKeteranganBtn() {
            var count = $('.keterangan-item').length;
            if (count <= 1) {
                $('.btn-hapus-keterangan').prop('disabled', true);
            } else {
                $('.btn-hapus-keterangan').prop('disabled', false);
            }
        }

        // Repeater Pasal
        $('#btn-tambah-pasal').click(function(e) {
            e.preventDefault();
            var item = `
                <div class="row align-items-end mb-2 pasal-item">
                    <div class="col-md-11 col-12 mb-2 mb-md-0">
                        <textarea name="pasal[]" class="form-control form-control-sm" rows="2" placeholder="Masukkan poin pelanggaran..."></textarea>
                    </div>
                    <div class="col-md-1 col-12 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-pasal">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#container-pasal').append(item);
            toggleHapusPasalBtn();
        });

        $(document).on('click', '.btn-hapus-pasal', function(e) {
            e.preventDefault();
            $(this).closest('.pasal-item').remove();
            toggleHapusPasalBtn();
        });

        function toggleHapusPasalBtn() {
            var count = $('.pasal-item').length;
            if (count <= 1) {
                $('.btn-hapus-pasal').prop('disabled', true);
            } else {
                $('.btn-hapus-pasal').prop('disabled', false);
            }
        }

        // Repeater UU
        $('#btn-tambah-uu').click(function(e) {
            e.preventDefault();
            var item = `
                <div class="row align-items-end mb-2 uu-item">
                    <div class="col-md-11 col-12 mb-2 mb-md-0">
                        <textarea name="dasar_uu[]" class="form-control form-control-sm" rows="2" placeholder="Contoh: Undang-Undang Nomor 13 Tahun 2003 tentang Ketenagakerjaan..."></textarea>
                    </div>
                    <div class="col-md-1 col-12 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-uu">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#container-uu').append(item);
            toggleHapusUuBtn();
        });

        $(document).on('click', '.btn-hapus-uu', function(e) {
            e.preventDefault();
            $(this).closest('.uu-item').remove();
            toggleHapusUuBtn();
        });

        function toggleHapusUuBtn() {
            var count = $('.uu-item').length;
            if (count <= 1) {
                $('.btn-hapus-uu').prop('disabled', true);
            } else {
                $('.btn-hapus-uu').prop('disabled', false);
            }
        }
    });
</script>

