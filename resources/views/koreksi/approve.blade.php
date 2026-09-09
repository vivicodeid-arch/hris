<form action="{{ route('koreksi.storeapprove', Crypt::encrypt($koreksi->kode_koreksi)) }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered mb-3">
                <tr>
                    <th class="bg-light" style="width: 40%;">Kode Koreksi</th>
                    <td>{{ $koreksi->kode_koreksi }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Karyawan</th>
                    <td>{{ $koreksi->nik }} - {{ $koreksi->nama_karyawan }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Tanggal Absen</th>
                    <td>{{ DateToIndo($koreksi->tanggal) }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Jadwal / Jam Kerja</th>
                    <td class="fw-bold text-primary">{{ $koreksi->kode_jam_kerja }} - {{ $koreksi->nama_jam_kerja }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Jam Masuk Diajukan</th>
                    <td class="text-success fw-bold">{{ $koreksi->jam_in ?? '-' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Jam Pulang Diajukan</th>
                    <td class="text-danger fw-bold">{{ $koreksi->jam_out ?? '-' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Alasan</th>
                    <td>{{ $koreksi->keterangan }}</td>
                </tr>
            </table>

            <div class="form-group mb-3">
                <label class="form-label fw-bold">Catatan Approval</label>
                <textarea name="catatan" class="form-control" rows="3" placeholder="Berikan catatan jika diperlukan..."></textarea>
            </div>

            <div class="row g-2">
                <div class="col-6">
                    <button class="btn btn-success w-100" name="approve" type="submit" value="approve">
                        <i class="ti ti-check me-1"></i> Setujui
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-danger w-100" name="tolak" type="submit" value="tolak">
                        <i class="ti ti-x me-1"></i> Tolak
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
