<form action="{{ route('project.store') }}" method="POST" id="formCreateProject">
    @csrf
    
    <div class="row">
        <!-- Left Column: Project Info -->
        <div class="col-lg-6 col-md-12">
            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="ti ti-info-circle me-1"></i> Informasi Utama</h5>
            
            <div class="mb-3">
                <label class="form-label fw-bold" for="nama_project">Nama Project <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nama_project" id="nama_project" value="{{ old('nama_project') }}" placeholder="Contoh: Pembuatan Website E-Commerce" required />
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" for="deskripsi">Deskripsi Project</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi" rows="4" placeholder="Masukkan deskripsi detail mengenai project...">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="category_id">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select" name="category_id" id="category_id" required>
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="prioritas">Prioritas <span class="text-danger">*</span></label>
                    <select class="form-select" name="prioritas" id="prioritas" required>
                        <option value="low" {{ old('prioritas') == 'low' ? 'selected' : '' }}>RENDAH (LOW)</option>
                        <option value="medium" {{ old('prioritas') == 'medium' || !old('prioritas') ? 'selected' : '' }}>SEDANG (MEDIUM)</option>
                        <option value="high" {{ old('prioritas') == 'high' ? 'selected' : '' }}>TINGGI (HIGH)</option>
                        <option value="critical" {{ old('prioritas') == 'critical' ? 'selected' : '' }}>KRITIS (CRITICAL)</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="kode_dept">Departemen Terkait</label>
                    <select class="form-select" name="kode_dept" id="kode_dept">
                        <option value="">Semua Departemen</option>
                        @foreach ($departements as $dept)
                            <option value="{{ $dept->kode_dept }}" {{ old('kode_dept') == $dept->kode_dept ? 'selected' : '' }}>{{ strtoupper($dept->nama_dept) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="kode_cabang">Cabang Terkait</label>
                    <select class="form-select" name="kode_cabang" id="kode_cabang">
                        <option value="">Semua Cabang</option>
                        @foreach ($cabangs as $cab)
                            <option value="{{ $cab->kode_cabang }}" {{ old('kode_cabang') == $cab->kode_cabang ? 'selected' : '' }}>{{ strtoupper($cab->nama_cabang) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="start_date">Tanggal Mulai <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                        <input type="text" class="form-control flatpickr-date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required placeholder="Tanggal Mulai" />
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="end_date">Tanggal Selesai (Deadline) <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                        <input type="text" class="form-control flatpickr-date" name="end_date" id="end_date" value="{{ old('end_date') }}" required placeholder="Tanggal Selesai (Deadline)" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Budget & Team Members -->
        <div class="col-lg-6 col-md-12">
            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="ti ti-users me-1"></i> Penugasan Tim & Budget</h5>
            
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="status">Status Project <span class="text-danger">*</span></label>
                    <select class="form-select" name="status" id="status" required>
                        <option value="planning" {{ old('status') == 'planning' ? 'selected' : '' }}>PLANNING</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>IN PROGRESS</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>COMPLETED</option>
                        <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>ON HOLD</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>CANCELLED</option>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="budget">Anggaran (Budget)</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control" name="budget" id="budget" value="{{ old('budget') }}" placeholder="Contoh: 50.000.000" style="text-align: right;" />
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" for="leader_nik">Project Leader <span class="text-danger">*</span></label>
                <select class="form-select select-search" name="leader_nik" id="leader_nik" required>
                    <option value="">Pilih Project Leader</option>
                    @foreach ($karyawan as $k)
                        <option value="{{ $k->nik }}" {{ old('leader_nik') == $k->nik ? 'selected' : '' }}>
                            {{ !empty($k->nik_show) ? $k->nik_show : $k->nik }} - {{ $k->nama_karyawan }} ({{ $k->jabatan ? $k->jabatan->nama_jabatan : '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" for="members">Anggota Tim (Multi-select)</label>
                <select class="form-select select-search-multiple" name="members[]" id="members" multiple style="min-height: 120px;">
                    @foreach ($karyawan as $k)
                        <option value="{{ $k->nik }}" {{ (collect(old('members'))->contains($k->nik)) ? 'selected' : '' }}>
                            {{ $k->nama_karyawan }} ({{ $k->jabatan ? $k->jabatan->nama_jabatan : '-' }})
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Tekan tombol Ctrl (Windows) / Cmd (Mac) untuk memilih lebih dari satu karyawan.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" for="catatan">Catatan Tambahan</label>
                <textarea class="form-control" name="catatan" id="catatan" rows="3" placeholder="Masukkan catatan tambahan jika diperlukan...">{{ old('catatan') }}</textarea>
            </div>
        </div>
    </div>

    <div class="row mt-3 border-top pt-3">
        <div class="col-12 d-flex justify-content-end">
            @if(request()->ajax())
                <button type="button" class="btn btn-label-secondary me-2" data-bs-dismiss="modal">Batal</button>
            @else
                <a href="{{ route('project.index') }}" class="btn btn-label-secondary me-2">Batal</a>
            @endif
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan Project</button>
        </div>
    </div>
</form>

<script>
    $(function() {
        // Auto format rupiah input for budget
        $('#budget').on('input', function() {
            var val = $(this).val().replace(/[^0-9]/g, '');
            if(val) {
                $(this).val(new Intl.NumberFormat('id-ID').format(val));
            } else {
                $(this).val('');
            }
        });

        // Initialize Select2 for Project Leader
        if ($('#leader_nik').length) {
            $('#leader_nik').select2({
                dropdownParent: $('#modalCreateProject').length ? $('#modalCreateProject') : null,
                width: '100%'
            });
        }

        // Initialize Select2 for Team Members
        if ($('#members').length) {
            $('#members').select2({
                dropdownParent: $('#modalCreateProject').length ? $('#modalCreateProject') : null,
                width: '100%',
                placeholder: 'Pilih Anggota Tim'
            });
        }

        // Initialize Flatpickr for dates
        if ($('.flatpickr-date').length) {
            $('.flatpickr-date').flatpickr({
                dateFormat: 'Y-m-d',
                allowInput: true
            });
        }
    });
</script>
