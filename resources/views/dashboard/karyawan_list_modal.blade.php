<div class="p-1 mb-3">
    <div class="input-group input-group-merge">
        <span class="input-group-text"><i class="ti ti-search"></i></span>
        <input type="text" id="searchKaryawanNama" class="form-control" placeholder="Cari berdasarkan NIK atau Nama Karyawan...">
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
            <tr>
                <th class="text-white py-3 text-center" style="width: 60px;">NO.</th>
                <th class="text-white py-3 text-center" style="width: 80px;">FOTO</th>
                <th class="text-white py-3" style="width: 120px;">NIK</th>
                <th class="text-white py-3">NAMA KARYAWAN</th>
                <th class="text-white py-3">JABATAN / DEPT / CABANG</th>
                @if($status == 'h')
                    <th class="text-white py-3">JAM MASUK</th>
                    <th class="text-white py-3">JAM PULANG</th>
                @else
                    <th class="text-white py-3">KETERANGAN</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($karyawanList as $k)
                <tr>
                    <td class="py-2 text-center">{{ $loop->iteration }}</td>
                    <td class="py-2 text-center">
                        @if (!empty($k->foto) && Storage::disk('public')->exists('/karyawan/' . $k->foto))
                            <img src="{{ getfotoKaryawan($k->foto) }}" alt="Avatar"
                                class="rounded-circle"
                                style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #e9ecef;">
                        @else
                            <img src="{{ asset('assets/img/avatars/No_Image_Available.jpg') }}"
                                alt="No Image" class="rounded-circle"
                                style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #e9ecef;">
                        @endif
                    </td>
                    <td class="py-2"><span class="fw-bold">{{ $k->nik }}</span></td>
                    <td class="py-2">{{ $k->nama_karyawan }}</td>
                    <td class="py-2">
                        <div class="fw-semibold text-dark" style="font-size: 13px;">{{ $k->nama_jabatan }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">{{ $k->nama_dept }} | {{ $k->nama_cabang }}</div>
                    </td>
                    @if($status == 'h')
                        <td class="py-2">
                            @if($k->jam_in)
                                <span class="badge bg-label-success">{{ $k->jam_in }}</span>
                            @else
                                <span class="badge bg-label-secondary">-</span>
                            @endif
                            @if($k->jam_masuk)
                                <div class="text-muted mt-1" style="font-size: 0.75rem;">Jadwal: {{ $k->jam_masuk }}</div>
                            @endif
                        </td>
                        <td class="py-2">
                            @if($k->jam_out)
                                <span class="badge bg-label-danger">{{ $k->jam_out }}</span>
                            @else
                                <span class="badge bg-label-secondary">-</span>
                            @endif
                            @if($k->jam_pulang)
                                <div class="text-muted mt-1" style="font-size: 0.75rem;">Jadwal: {{ $k->jam_pulang }}</div>
                            @endif
                        </td>
                    @else
                        <td class="py-2">
                            @if($k->keterangan)
                                <span class="text-wrap" style="font-size: 13px;">{{ $k->keterangan }}</span>
                            @else
                                <span class="text-muted italic" style="font-size: 13px;">Tidak ada keterangan</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="text-muted">Tidak ada data karyawan untuk status ini.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
