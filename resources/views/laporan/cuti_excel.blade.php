<table style="width: 100%">
    <tr>
        <td colspan="17" style="font-weight: bold; font-size: 14px">
            LAPORAN CUTI KARYAWAN
        </td>
    </tr>
    <tr>
        <td colspan="17" style="font-weight: bold; font-size: 14px">
            {{ textUpperCase($generalsetting->nama_perusahaan) }}
        </td>
    </tr>
    <tr>
        <td colspan="17" style="font-size: 12px">
            PERIODE TAHUN {{ $tahun }}
        </td>
    </tr>
    <tr>
        <td colspan="17" style="font-size: 12px; font-style: italic;">
            {{ $generalsetting->alamat }}
        </td>
    </tr>
    <tr>
        <td colspan="17" style="font-size: 12px; font-style: italic;">
            {{ $generalsetting->telepon }}
        </td>
    </tr>
    <tr>
        <td colspan="17"></td>
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
        <td style="font-weight: bold">Jenis Cuti</td>
        <td>:</td>
        <td>{{ textUpperCase($jenis_cuti) }}</td>
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
            <th colspan="12" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Bulan</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Total Ambil</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Jatah Cuti</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Sisa Cuti</th>
        </tr>
        <tr>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Jan</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Feb</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Mar</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Apr</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Mei</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Jun</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Jul</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Agu</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Sep</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Okt</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Nov</th>
            <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Des</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rekap_cuti as $nik => $d)
            <tr>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; mso-number-format:'\@';">'{{ $d['nik_show'] ?? $nik }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle;">{{ $d['nama'] }}</td>
                @for ($i = 1; $i <= 12; $i++)
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; {{ $d['bulan'][$i] > 0 ? 'background-color: #f7d7da; font-weight: bold;' : '' }}">
                        {{ $d['bulan'][$i] > 0 ? $d['bulan'][$i] : '' }}
                    </td>
                @endfor
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold">{{ $d['total_ambil'] }}</td>
                
                <!-- Logic for Sisa Cuti -->
                @php
                    $jatah = '-';
                    $sisa = '-';
                    if (!empty($master_cuti)) {
                        $jatah = $master_cuti->jumlah_hari;
                        if ($master_cuti->kode_cuti == 'C01') {
                                 // Annual Leave logic often calculated per year.
                            $sisa = $master_cuti->jumlah_hari - $d['total_ambil'];
                        } else {
                                 // Other leaves might be per event, so "Sisa" per year might just be max - used
                            $sisa = $master_cuti->jumlah_hari - $d['total_ambil'];
                        }
                        
                        // Handling negative sisa if overrides?
                        if($sisa < 0) $sisa = 0; 
                    }
                @endphp

                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $jatah }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $sisa }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table width="100%" style="margin-top: 30px;">
    <tr>
        <td colspan="3" style="text-align: center; vertical-align: bottom" height="80px">
            <u>Manager HRD</u><br>
            <i><b>HRD Manager</b></i>
        </td>
        <td colspan="11"></td>
        <td colspan="3" style="text-align: center; vertical-align: bottom">
            <u>Direktur</u><br>
            <i><b>Direktur</b></i>
        </td>
    </tr>
</table>
