<form action="{{ route('jenisreimbursement.storekaryawan', Crypt::encrypt($jenis->id)) }}" method="POST" id="formAddKaryawanEnroll">
    @csrf
    <div class="row mb-3">
        <div class="col-md-6 text-start">
            <div class="form-group mb-2">
                <label class="form-label fw-bold">Tanggal Mulai Berlaku <span class="text-danger">*</span></label>
                <input type="text" name="tanggal_mulai" class="form-control flatpickr-enroll" value="{{ date('Y-m-d') }}" placeholder="YYYY-MM-DD">
            </div>
        </div>
        <div class="col-md-6 text-start">
            <div class="form-group mb-2">
                <label class="form-label fw-bold">Tanggal Berakhir (Opsional)</label>
                <input type="text" name="tanggal_selesai" class="form-control flatpickr-enroll" placeholder="Kosongkan jika selamanya">
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12 text-start">
            <div class="form-group mb-2">
                <label class="form-label fw-bold">Override Plafon (Opsional)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="batas_nominal_override" class="form-control money-enroll" placeholder="Isi jika berbeda dengan plafon global">
                </div>
                <small class="text-muted italic">Biarkan kosong untuk mengikuti batas nominal global (Rp {{ number_format($jenis->batas_nominal, 0, ',', '.') }}).</small>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4 text-start">
            <div class="form-group mb-2">
                <label class="form-label fw-bold small text-muted">Cari Nama / NIK</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" id="filterSearch" class="form-control" placeholder="Ketik sesuatu...">
                </div>
            </div>
        </div>
        <div class="col-md-4 text-start">
            <div class="form-group mb-2">
                <label class="form-label fw-bold small text-muted">Filter Departemen</label>
                <select id="filterDept" class="form-select">
                    <option value="">Semua Departemen</option>
                    @foreach($departemen as $d)
                        <option value="{{ $d->kode_dept }}">{{ $d->nama_dept }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4 text-start">
            <div class="form-group mb-2">
                <label class="form-label fw-bold small text-muted">Filter Cabang</label>
                <select id="filterCabang" class="form-select">
                    <option value="">Semua Cabang</option>
                    @foreach($cabang as $c)
                        <option value="{{ $c->kode_cabang }}">{{ $c->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <hr>

    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-hover" id="tableKaryawanEnroll">
            <thead class="sticky-top bg-white">
                <tr>
                    <th style="width: 50px;">
                        <input class="form-check-input" type="checkbox" id="checkAllKaryawan">
                    </th>
                    <th>NIK</th>
                    <th>NAMA KARYAWAN</th>
                    <th>DEPARTEMEN</th>
                    <th>CABANG</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($karyawan as $k)
                    <tr class="karyawan-row" data-name="{{ strtolower($k->nama_karyawan) }}" data-nik="{{ strtolower($k->nik) }}" data-nik-show="{{ strtolower($k->nik_show) }}" data-dept="{{ $k->kode_dept }}" data-cabang="{{ $k->kode_cabang }}">
                        <td>
                            <input class="form-check-input checkKaryawan" type="checkbox" name="nik[]" value="{{ $k->nik }}">
                        </td>
                        <td>{{ $k->nik_show ?: $k->nik }}</td>
                        <td>{{ $k->nama_karyawan }}</td>
                        <td>{{ $k->nama_dept }}</td>
                        <td>{{ $k->nama_cabang }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-end">
            <button type="button" class="btn btn-label-secondary me-2" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSubmitEnroll">
                <i class="ti ti-user-plus me-1"></i> Daftarkan Karyawan Terpilih
            </button>
        </div>
    </div>
</form>

<script>
    $(function() {
        $(".money-enroll").maskMoney({
            thousands: '.', decimal: ',', precision: 0, allowZero: true
        });

        $(".flatpickr-enroll").flatpickr({
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d",
        });

        $("#checkAllKaryawan").click(function() {
            $(".checkKaryawan:visible").prop('checked', $(this).prop('checked'));
        });

        // Filtering Logic
        function filterKaryawan() {
            let search = $("#filterSearch").val().toLowerCase();
            let dept = $("#filterDept").val();
            let cabang = $("#filterCabang").val();

            $(".karyawan-row").each(function() {
                let name = String($(this).data('name'));
                let nik = String($(this).data('nik'));
                let nikShow = String($(this).data('nik-show'));
                let rowDept = $(this).data('dept');
                let rowCabang = $(this).data('cabang');

                let matchSearch = name.includes(search) || nik.includes(search) || nikShow.includes(search);
                let matchDept = dept == "" || rowDept == dept;
                let matchCabang = cabang == "" || rowCabang == cabang;

                if (matchSearch && matchDept && matchCabang) {
                    $(this).show();
                } else {
                    $(this).hide();
                    $(this).find(".checkKaryawan").prop('checked', false);
                }
            });
        }

        $("#filterSearch").on('keyup', filterKaryawan);
        $("#filterDept, #filterCabang").on('change', filterKaryawan);

        $("#formAddKaryawanEnroll").submit(function(e) {
            if ($(".checkKaryawan:checked").length == 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Silakan pilih minimal satu karyawan.',
                    icon: 'warning'
                });
                return false;
            }

            let tglMulai = $("input[name='tanggal_mulai']").val();
            if (tglMulai == "") {
                e.preventDefault();
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Tanggal mulai berlaku harus diisi.',
                    icon: 'warning'
                });
                return false;
            }
        });
    });
</script>
