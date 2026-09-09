<table style="width: 100%">
    <tr>
        <td colspan="{{ $jmlhari + 3 }}" style="font-weight: bold; font-size: 14px">
            LAPORAN JADWAL KARYAWAN
        </td>
    </tr>
    <tr>
        <td colspan="{{ $jmlhari + 3 }}" style="font-weight: bold; font-size: 14px">
            {{ textUpperCase($generalsetting->nama_perusahaan) }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $jmlhari + 3 }}" style="font-size: 12px">
            PERIODE {{ date('d-m-Y', strtotime($periode_dari)) }} - {{ date('d-m-Y', strtotime($periode_sampai)) }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $jmlhari + 3 }}" style="font-size: 12px; font-style: italic;">
            {{ $generalsetting->alamat }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $jmlhari + 3 }}" style="font-size: 12px; font-style: italic;">
            {{ $generalsetting->telepon }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $jmlhari + 3 }}"></td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; border: 1px solid #000000;">
    <thead>
        <tr>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">No</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">NIK</th>
            <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Nama Karyawan</th>
            <th colspan="{{ $jmlhari }}" style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle;">Tanggal</th>
        </tr>
        <tr>
            @php
                $tanggal_loop = $periode_dari;
            @endphp
            @while (strtotime($tanggal_loop) <= strtotime($periode_sampai))
                <th style="border: 1px solid #000000; background-color: #024a75; color: white; text-align: center; font-weight: bold; vertical-align: middle; width: 120px">
                    {{ date('d', strtotime($tanggal_loop)) }} - {{ getHari(date('Y-m-d', strtotime($tanggal_loop))) }}
                </th>
                @php
                    $tanggal_loop = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_loop)));
                @endphp
            @endwhile
        </tr>
    </thead>
    <tbody>
        @foreach ($karyawan as $d)
            @php
                $tanggal_loop = $periode_dari;
                $mapJadwalByDate = $jadwal_bydate[$d->nik] ?? [];
                $mapJadwalGrupByDate = $jadwal_grup_bydate[$d->nik] ?? [];
                $mapJadwalByDay = $jadwal_byday[$d->nik] ?? [];
            @endphp
            <tr>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; mso-number-format:'\@';">'{{ $d->nik_show ?? $d->nik }}</td>
                <td style="border: 1px solid #000000; vertical-align: middle; text-align: left;">{{ $d->nama_karyawan }}</td>
                
                @while (strtotime($tanggal_loop) <= strtotime($periode_sampai))
                    @php
                        $search = [
                            'nik' => $d->nik,
                            'tanggal' => $tanggal_loop,
                        ];
                        $ceklibur = ceklibur($datalibur, $search);
                        $nama_hari = getHari($tanggal_loop);
                        
                        $jadwal_info = null;
                        
                        // 1) By-Date Employee
                        if (isset($mapJadwalByDate[$tanggal_loop])) {
                            $jadwal_info = $mapJadwalByDate[$tanggal_loop];
                        }
                        // 2) By-Date Group
                        elseif (isset($mapJadwalGrupByDate[$tanggal_loop])) {
                            $jadwal_info = $mapJadwalGrupByDate[$tanggal_loop];
                        }
                        // 3) By-Day Employee
                        elseif (isset($mapJadwalByDay[$nama_hari])) {
                            $jadwal_info = $mapJadwalByDay[$nama_hari];
                        }
                        // 4) By-Day Dept/Branch
                        else {
                            $keyDeptCabang = $d->kode_dept . '|' . $d->kode_cabang;
                            $mapDept = $jadwal_bydept[$keyDeptCabang] ?? [];
                            if (isset($mapDept[$nama_hari])) {
                                $jadwal_info = $mapDept[$nama_hari];
                            }
                            // 5) Global Schedule
                            elseif (isset($jadwal_global[$nama_hari])) {
                                $jadwal_info = $jadwal_global[$nama_hari];
                            }
                        }
                        
                        $bgcolor = '';
                        $content = '';
                        
                        if (!empty($ceklibur)) {
                            $bgcolor = '#008000'; // Green hex for Excel
                            $ket_libur = !empty($ceklibur[0]['keterangan']) ? "\n" . $ceklibur[0]['keterangan'] : '';
                            $content = 'LIBUR' . $ket_libur;
                        } else {
                            if ($nama_hari == 'Minggu') {
                                $bgcolor = '#ffa500'; // Orange hex for Excel
                            }
                            
                            if ($jadwal_info) {
                                $content = $jadwal_info['nama_jam_kerja'] . "\n" . 
                                           date('H:i', strtotime($jadwal_info['jam_masuk'])) . '-' . 
                                           date('H:i', strtotime($jadwal_info['jam_pulang']));
                                if (empty($bgcolor) && !empty($jadwal_info['color'])) {
                                    $bgcolor = $jadwal_info['color'];
                                }
                            }
                        }
                    @endphp
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; @if(!empty($bgcolor)) background-color: {{ $bgcolor }}; color: #ffffff; @endif">
                        {{ $content }}
                    </td>
                    @php
                        $tanggal_loop = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_loop)));
                    @endphp
                @endwhile
            </tr>
        @endforeach
    </tbody>
</table>
