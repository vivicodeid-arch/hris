<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead style="background-color: var(--theme-color-1) !important; color: white !important;">
            <tr>
                <th class="text-white py-3" style="width: 60px;">NO.</th>
                <th class="text-white py-3">TANGGAL</th>
                <th class="text-white py-3">NAMA PELATIHAN</th>
                <th class="text-white py-3">PENYELENGGARA</th>
                <th class="text-white py-3 text-center" style="width: 120px;">#</th>
            </tr>
        </thead>
        <tbody>
            @if ($pelatihan->isEmpty())
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">Data tidak ditemukan.</td>
                </tr>
            @else
                @foreach ($pelatihan as $d)
                    <tr>
                        <td class="py-2">{{ $loop->iteration }}</td>
                        <td class="py-2 text-dark">{{ date('d-m-Y', strtotime($d->tanggal_pelatihan)) }}</td>
                        <td class="py-2">{{ $d->nama_pelatihan }}</td>
                        <td class="py-2">{{ $d->penyelenggara }}</td>
                        <td class="py-2 text-center">
                            <div class="d-inline-flex border rounded overflow-hidden shadow-xs">
                                @if ($d->foto)
                                    <a href="{{ asset('storage/pelatihan/' . $d->foto) }}" target="_blank"
                                        class="btn btn-sm px-2 py-1 border-0 rounded-0" title="Lihat Sertifikat"
                                        style="background: #f8f9fa;">
                                        <i class="ti ti-file-text fs-6 text-info"></i>
                                    </a>
                                @else
                                    <button class="btn btn-sm px-2 py-1 border-0 rounded-0 disabled" style="background: #f8f9fa;">
                                        <i class="ti ti-file-off fs-6 text-muted"></i>
                                    </button>
                                @endif

                                <form action="{{ route('pelatihan.delete', Crypt::encrypt($d->id)) }}" method="POST"
                                    class="deleteform m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm px-2 py-1 border-0 rounded-0 border-start"
                                        title="Hapus" style="background: #f8f9fa;">
                                        <i class="ti ti-trash fs-6 text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
