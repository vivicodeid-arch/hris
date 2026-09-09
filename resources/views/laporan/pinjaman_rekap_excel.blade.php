<table style="width: 100%">
    <tr>
        <td colspan="10" style="font-weight: bold; font-size: 14px">
            LAPORAN REKAP PINJAMAN KARYAWAN
        </td>
    </tr>
    <tr>
        <td colspan="10" style="font-weight: bold; font-size: 14px">
            {{ textUpperCase($generalsetting->nama_perusahaan) }}
        </td>
    </tr>
    <tr>
        <td colspan="10" style="font-size: 12px">
            PERIODE {{ textUpperCase(getNamabulan($bulan)) }} {{ $tahun }}
        </td>
    </tr>
    <tr>
        <td colspan="10" style="font-size: 12px; font-style: italic;">
            {{ $generalsetting->alamat }}
        </td>
    </tr>
    <tr>
        <td colspan="10" style="font-size: 12px; font-style: italic;">
            {{ $generalsetting->telepon }}
        </td>
    </tr>
    <tr>
        <td colspan="10"></td>
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
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">No</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">NIK</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Nama Karyawan</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Departemen</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Cabang</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Saldo Awal</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Penambah (Pinjaman Baru)</th>
            <th colspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Pembayaran</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Saldo Akhir</th>
        </tr>
        <tr>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Potong Gaji</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Bayar Cash</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rekap as $r)
            <tr>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; mso-number-format:'\@';">'{{ $r->nik_show ?? $r->nik }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $r->nama_karyawan }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $r->nama_dept }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $r->nama_cabang }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $r->saldo_awal }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $r->penambah }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $r->potong_gaji }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $r->bayar_cash }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ $r->saldo_akhir }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="border: 1px solid #000000; text-align: right; font-weight: bold;">TOTAL</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $rekap->sum('saldo_awal') }}</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $rekap->sum('penambah') }}</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $rekap->sum('potong_gaji') }}</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $rekap->sum('bayar_cash') }}</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $rekap->sum('saldo_akhir') }}</td>
        </tr>
    </tfoot>
</table>
