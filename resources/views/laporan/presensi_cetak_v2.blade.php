<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Presensi Format 2 {{ date('Y-m-d H:i:s') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
    <style>
        @page {
            size: A3 landscape; /* Higher size for Format 2 since it's very wide */
            margin: 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px; /* Smaller font for Format 2 */
            margin: 0;
            padding: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header h4 {
            line-height: 1.2;
            margin: 0 0 5px 0;
        }

        .btn {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.42857143;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            border: 1px solid transparent;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn-warning {
            color: #fff;
            background-color: #f0ad4e;
            border-color: #eea236;
        }

        .btn-secondary {
            color: #333;
            background-color: #fff;
            border-color: #ccc;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            max-height: 75vh;
            overflow-y: auto;
            position: relative;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .datatable3 {
            width: 100%;
            border-separate: separate; /* Required for perfect sticky alignment with borders */
            border-spacing: 0; /* Ensures cells touch exactly without gaps */
            font-size: 9px;
            min-width: 100%;
        }

        .datatable3 th,
        .datatable3 td {
            border: 1px solid #333;
            padding: 8px 4px; /* Slightly more vertical padding for breathing room */
            vertical-align: middle;
            white-space: nowrap;
            box-sizing: border-box;
            font-size: 11px; /* Uniform font size for better visibility */
        }

        .datatable3 th {
            background-color: #024a75;
            color: white;
            text-transform: uppercase;
            text-align: center;
            font-weight: bold;
            position: sticky;
            z-index: 20;
            box-shadow: inset 1px -1px 0 0 #333, inset -1px 1px 0 0 #333; /* Shared border simulation */
            background-clip: padding-box;
            border: none;
            height: 45px; /* Increased height for larger font labels */
            line-height: 1.1;
        }

        /* Sticky header rows - precisely calculated offsets matching 45px height */
        .datatable3 thead tr:nth-child(1) th { top: 0; z-index: 24; }
        .datatable3 thead tr:nth-child(2) th { top: 45px; z-index: 23; }
        .datatable3 thead tr:nth-child(3) th { top: 90px; z-index: 22; }
        .datatable3 thead tr:nth-child(4) th { top: 135px; z-index: 21; }

        @media screen {
            .sticky-col {
                position: sticky;
                background-color: #fff;
                z-index: 10;
                /* Ensure borders are visible in sticky columns */
                border-left: 1px solid #333 !important;
                border-right: 1px solid #333 !important;
            }
            
            .datatable3 th.sticky-col {
                z-index: 50 !important;
                background-color: #024a75;
                border-left: 1px solid #333 !important;
                border-right: 1px solid #333 !important;
            }

            .first-col { left: 0; width: 40px; min-width: 40px; max-width: 40px; }
            .second-col { left: 40px; width: 110px; min-width: 110px; max-width: 110px; }
            .third-col { left: 150px; width: 220px; min-width: 220px; max-width: 220px; }
            .fourth-col { left: 370px; width: 150px; min-width: 150px; max-width: 150px; }
            .fifth-col { left: 520px; width: 60px; min-width: 60px; max-width: 60px; }
            
            .datatable3 td, .datatable3 th {
                background-clip: padding-box;
            }
        }

        @media print {
            .table-responsive { overflow: visible; border: none; }
            .datatable3 { width: 100%; table-layout: auto; }
            .btn { display: none; }
            .sticky-col { position: static !important; }
        }
    </style>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/external/js/sweetalert2@11.js') }}"></script>
</head>

<body>

    <div class="header" style="margin-bottom: 10px">
        <table>
            <tr>
                <td style="width: 70px; padding-right: 10px;">
                    @if ($generalsetting->logo && Storage::exists('public/logo/' . $generalsetting->logo))
                        <img src="{{ asset('storage/logo/' . $generalsetting->logo) }}" alt="Logo Perusahaan" style="max-width: 100px;">
                    @else
                        <img src="https://placehold.co/100x100?text=Logo" alt="Logo Default" style="max-width: 100px;">
                    @endif
                </td>
                <td>
                    <h4 style="line-height: 20px; margin-bottom: 5px">
                        LAPORAN PRESENSI (FORMAT 2)
                        <br>
                        {{ $generalsetting->nama_perusahaan }}
                        <br>
                        PERIODE {{ date('d-m-Y', strtotime($periode_dari)) }} -
                        {{ date('d-m-Y', strtotime($periode_sampai)) }}
                    </h4>
                    <span style="font-style: italic;">{{ $generalsetting->alamat }}</span><br>
                    <span style="font-style: italic;">{{ $generalsetting->telepon }}</span>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <!-- Locking form removed as requested for Format 2 -->
                </td>
            </tr>
        </table>
    </div>

    <div class="table-responsive">
        <table class="datatable3">
            <thead>
                <tr>
                    <th rowspan="4" class="sticky-col first-col">No</th>
                    <th rowspan="4" class="sticky-col second-col">Nik</th>
                    <th rowspan="4" class="sticky-col third-col">Nama Karyawan</th>
                    <th rowspan="4" class="sticky-col fourth-col">Jabatan</th>
                    <th rowspan="4" class="sticky-col fifth-col">Dept</th>
                    <th colspan="{{ $jmlhari * 8 }}">Tanggal</th>
                    <th rowspan="4" style="min-width: 60px">Denda (T)</th>
                    <th rowspan="4" style="min-width: 60px">Pot. Jam (T)</th>
                    <th rowspan="4" style="min-width: 60px">Lembur (T)</th>
                    <th colspan="9">Rekap</th>
                </tr>
                <tr>
                    @php
                        $tanggal_presensi = $periode_dari;
                    @endphp
                    @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                        <th colspan="8">{{ getHari(date('Y-m-d', strtotime($tanggal_presensi))) }}</th>
                        @php
                            $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                        @endphp
                    @endwhile
                    <th rowspan="3">Hadir</th>
                    <th rowspan="3">Izin</th>
                    <th rowspan="3">Sakit</th>
                    <th rowspan="3">Alfa</th>
                    <th rowspan="3">Libur</th>
                    <th rowspan="3">Terlambat</th>
                    <th rowspan="3">T.S.M</th>
                    <th rowspan="3">T.S.P</th>
                    <th rowspan="3">P.C</th>
                </tr>
                <tr>
                    @php $tanggal_presensi = $periode_dari; @endphp
                    @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                        <th colspan="8">{{ date('d', strtotime($tanggal_presensi)) }}</th>
                        @php
                            $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                        @endphp
                    @endwhile
                </tr>
                <tr>
                    @php $tanggal_presensi = $periode_dari; @endphp
                    @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                        <th style="min-width: 80px; background-color: #056191;">Jadwal</th>
                        <th style="min-width: 40px; background-color: #056191;">In</th>
                        <th style="min-width: 40px; background-color: #056191;">Out</th>
                        <th style="min-width: 40px; background-color: #056191;">Ist-O</th>
                        <th style="min-width: 40px; background-color: #056191;">Ist-I</th>
                        <th style="min-width: 40px; background-color: #0b99b3;">Lbr</th> <!-- Teal for Lembur -->
                        <th style="min-width: 35px; background-color: #c0392b;">PJ</th>  <!-- Red for Potongan -->
                        <th style="min-width: 35px; background-color: #c0392b;">Dnd</th> <!-- Red for Denda -->
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
                        <td class="sticky-col first-col">{{ $loop->iteration }}</td>
                        <td class="sticky-col second-col">'{{ $d['nik_show'] ?? $d['nik'] }}</td>
                        <td class="sticky-col third-col">{{ $d['nama_karyawan'] }}</td>
                        <td class="sticky-col fourth-col">{{ $d['nama_jabatan'] }}</td>
                        <td class="sticky-col fifth-col" style="text-align: center">{{ $d['kode_dept'] }}</td>
                        
                        @php
                            $total_denda = 0; $total_potongan_jam = 0; $total_jam_lembur = 0;
                            $jml_hadir = 0; $jml_sakit = 0; $jml_izin = 0; $jml_cuti = 0;
                            $jml_libur = 0; $jml_alfa = 0; $jml_terlambat = 0;
                            $jml_pulangcepat = 0; $jml_tidakscanmasuk = 0; $jml_tidakscanpulang = 0;

                            $lemburKhusus = $lembur_khusus_map[$d['nik']] ?? null;
                        @endphp

                        @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                            @php
                                $denda = 0; $potongan_jam = 0;
                                $libur_key = $d['nik'] . '|' . $tanggal_presensi;
                                $ceklibur = $datalibur_indexed[$libur_key] ?? ($datalibur_by_tanggal[$tanggal_presensi] ?? []);

                                // Optimized: Check libur using employee-specific configuration or general configuration
                                if (!empty($ceklibur)) {
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
                                // Cek apakah data lembur sudah di-snapshot (dikunci)
                                $lembur_key = $d['nik'] . '|' . $tanggal_presensi;
                                $ceklembur = $datalembur_indexed[$lembur_key] ?? [];
                                $snapshot_lembur = isset($d[$tanggal_presensi]) && $d[$tanggal_presensi]['jam_lembur_aktual'] !== null;

                                if ($snapshot_lembur) {
                                    $jam_netto_harian = $d[$tanggal_presensi]['is_lembur_khusus']
                                        ? $d[$tanggal_presensi]['jam_lembur_aktual']
                                        : $d[$tanggal_presensi]['jam_lembur_netto'];
                                } else {
                                    // O(1) indexed lookup instead of linear search
                                    $lembur_aktual = !empty($ceklembur) ? hitungLembur($ceklembur) : 0;
                                    $tipe_hari = $is_libur ? 2 : 1;
                                    $jam_netto_harian = $lembur_aktual > 0 ? hitungJamNetto($lembur_aktual, $tipe_hari) : 0;
                                    
                                    // Jika ada lembur khusus, gunakan JAM AKTUAL (Gross)
                                    if ($lemburKhusus) {
                                        $jam_netto_harian = $lembur_aktual;
                                    }
                                }

                                $nama_hari = getHari($tanggal_presensi);

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
                                    if (!empty($ceklibur)) { $bgcolor = '#006400'; $textcolor = 'white'; $jml_libur++; $col_data['in'] = 'LIBUR'; }
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
                                $cell_style = "background-color:$bgcolor; color:$textcolor;";
                            @endphp
                            
                            <td style="{{ $cell_style }} text-align:center;">{{ $col_data['jadwal'] }}</td>
                            <td style="{{ $cell_style }} text-align:center;">{{ $col_data['in'] }}</td>
                            <td style="{{ $cell_style }} text-align:center;">{{ $col_data['out'] }}</td>
                            <td style="{{ $cell_style }} text-align:center;">{{ $col_data['ist_o'] }}</td>
                            <td style="{{ $cell_style }} text-align:center;">{{ $col_data['ist_i'] }}</td>
                            <td style="{{ $cell_style }} text-align:center;">
                                @if($col_data['lbr'] != '-')
                                    <a href="{{ route('laporan.lemburdetail', [$d['nik'], $periode_dari, $periode_sampai]) }}" target="_blank" style="color: #024a75; text-decoration: underline;">
                                        {{ $col_data['lbr'] }}
                                    </a>
                                @else
                                    {{ $col_data['lbr'] }}
                                @endif
                            </td>
                            <td style="{{ $cell_style }} text-align:center;">{{ $col_data['pj'] }}</td>
                            <td style="{{ $cell_style }} text-align:right;">{{ $col_data['dnd'] }}</td>

                            @php $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi))); @endphp
                        @endwhile

                        <td style="text-align: right">{{ formatAngka($total_denda) }}</td>
                        <td style="text-align: center">{{ formatAngkaDesimal($total_potongan_jam) }}</td>
                        <td style="text-align:center">
                            <a href="{{ route('laporan.lemburdetail', [$d['nik'], $periode_dari, $periode_sampai]) }}" target="_blank" style="color: #024a75; text-decoration: underline; font-weight: bold;">
                                {{ formatAngkaDesimal($total_jam_lembur) }}
                                @if ($lemburKhusus)
                                    <span style="font-size: 10px; color: #ea580c;">★</span>
                                @endif
                            </a>
                        </td>
                        <td style="text-align:center">{{ $jml_hadir }}</td>
                        <td style="text-align:center">{{ $jml_izin }}</td>
                        <td style="text-align:center">{{ $jml_sakit }}</td>
                        <td style="text-align:center">{{ $jml_alfa }}</td>
                        <td style="text-align:center">{{ $jml_libur }}</td>
                        <td style="text-align:center">{{ $jml_terlambat }}</td>
                        <td style="text-align:center">{{ $jml_tidakscanmasuk }}</td>
                        <td style="text-align:center">{{ $jml_tidakscanpulang }}</td>
                        <td style="text-align:center">{{ $jml_pulangcepat }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            // Locking scripts removed for Format 2
        });
    </script>
</body>
</html>
