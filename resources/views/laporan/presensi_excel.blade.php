
    <table class="datatable3" style="width: 100%; border-collapse: collapse; border: 1px solid #000000">
        <thead>
            <tr>
                <td colspan="{{ 17 + $jmlhari }}" style="font-weight: bold; font-size: 14px">LAPORAN PRESENSI</td>
            </tr>
            <tr>
                <td colspan="{{ 17 + $jmlhari }}" style="font-weight: bold; font-size: 14px">{{ $generalsetting->nama_perusahaan }}</td>
            </tr>
            <tr>
                <td colspan="{{ 17 + $jmlhari }}" style="font-size: 12px">PERIODE {{ date('d-m-Y', strtotime($periode_dari)) }} - {{ date('d-m-Y', strtotime($periode_sampai)) }}</td>
            </tr>
            <tr>
                <td colspan="{{ 17 + $jmlhari }}" style="font-size: 12px; font-style: italic;">{{ $generalsetting->alamat }}</td>
            </tr>
            <tr>
                <td colspan="{{ 17 + $jmlhari }}" style="font-size: 12px; font-style: italic;">{{ $generalsetting->telepon }}</td>
            </tr>
            <tr>
                <td colspan="{{ 16 + $jmlhari }}"></td>
            </tr>
            <tr>
                <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">No</th>
                <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Nik</th>
                <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Nama Karyawan</th>
                <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Jabatan</th>
                <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Dept</th>
                <th colspan="{{ $jmlhari }}" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Tanggal</th>
                <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Denda</th>
                <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Pot. Jam</th>
                <th rowspan="3" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Lembur</th>
                <th colspan="9" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Rekap</th>
            </tr>
            <tr>
                @php
                    $tanggal_presensi = $periode_dari;
                @endphp
                @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                    <th style="width: 100px; border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">{{ getHari(date('Y-m-d', strtotime($tanggal_presensi))) }}</th>
                    @php
                        $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                    @endphp
                @endwhile
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Hadir</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Izin</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Sakit</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Alfa</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Libur</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Terlambat</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">T.S.M</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">T.S.P</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">P.C</th>
            </tr>
            <tr>
                @php
                    $tanggal_presensi = $periode_dari;
                @endphp
                @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                    <th style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">{{ date('d', strtotime($tanggal_presensi)) }}</th>
                    @php
                        $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                    @endphp
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
                    $lemburKhusus = $lembur_khusus_map[$d['nik']] ?? null;
                @endphp
                <tr>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ $loop->iteration }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">'{{ $d['nik_show'] ?? $d['nik'] }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">{{ $d['nama_karyawan'] }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">{{ $d['nama_jabatan'] }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $d['kode_dept'] }}</td>
                    @php
                        $total_denda = 0; $total_potongan_jam = 0; $total_jam_lembur = 0;
                        $jml_hadir = 0; $jml_sakit = 0; $jml_izin = 0; $jml_cuti = 0;
                        $jml_libur = 0; $jml_alfa = 0; $jml_terlambat = 0;
                        $jml_pulangcepat = 0; $jml_tidakscanmasuk = 0; $jml_tidakscanpulang = 0;
                    @endphp
                    @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                        @php
                            $bgcolor = ''; $textcolor = ''; $denda = 0; $potongan_jam = 0;
                            $libur_key = $d['nik'] . '|' . $tanggal_presensi;
                            $ceklibur = $datalibur_indexed[$libur_key] ?? ($datalibur_by_tanggal[$tanggal_presensi] ?? []);

                            if (!empty($ceklibur)) {
                                $is_libur = true;
                            } else {
                                $has_schedule = false;
                                $nama_hari_check = getHari($tanggal_presensi);
                                if (isset($mapJadwalByDate[$tanggal_presensi])) $has_schedule = true;
                                elseif (isset($mapJadwalGrupByDate[$tanggal_presensi])) $has_schedule = true;
                                elseif (isset($mapJadwalByDay[$nama_hari_check])) $has_schedule = true;
                                else {
                                    $keyDC = $d['kode_dept'] . '|' . $d['kode_cabang'];
                                    $mapD = $jadwal_bydept[$keyDC] ?? [];
                                    if (isset($mapD[$nama_hari_check])) $has_schedule = true;
                                    elseif (isset($jadwal_global[$nama_hari_check])) $has_schedule = true;
                                }
                                $is_libur = !$has_schedule;
                            }
                            $tipe_hari = $is_libur ? 2 : 1;

                            $lembur_key = $d['nik'] . '|' . $tanggal_presensi;
                            $ceklembur_data = $datalembur_indexed[$lembur_key] ?? [];
                            $snapshot_lembur = isset($d[$tanggal_presensi]) && $d[$tanggal_presensi]['jam_lembur_aktual'] !== null;

                            if ($snapshot_lembur) {
                                $jml_jam_lembur = $d[$tanggal_presensi]['is_lembur_khusus']
                                    ? $d[$tanggal_presensi]['jam_lembur_aktual']
                                    : $d[$tanggal_presensi]['jam_lembur_netto'];
                            } else {
                                $lembur_aktual = hitungLembur($ceklembur_data);
                                $jam_netto_harian = $lembur_aktual > 0 ? hitungJamNetto($lembur_aktual, $tipe_hari) : 0;
                                $jml_jam_lembur = $lemburKhusus ? $lembur_aktual : $jam_netto_harian;
                            }

                            $ket = "";
                        @endphp
                        @if (isset($d[$tanggal_presensi]))
                            @if ($d[$tanggal_presensi]['status'] == 'h')
                                @php
                                    $jml_hadir++;
                                    $jam_masuk = $tanggal_presensi . ' ' . $d[$tanggal_presensi]['jam_masuk'];
                                    $jam_in = !empty($d[$tanggal_presensi]['jam_in']) ? date('H:i', strtotime($d[$tanggal_presensi]['jam_in'])) : 'X';
                                    $jam_out = !empty($d[$tanggal_presensi]['jam_out']) ? date('H:i', strtotime($d[$tanggal_presensi]['jam_out'])) : 'X';
                                    
                                    $terlambat = hitungjamterlambat($d[$tanggal_presensi]['jam_in'], $jam_masuk);
                                    if ($terlambat && $terlambat['menitterlambat'] > 0) $jml_terlambat++;

                                    $denda_db = $d[$tanggal_presensi]['denda'] ?? null;
                                    if ($denda_db !== null) { $denda = $denda_db; } 
                                    else if ($terlambat) { $denda = $terlambat['desimal_terlambat'] < 1 ? hitungdenda($denda_list, $terlambat['menitterlambat']) : 0; }

                                    $pc = hitungpulangcepat($tanggal_presensi, $d[$tanggal_presensi]['jam_out'], $d[$tanggal_presensi]['jam_pulang'], $d[$tanggal_presensi]['istirahat'], $d[$tanggal_presensi]['jam_awal_istirahat'], $d[$tanggal_presensi]['jam_akhir_istirahat'], $d[$tanggal_presensi]['lintashari']);
                                    if ($pc) $jml_pulangcepat++;

                                    $ist_pot = hitungPotonganIstirahat($d[$tanggal_presensi]['istirahat_out'], $d[$tanggal_presensi]['istirahat_in'], $d[$tanggal_presensi]['jam_awal_istirahat'], $d[$tanggal_presensi]['jam_akhir_istirahat']);
                                    $no_abs_pot = (empty($d[$tanggal_presensi]['jam_out']) || empty($d[$tanggal_presensi]['jam_in'])) ? $d[$tanggal_presensi]['total_jam'] : 0;
                                    $pj_ist_stat = $d[$tanggal_presensi]['status_potongan_istirahat'] ?? $generalsetting->potongan_istirahat;
                                    
                                    $potongan_jam = $no_abs_pot == 0 ? ($pc + ($terlambat && $terlambat['desimal_terlambat'] >= 1 ? $terlambat['desimal_terlambat'] : 0) + ($pj_ist_stat == 1 ? $ist_pot : 0)) : $no_abs_pot;
                                    if (($d[$tanggal_presensi]['status_potongan'] ?? $generalsetting->status_potongan_jam) == 0) $potongan_jam = 0;

                                    if (empty($d[$tanggal_presensi]['jam_in'])) $jml_tidakscanmasuk++;
                                    if (empty($d[$tanggal_presensi]['jam_out'])) $jml_tidakscanpulang++;

                                    $ket = "<b>" . e($d[$tanggal_presensi]['nama_jam_kerja']) . "</b><br/>" .
                                           "<span style='color:blue'>" . date('H:i', strtotime($d[$tanggal_presensi]['jam_masuk'])) . "-" . date('H:i', strtotime($d[$tanggal_presensi]['jam_pulang'])) . "</span><br/>" .
                                           "<span style='color:" . (!empty($d[$tanggal_presensi]['jam_in']) ? "green" : "red") . "'>" . $jam_in . "</span> - " .
                                           "<span style='color:" . (!empty($d[$tanggal_presensi]['jam_out']) ? "green" : "red") . "'>" . $jam_out . "</span>";
                                    
                                    if ($terlambat && $terlambat['menitterlambat'] > 0) $ket .= "<br/><span style='color:red'>" . $terlambat['show_laporan'] . "</span>";
                                    if ($denda > 0) $ket .= "<br/><span style='color:red'>Denda : " . formatAngka($denda) . "</span>";
                                    if ($pc > 0) $ket .= "<br/><span style='color:red'>PC : " . formatAngkaDesimal($pc) . " Jam</span>";
                                    if ($potongan_jam > 0) $ket .= "<br/><span style='color:red'>PJ : " . formatAngkaDesimal($potongan_jam) . " Jam</span>";
                                    if ($jml_jam_lembur > 0) $ket .= "<br/><span style='color:#0b99b3'>Lembur : " . formatAngkaDesimal($jml_jam_lembur) . " Jam</span>";
                                @endphp
                            @else
                                @php
                                    $status_map = ['i' => ['IZIN', '#dea51f', 'jml_izin'], 's' => ['SAKIT', '#c8075b', 'jml_sakit'], 'c' => ['CUTI', '#0164b5', 'jml_cuti'], 'a' => ['ALPA', 'red', 'jml_alfa']];
                                    $st = $status_map[$d[$tanggal_presensi]['status']];
                                    $bgcolor = $st[1]; $textcolor = 'white'; ${$st[2]}++;
                                    $ket = "<b>" . $st[0] . "</b><br/>" . e($d[$tanggal_presensi]['keterangan_izin_absen'] ?? ($d[$tanggal_presensi]['keterangan_izin_sakit'] ?? ($d[$tanggal_presensi]['keterangan_izin_cuti'] ?? "")));
                                    if ($d[$tanggal_presensi]['status'] == 'a' || $d[$tanggal_presensi]['status'] == 'i') {
                                        $potongan_jam = ($d[$tanggal_presensi]['status_potongan'] ?? $generalsetting->status_potongan_jam) == 1 ? $d[$tanggal_presensi]['total_jam'] : 0;
                                        if ($potongan_jam > 0) $ket .= "<br/><span>PJ : " . formatAngkaDesimal($potongan_jam) . " Jam</span>";
                                    }
                                    $denda = $d[$tanggal_presensi]['denda'] ?? 0;
                                    if ($denda > 0) $ket .= "<br/><span style='color:red'>Denda : " . formatAngka($denda) . "</span>";
                                @endphp
                            @endif
                        @else
                            @php
                                $is_future = strtotime($tanggal_presensi) > strtotime(date('Y-m-d'));
                                if (!empty($ceklibur)) {
                                    $bgcolor = '#006400'; $textcolor = 'white'; $jml_libur++;
                                    $ket = "<b>LIBUR</b><br/>" . e($ceklibur[0]['keterangan']);
                                } else {
                                    $fallback = $mapJadwalByDate[$tanggal_presensi] ?? ($mapJadwalGrupByDate[$tanggal_presensi] ?? ($mapJadwalByDay[getHari($tanggal_presensi)] ?? ($jadwal_bydept[$d['kode_dept'].'|'.$d['kode_cabang']][getHari($tanggal_presensi)] ?? ($jadwal_global[getHari($tanggal_presensi)] ?? null))));
                                    if (is_array($fallback) && !$is_future) {
                                        $jml_alfa++; $bgcolor = 'red'; $textcolor = 'white';
                                        $potongan_jam = $generalsetting->status_potongan_jam == 1 ? $fallback['total_jam'] : 0;
                                        $ket = "<b>ALPA</b>";
                                        if ($potongan_jam > 0) $ket .= "<br/><span>PJ : " . formatAngkaDesimal($potongan_jam) . " Jam</span>";
                                    }
                                }
                                if ($is_libur && empty($ceklibur)) { $bgcolor = 'orange'; $textcolor = 'white'; $ket = "<b>LB-K</b>"; }
                            @endphp
                        @endif
                        @php
                            $total_denda += $denda; $total_potongan_jam += $potongan_jam; $total_jam_lembur += $jml_jam_lembur;
                            $bgcolor = !empty($ceklibur) ? '#006400' : ($is_libur ? 'orange' : $bgcolor);
                            if (!empty($ceklibur) || $is_libur) $textcolor = 'white';
                        @endphp
                        <td style="background-color:{{ $bgcolor }}; color:{{ $textcolor }}; border: 1px solid #000000; vertical-align: middle; text-align: center;">
                            {!! $ket !!}
                        </td>
                        @php
                            $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                        @endphp
                    @endwhile
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: right;">{{ formatAngka($total_denda) }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ formatAngkaDesimal($total_potongan_jam) }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ formatAngkaDesimal($total_jam_lembur) }}{{ $lemburKhusus ? ' *' : '' }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ $jml_hadir }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ $jml_izin }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ $jml_sakit }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ $jml_alfa }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ $jml_libur }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ $jml_terlambat }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ $jml_tidakscanmasuk }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ $jml_tidakscanpulang }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle; text-align: center;">{{ $jml_pulangcepat }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
