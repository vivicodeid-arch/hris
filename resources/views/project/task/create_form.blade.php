<form action="{{ route('project.task.store', $projectId) }}" method="POST" id="formCreateTask">
    @csrf
    
    <div class="row">
        <!-- Left Column: Task Info -->
        <div class="col-lg-6 col-md-12">
            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="ti ti-info-circle me-1"></i> Detail Tugas</h5>
            
            <div class="mb-3">
                <label class="form-label fw-bold" for="judul">Judul Task <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="judul" id="judul" value="{{ old('judul') }}" placeholder="Contoh: Implementasi Desain Layout Dashboard" required />
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" for="deskripsi">Deskripsi Tugas</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi" rows="4" placeholder="Masukkan detail instruksi atau keterangan tugas..."></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" for="parent_id">Sub-task Dari (Parent Task)</label>
                <select class="form-select select-search-parent" name="parent_id" id="parent_id">
                    <option value="">-- Tanpa Parent (Tugas Utama) --</option>
                    @foreach ($parentTasks as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', isset($parentId) ? $parentId : '') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->kode_task }} - {{ $parent->judul }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih jika task ini merupakan sub-task dari task utama yang sudah ada.</small>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="start_date">Tanggal Mulai</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                        <input type="text" class="form-control flatpickr-date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}" placeholder="Tanggal Mulai" />
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="due_date">Deadline (Due Date)</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                        <input type="text" class="form-control flatpickr-date" name="due_date" id="due_date" value="{{ old('due_date') }}" placeholder="Deadline (Due Date)" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Status & Assignment -->
        <div class="col-lg-6 col-md-12">
            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="ti ti-users me-1"></i> Penugasan & Status</h5>
            
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="prioritas">Prioritas <span class="text-danger">*</span></label>
                    <select class="form-select" name="prioritas" id="prioritas" required>
                        <option value="low" {{ old('prioritas') == 'low' ? 'selected' : '' }}>RENDAH (LOW)</option>
                        <option value="medium" {{ old('prioritas') == 'medium' || !old('prioritas') ? 'selected' : '' }}>SEDANG (MEDIUM)</option>
                        <option value="high" {{ old('prioritas') == 'high' ? 'selected' : '' }}>TINGGI (HIGH)</option>
                        <option value="critical" {{ old('prioritas') == 'critical' ? 'selected' : '' }}>KRITIS (CRITICAL)</option>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="status">Status <span class="text-danger">*</span></label>
                    <select class="form-select" name="status" id="status" required>
                        <option value="todo" {{ old('status') == 'todo' || !old('status') ? 'selected' : '' }}>TO DO</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>IN PROGRESS</option>
                        <option value="review" {{ old('status') == 'review' ? 'selected' : '' }}>REVIEW</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>COMPLETED</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>CANCELLED</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="progress">Progress (%)</label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="progress" id="progress" min="0" max="100" value="{{ old('progress', 0) }}" required />
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label fw-bold" for="urutan">Urutan Tampil</label>
                    <input type="number" class="form-control" name="urutan" id="urutan" min="0" value="{{ old('urutan', 0) }}" />
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" for="members_select">Ditugaskan Kepada (Multi-select)</label>
                <select class="form-select select-search-multiple" name="members[]" id="members_select" multiple style="min-height: 150px;">
                    @foreach ($project->members as $member)
                        @if($member->karyawan)
                            <option value="{{ $member->nik }}" {{ (collect(old('members'))->contains($member->nik)) ? 'selected' : '' }}>
                                {{ $member->karyawan->nama_karyawan }} ({{ $member->karyawan->jabatan ? $member->karyawan->jabatan->nama_jabatan : 'Staf' }})
                            </option>
                        @endif
                    @endforeach
                </select>
                <small class="text-muted">Pilih satu atau beberapa anggota tim project ini. Tekan Ctrl/Cmd untuk memilih lebih dari satu.</small>
            </div>
        </div>
    </div>

    <div class="row mt-3 border-top pt-3">
        <div class="col-12 d-flex justify-content-end">
            @if(request()->ajax())
                <button type="button" class="btn btn-label-secondary me-2" data-bs-dismiss="modal">Batal</button>
            @else
                <a href="{{ route('project.show', $projectId) }}" class="btn btn-label-secondary me-2">Batal</a>
            @endif
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan Task</button>
        </div>
    </div>
</form>

<script>
    $(function() {
        // Auto match progress for completed status
        $('#status').on('change', function() {
            if ($(this).val() === 'completed') {
                $('#progress').val(100);
            } else if ($(this).val() === 'todo') {
                $('#progress').val(0);
            }
        });

        $('#progress').on('input', function() {
            var val = parseInt($(this).val());
            if (val >= 100) {
                $('#status').val('completed');
            } else if (val > 0 && $('#status').val() === 'todo') {
                $('#status').val('in_progress');
            } else if (val === 0) {
                $('#status').val('todo');
            }
        });

        // Initialize Select2 inside modal or page
        var selectParent = $('#modalCreateTask').length ? $('#modalCreateTask') : null;
        
        if ($('#members_select').length) {
            $('#members_select').select2({
                dropdownParent: selectParent,
                width: '100%',
                placeholder: 'Pilih Anggota Tim'
            });
        }
        
        if ($('#parent_id').length) {
            $('#parent_id').select2({
                dropdownParent: selectParent,
                width: '100%'
            });
        }

        // Flatpickr for dates
        if ($('.flatpickr-date').length) {
            $('.flatpickr-date').flatpickr({
                dateFormat: 'Y-m-d',
                allowInput: true
            });
        }
    });
</script>
