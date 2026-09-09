<table style="width: 100%">
    <tr>
        <td colspan="13" style="font-weight: bold; font-size: 14px">
            LAPORAN PINJAMAN KARYAWAN
        </td>
    </tr>
    <tr>
        <td colspan="13" style="font-weight: bold; font-size: 14px">
            {{ textUpperCase($generalsetting->nama_perusahaan) }}
        </td>
    </tr>
    <tr>
        <td colspan="13" style="font-size: 12px">
            PERIODE {{ date('d-m-Y', strtotime($dari)) }} - {{ date('d-m-Y', strtotime($sampai)) }}
        </td>
    </tr>
    <tr>
        <td colspan="13" style="font-size: 12px; font-style: italic;">
            {{ $generalsetting->alamat }}
        </td>
    </tr>
    <tr>
        <td colspan="13" style="font-size: 12px; font-style: italic;">
            {{ $generalsetting->telepon }}
        </td>
    </tr>
    <tr>
        <td colspan="13"></td>
    </tr>
</table>

<table>
    <tr>
        <td style="font-weight: bold">Cabang</td>
        <td>:</td>
        <td>{{ textUpperCase($namacabang) }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold">Departemen</td>
        <td>:</td>
        <td>{{ textUpperCase($namadept) }}</td>
    </tr>
    <tr>
        <td colspan="3"></td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; border: 1px solid #000000;">
    <thead>
        <tr>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">No</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">No Pinjaman</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">NIK</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Nama Karyawan</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Departemen</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Cabang</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Tanggal Pinjaman</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Jumlah Pinjaman</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Tenor (Bulan)</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Cicilan Per Bulan</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Total Dibayar</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Sisa Pinjaman</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Status</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pinjaman as $p)
            @php
                $statusLabel = 'Aktif';
                if ($p->status == 'L') $statusLabel = 'Lunas';
                elseif ($p->status == 'B') $statusLabel = 'Batal';
            @endphp
            <tr>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle; mso-number-format:'\@';">'{{ $p->no_pinjaman }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; mso-number-format:'\@';">'{{ $p->nik_show ?? $p->nik }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $p->nama_karyawan }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $p->nama_dept }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $p->nama_cabang }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ date('d-m-Y', strtotime($p->tanggal_pinjaman)) }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $p->jumlah_pinjaman }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $p->jumlah_cicilan }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $p->jumlah_per_cicilan }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $p->total_dibayar }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $p->sisa_pinjaman }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $statusLabel }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $p->keterangan ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" style="border: 1px solid #000000; text-align: right; font-weight: bold;">TOTAL</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $pinjaman->sum('jumlah_pinjaman') }}</td>
            <td colspan="2" style="border: 1px solid #000000;"></td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $pinjaman->sum('total_dibayar') }}</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $pinjaman->sum('sisa_pinjaman') }}</td>
            <td colspan="2" style="border: 1px solid #000000;"></td>
        </tr>
    </tfoot>
</table>
