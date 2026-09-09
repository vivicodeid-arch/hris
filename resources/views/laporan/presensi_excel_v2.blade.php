<table class="datatable3" style="width: 100%; border-collapse: collapse; border: 1px solid #000000">
    <thead>
        <tr>
            <th colspan="{{ 3 + 5 + ($jmlhari * 8) + 9 }}" style="font-weight: bold; font-size: 14px">LAPORAN PRESENSI (FORMAT 2)</th>
        </tr>
        <tr>
            <th colspan="{{ 3 + 5 + ($jmlhari * 8) + 9 }}" style="font-weight: bold; font-size: 14px">{{ $generalsetting->nama_perusahaan }}</th>
        </tr>
        <tr>
            <th colspan="{{ 3 + 5 + ($jmlhari * 8) + 9 }}" style="font-size: 12px">PERIODE {{ date('d-m-Y', strtotime($periode_dari)) }} - {{ date('d-m-Y', strtotime($periode_sampai)) }}</th>
        </tr>
        <tr>
            <th colspan="{{ 3 + 5 + ($jmlhari * 8) + 9 }}" style="font-size: 12px; font-style: italic;">{{ $generalsetting->alamat }}</th>
        </tr>
        <tr>
            <th colspan="{{ 3 + 5 + ($jmlhari * 8) + 9 }}" style="font-size: 12px; font-style: italic;">{{ $generalsetting->telepon }}</th>
        </tr>
        <tr>
            <th colspan="{{ 3 + 5 + ($jmlhari * 8) + 9 }}"></th>
        </tr>
        <tr>
            <th rowspan="4" style="border: 1px solid #000000; background-color: #024a75; color: white;">No</th>
            <th rowspan="4" style="border: 1px solid #000000; background-color: #024a75; color: white;">Nik</th>
            <th rowspan="4" style="border: 1px solid #000000; background-color: #024a75; color: white;">Nama Karyawan</th>
            <th rowspan="4" style="border: 1px solid #000000; background-color: #024a75; color: white;">Jabatan</th>
            <th rowspan="4" style="border: 1px solid #000000; background-color: #024a75; color: white;">Dept</th>
            <th colspan="{{ $jmlhari * 8 }}" style="border: 1px solid #000000; background-color: #024a75; color: white;">Tanggal</th>
            <th rowspan="4" style="border: 1px solid #000000; background-color: #024a75; color: white;">Denda (T)</th>
            <th rowspan="4" style="border: 1px solid #000000; background-color: #024a75; color: white;">Pot. Jam (T)</th>
            <th rowspan="4" style="border: 1px solid #000000; background-color: #024a75; color: white;">Lembur (T)</th>
            <th colspan="9" style="border: 1px solid #000000; background-color: #024a75; color: white;">Rekap</th>
        </tr>
        <tr>
            @php
                $tanggal_presensi = $periode_dari;
            @endphp
            @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                <th colspan="8" style="border: 1px solid #000000; background-color: #024a75; color: white;">{{ getHari(date('Y-m-d', strtotime($tanggal_presensi))) }}</th>
                @php
                    $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                @endphp
            @endwhile
            <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white;">Hadir</th>
            <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white;">Izin</th>
            <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white;">Sakit</th>
            <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white;">Alfa</th>
            <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white;">Libur</th>
            <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white;">Terlambat</th>
            <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white;">T.S.M</th>
            <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white;">T.S.P</th>
            <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white;">P.C</th>
        </tr>
        <tr>
            @php $tanggal_presensi = $periode_dari; @endphp
            @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                <th colspan="8" style="border: 1px solid #000000; background-color: #024a75; color: white;">{{ date('d', strtotime($tanggal_presensi)) }}</th>
                @php
                    $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                @endphp
            @endwhile
        </tr>
        <tr>
            @php $tanggal_presensi = $periode_dari; @endphp
            @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                <th style="border: 1px solid #000000; background-color: #056191; color: white;">Jadwal</th>
                <th style="border: 1px solid #000000; background-color: #056191; color: white;">In</th>
                <th style="border: 1px solid #000000; background-color: #056191; color: white;">Out</th>
                <th style="border: 1px solid #000000; background-color: #056191; color: white;">Ist-O</th>
                <th style="border: 1px solid #000000; background-color: #056191; color: white;">Ist-I</th>
                <th style="border: 1px solid #000000; background-color: #0b99b3; color: white;">Lbr</th>
                <th style="border: 1px solid #000000; background-color: #c0392b; color: white;">PJ</th>
                <th style="border: 1px solid #000000; background-color: #c0392b; color: white;">Dnd</th>
                @php $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi))); @endphp
            @endwhile
        </tr>
    </thead>
    <tbody>
        @foreach ($laporan_presensi as $d)
            @php
                $tanggal_presensi = $periode_dari;
                $mapJadwalByDate = $jadwal_bydate[$d['nik']] ?? [];
                $mapJadwalGrupByDate = $jadwal_grup_bydate[$d['nik']] ?? [];
                $mapJadwalByDay = $jadwal_byday[$d['nik']] ?? [];
            @endphp
            <tr>
                <td style="border: 1px solid #000000; text-align:center;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid #000000; text-align:center;">'{{ $d['nik_show'] ?? $d['nik'] }}</td>
                <td style="border: 1px solid #000000;">{{ $d['nama_karyawan'] }}</td>
                <td style="border: 1px solid #000000;">{{ $d['nama_jabatan'] }}</td>
                <td style="border: 1px solid #000000; text-align: center">{{ $d['kode_dept'] }}</td>
                
                @php
                    $total_denda = 0; $total_potongan_jam = 0; $total_jam_lembur = 0;
                    $jml_hadir = 0; $jml_sakit = 0; $jml_izin = 0; $jml_cuti = 0;
                    $jml_libur = 0; $jml_alfa = 0; $jml_terlambat = 0;
                    $jml_pulangcepat = 0; $jml_tidakscanmasuk = 0; $jml_tidakscanpulang = 0;
                @endphp

                @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                    @php
                        $bgcolor = ''; $textcolor = '';
                        $denda = 0; $potongan_jam = 0;
                        $is_libur_nasional = isset($libur_nasional_dates[$tanggal_presensi]);
                        if ($is_libur_nasional) {
                            $is_libur = true;
                        } else {
                            $has_schedule = false;
                            $nama_hari = getHari($tanggal_presensi);
                            if (isset($mapJadwalByDate[$tanggal_presensi])) $has_schedule = true;
                            elseif (isset($mapJadwalGrupByDate[$tanggal_presensi])) $has_schedule = true;
                            elseif (isset($mapJadwalByDay[$nama_hari])) $has_schedule = true;
                            else {
                                $keyDC = $d['kode_dept'] . '|' . $d['kode_cabang'];
                                $mapD = $jadwal_bydept[$keyDC] ?? [];
                                if (isset($mapD[$nama_hari])) $has_schedule = true;
                                elseif (isset($jadwal_global[$nama_hari])) $has_schedule = true;
                            }
                            $is_libur = !$has_schedule;
                        }
                        $ceklibur = $datalibur_indexed[$d['nik'] . '|' . $tanggal_presensi] ?? ($datalibur_by_tanggal[$tanggal_presensi] ?? []);
                        $lembur_key = $d['nik'] . '|' . $tanggal_presensi;
                        $ceklembur = $datalembur_indexed[$lembur_key] ?? [];
                        $snapshot_lembur = isset($d[$tanggal_presensi]) && $d[$tanggal_presensi]['jam_lembur_aktual'] !== null;

                        if ($snapshot_lembur) {
                            $jam_netto_harian = $d[$tanggal_presensi]['is_lembur_khusus']
                                ? $d[$tanggal_presensi]['jam_lembur_aktual']
                                : $d[$tanggal_presensi]['jam_lembur_netto'];
                        } else {
                            $lembur_aktual = !empty($ceklembur) ? hitungLembur($ceklembur) : 0;
                            $tipe_hari = $is_libur ? 2 : 1;
                            $jam_netto_harian = $lembur_aktual > 0 ? hitungJamNetto($lembur_aktual, $tipe_hari) : 0;
                        }

                        $col_data = ['jadwal' => '-', 'in' => '-', 'out' => '-', 'ist_o' => '-', 'ist_i' => '-', 'lbr' => '-', 'pj' => '-', 'dnd' => '-'];
                        $bgcolor = ''; $textcolor = '';
                    @endphp

                    @if (isset($d[$tanggal_presensi]))
                        @php $row_p = $d[$tanggal_presensi]; @endphp
                        @php
                            $col_data['jadwal'] = $row_p['nama_jam_kerja'] . ' (' . date('H:i', strtotime($row_p['jam_masuk'])) . '-' . date('H:i', strtotime($row_p['jam_pulang'])) . ')';
                        @endphp
                        @if ($row_p['status'] == 'h')
                            @php
                                $jml_hadir++;
                                $jam_masuk_ref = $tanggal_presensi . ' ' . $row_p['jam_masuk'];
                                $col_data['in'] = !empty($row_p['jam_in']) ? date('H:i', strtotime($row_p['jam_in'])) : 'X';
                                $col_data['out'] = !empty($row_p['jam_out']) ? date('H:i', strtotime($row_p['jam_out'])) : 'X';
                                
                                $terlambat = hitungjamterlambat($row_p['jam_in'], $jam_masuk_ref);
                                if ($terlambat && $terlambat['menitterlambat'] > 0) $jml_terlambat++;

                                $denda_db = $row_p['denda'] ?? null;
                                if ($denda_db !== null) { $denda = $denda_db; } 
                                else if ($terlambat) { $denda = $terlambat['desimal_terlambat'] < 1 ? hitungdenda($denda_list, $terlambat['menitterlambat']) : 0; }

                                $pc = hitungpulangcepat($tanggal_presensi, $row_p['jam_out'], $row_p['jam_pulang'], $row_p['istirahat'], $row_p['jam_awal_istirahat'], $row_p['jam_akhir_istirahat'], $row_p['lintashari']);
                                if ($pc) $jml_pulangcepat++;

                                $ist_pot = hitungPotonganIstirahat($row_p['istirahat_out'], $row_p['istirahat_in'], $row_p['jam_awal_istirahat'], $row_p['jam_akhir_istirahat']);
                                $no_abs_pot = (empty($row_p['jam_out']) || empty($row_p['jam_in'])) ? $row_p['total_jam'] : 0;
                                $pj_ist_stat = $row_p['status_potongan_istirahat'] ?? $generalsetting->potongan_istirahat;
                                
                                $potongan_jam = $no_abs_pot == 0 ? ($pc + ($terlambat && $terlambat['desimal_terlambat'] >= 1 ? $terlambat['desimal_terlambat'] : 0) + ($pj_ist_stat == 1 ? $ist_pot : 0)) : $no_abs_pot;
                                if (($row_p['status_potongan'] ?? $generalsetting->status_potongan_jam) == 0) $potongan_jam = 0;

                                $col_data['ist_o'] = !empty($row_p['istirahat_out']) ? date('H:i', strtotime($row_p['istirahat_out'])) : '-';
                                $col_data['ist_i'] = !empty($row_p['istirahat_in']) ? date('H:i', strtotime($row_p['istirahat_in'])) : '-';
                                
                                $col_data['lbr'] = $jam_netto_harian > 0 ? formatAngkaDesimal($jam_netto_harian) : '-';
                                $col_data['pj'] = $potongan_jam > 0 ? formatAngkaDesimal($potongan_jam) : '-';
                                $col_data['dnd'] = $denda > 0 ? formatAngka($denda) : '-';
                                if (empty($row_p['jam_in'])) $jml_tidakscanmasuk++;
                                if (empty($row_p['jam_out'])) $jml_tidakscanpulang++;
                            @endphp
                        @else
                            @php
                                $status_map = ['i' => ['IZIN', '#dea51f', 'jml_izin'], 's' => ['SAKIT', '#c8075b', 'jml_sakit'], 'c' => ['CUTI', '#0164b5', 'jml_cuti'], 'a' => ['ALPA', 'red', 'jml_alfa']];
                                $st = $status_map[$row_p['status']];
                                $bgcolor = $st[1]; $textcolor = 'white'; ${$st[2]}++;
                                $col_data['in'] = $st[0];
                                if ($row_p['status'] == 'a' || $row_p['status'] == 'i') {
                                   $potongan_jam = ($row_p['status_potongan'] ?? $generalsetting->status_potongan_jam) == 1 ? $row_p['total_jam'] : 0;
                                   $col_data['pj'] = $potongan_jam > 0 ? formatAngkaDesimal($potongan_jam) : '-';
                                }
                                $denda = $row_p['denda'] ?? 0;
                                $col_data['dnd'] = $denda > 0 ? formatAngka($denda) : '-';
                            @endphp
                        @endif
                    @else
                        @php
                            $is_future = strtotime($tanggal_presensi) > strtotime(date('Y-m-d'));
                            if (!empty($ceklibur)) { 
                                $bgcolor = '#006400'; $textcolor = 'white'; $jml_libur++; $col_data['in'] = 'LIBUR'; 
                            }
                            else {
                                $fallback = $mapJadwalByDate[$tanggal_presensi] ?? ($mapJadwalGrupByDate[$tanggal_presensi] ?? ($mapJadwalByDay[$nama_hari] ?? ($jadwal_bydept[$d['kode_dept'].'|'.$d['kode_cabang']][$nama_hari] ?? ($jadwal_global[$nama_hari] ?? null))));
                                if (is_array($fallback)) {
                                    $col_data['jadwal'] = $fallback['nama_jam_kerja'] . ' (' . date('H:i', strtotime($fallback['jam_masuk'])) . '-' . date('H:i', strtotime($fallback['jam_pulang'])) . ')';
                                    $tJam = $fallback['total_jam'];
                                } else {
                                    $tJam = null;
                                }

                                if ($tJam !== null && !$is_future) {
                                    $jml_alfa++; $bgcolor = 'red'; $textcolor = 'white'; $col_data['in'] = 'ALPA';
                                    $potongan_jam = $generalsetting->status_potongan_jam == 1 ? $tJam : 0;
                                    $col_data['pj'] = $potongan_jam > 0 ? formatAngkaDesimal($potongan_jam) : '-';
                                }
                            }
                            if ($is_libur && empty($ceklibur)) { $bgcolor = 'orange'; $textcolor = 'white'; $col_data['in'] = 'LB-K'; }
                        @endphp
                    @endif

                    @php 
                        $total_denda += $denda; $total_potongan_jam += $potongan_jam; $total_jam_lembur += $jam_netto_harian;
                        $bgcolor = !empty($ceklibur) ? '#006400' : ($is_libur ? 'orange' : $bgcolor);
                        if (!empty($ceklibur) || $is_libur) $textcolor = 'white';
                        $cell_style = "background-color:$bgcolor; color:$textcolor; border: 1px solid #000000;";
                    @endphp
                    
                    <td style="{{ $cell_style }} text-align:center;">{{ $col_data['jadwal'] }}</td>
                    <td style="{{ $cell_style }} text-align:center;">{{ $col_data['in'] }}</td>
                    <td style="{{ $cell_style }} text-align:center;">{{ $col_data['out'] }}</td>
                    <td style="{{ $cell_style }} text-align:center;">{{ $col_data['ist_o'] }}</td>
                    <td style="{{ $cell_style }} text-align:center;">{{ $col_data['ist_i'] }}</td>
                    <td style="{{ $cell_style }} text-align:center;">{{ $col_data['lbr'] }}</td>
                    <td style="{{ $cell_style }} text-align:center;">{{ $col_data['pj'] }}</td>
                    <td style="{{ $cell_style }} text-align:right;">{{ $col_data['dnd'] }}</td>

                    @php $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi))); @endphp
                @endwhile

                <td style="border: 1px solid #000000; text-align: right">{{ formatAngka($total_denda) }}</td>
                <td style="border: 1px solid #000000; text-align: center">{{ formatAngkaDesimal($total_potongan_jam) }}</td>
                <td style="border: 1px solid #000000; text-align:center">{{ formatAngkaDesimal($total_jam_lembur) }}</td>
                <td style="border: 1px solid #000000; text-align:center">{{ $jml_hadir }}</td>
                <td style="border: 1px solid #000000; text-align:center">{{ $jml_izin }}</td>
                <td style="border: 1px solid #000000; text-align:center">{{ $jml_sakit }}</td>
                <td style="border: 1px solid #000000; text-align:center">{{ $jml_alfa }}</td>
                <td style="border: 1px solid #000000; text-align:center">{{ $jml_libur }}</td>
                <td style="border: 1px solid #000000; text-align:center">{{ $jml_terlambat }}</td>
                <td style="border: 1px solid #000000; text-align:center">{{ $jml_tidakscanmasuk }}</td>
                <td style="border: 1px solid #000000; text-align:center">{{ $jml_tidakscanpulang }}</td>
                <td style="border: 1px solid #000000; text-align:center">{{ $jml_pulangcepat }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
