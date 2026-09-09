<table style="width: 100%">
    <tr>
        <td colspan="11" style="font-weight: bold; font-size: 14px">
            LAPORAN REIMBURSEMENT KARYAWAN
        </td>
    </tr>
    <tr>
        <td colspan="11" style="font-weight: bold; font-size: 14px">
            {{ textUpperCase($generalsetting->nama_perusahaan) }}
        </td>
    </tr>
    <tr>
        <td colspan="11" style="font-size: 12px">
            PERIODE {{ date('d-m-Y', strtotime($dari)) }} - {{ date('d-m-Y', strtotime($sampai)) }}
        </td>
    </tr>
    <tr>
        <td colspan="11" style="font-size: 12px; font-style: italic;">
            {{ $generalsetting->alamat }}
        </td>
    </tr>
    <tr>
        <td colspan="11" style="font-size: 12px; font-style: italic;">
            {{ $generalsetting->telepon }}
        </td>
    </tr>
    <tr>
        <td colspan="11"></td>
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
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">No Pengajuan</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">NIK</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Nama Karyawan</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Departemen</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Cabang</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Tanggal Pengajuan</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Total Nominal</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Total Disetujui</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Status</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Rincian Keterangan &amp; Item</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reimbursement as $r)
            @php
                $statusLabel = 'Pending';
                if ($r->status == 'A') $statusLabel = 'Approved';
                elseif ($r->status == 'R') $statusLabel = 'Rejected';
                elseif ($r->status == 'D') $statusLabel = 'Dibayar';
                elseif ($r->status == 'B') $statusLabel = 'Batal';

                $rincian = '';
                if (isset($details_by_id[$r->id])) {
                    $items = [];
                    foreach ($details_by_id[$r->id] as $det) {
                        $itemStr = $det->nama_jenis . ' - ' . $det->keterangan . ' (Rp ' . number_format($det->nominal, 0, ',', '.');
                        if($det->nominal_disetujui !== null) {
                            $itemStr .= ' disetujui Rp ' . number_format($det->nominal_disetujui, 0, ',', '.');
                        }
                        $itemStr .= ')';
                        $items[] = $itemStr;
                    }
                    $rincian = implode("\n", $items);
                }
            @endphp
            <tr>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle; mso-number-format:'\@';">'{{ $r->no_reimbursement }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; mso-number-format:'\@';">'{{ $r->nik_show ?? $r->nik }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $r->nama_karyawan }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $r->nama_dept }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $r->nama_cabang }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ date('d-m-Y', strtotime($r->tanggal_pengajuan)) }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $r->total_nominal }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $r->total_disetujui }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $statusLabel }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle; white-space: pre-line;">{{ $rincian }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" style="border: 1px solid #000000; text-align: right; font-weight: bold;">TOTAL</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $reimbursement->sum('total_nominal') }}</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $reimbursement->sum('total_disetujui') }}</td>
            <td colspan="2" style="border: 1px solid #000000;"></td>
        </tr>
    </tfoot>
</table>

<table>
    <tr>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="4" style="font-weight: bold; font-size: 12px;">REKAPITULASI PER JENIS REIMBURSEMENT</td>
    </tr>
</table>

<table style="width: 50%; border-collapse: collapse; border: 1px solid #000000;">
    <thead>
        <tr>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold;">No</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold;">Jenis Reimbursement</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold;">Total Nominal</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold;">Total Disetujui</th>
        </tr>
    </thead>
    <tbody>
        @php
            $rekap_no = 1;
            $rekap_total_nominal = 0;
            $rekap_total_disetujui = 0;
        @endphp
        @foreach ($rekap as $rk)
            <tr>
                <td style="border: 1px solid #000000; text-align: center;">{{ $rekap_no++ }}</td>
                <td style="border: 1px solid #000000;">{{ $rk['nama_jenis'] }}</td>
                <td style="border: 1px solid #000000; text-align: right;">{{ $rk['total_nominal'] }}</td>
                <td style="border: 1px solid #000000; text-align: right;">{{ $rk['total_disetujui'] }}</td>
            </tr>
            @php
                $rekap_total_nominal += $rk['total_nominal'];
                $rekap_total_disetujui += $rk['total_disetujui'];
            @endphp
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="border: 1px solid #000000; text-align: right; font-weight: bold;">TOTAL</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $rekap_total_nominal }}</td>
            <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $rekap_total_disetujui }}</td>
        </tr>
    </tfoot>
</table>
