<div class="row">
    <div class="col">
        <table class="table">
            <tr>
                <th>Kode Koreksi</th>
                <td class="text-end">{{ $koreksi->kode_koreksi }}</td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td class="text-end">{{ DateToIndo($koreksi->tanggal) }}</td>
            </tr>
            <tr>
                <th>Jadwal / Jam Kerja</th>
                <td class="text-end">{{ $koreksi->kode_jam_kerja }} - {{ $koreksi->nama_jam_kerja }}</td>
            </tr>
            <tr>
                <th>NIK</th>
                <td class="text-end">{{ $koreksi->nik }}</td>
            </tr>
            <tr>
                <th>Nama Karyawan</th>
                <td class="text-end">{{ $koreksi->nama_karyawan }}</td>
            </tr>
            <tr>
                <th>Jam Masuk</th>
                <td class="text-end">{{ $koreksi->jam_in ?? '-' }}</td>
            </tr>
            <tr>
                <th>Jam Pulang</th>
                <td class="text-end">{{ $koreksi->jam_out ?? '-' }}</td>
            </tr>
            <tr>
                <th>Alasan</th>
                <td class="text-end">{{ $koreksi->keterangan }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td class="text-end">
                    @if ($koreksi->status == '0')
                        <span class="badge bg-warning">Pending</span>
                    @elseif($koreksi->status == '1')
                        <span class="badge bg-success">Disetujui</span>
                    @else
                        <span class="badge bg-danger">Ditolak</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
