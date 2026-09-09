<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Presensi {{ date('Y-m-d H:i:s') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/report.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
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

        .btn-warning { color: #fff; background-color: #f0ad4e; border-color: #eea236; }
        .btn-secondary { color: #333; background-color: #fff; border-color: #ccc; }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            max-height: 75vh;
            overflow-y: auto;
            position: relative;
            margin-bottom: 20px;
            border: 1px solid #333;
        }

        .datatable3 {
            width: 100%;
            border-collapse: separate; /* Changed to separate for better sticky behavior */
            border-spacing: 0;
            font-size: 10px;
            min-width: 100%;
        }

        .datatable3 th, .datatable3 td {
            border: 1px solid #333;
            padding: 8px 4px;
            vertical-align: middle;
            white-space: nowrap;
            box-sizing: border-box;
        }

        .datatable3 th {
            background-color: #024a75;
            color: white;
            text-transform: uppercase;
            text-align: center;
            font-weight: bold;
            position: sticky;
            z-index: 20;
            height: 40px; /* Force height for predictable sticky offsets */
            line-height: 1.2;
        }

        /* Sticky header rows - precisely calculated offsets matching 40px height */
        .datatable3 thead tr:nth-child(1) th { top: 0; z-index: 25; }
        .datatable3 thead tr:nth-child(2) th { top: 40px; z-index: 24; }
        .datatable3 thead tr:nth-child(3) th { top: 80px; z-index: 23; }
        .datatable3 thead tr:nth-child(4) th { top: 120px; z-index: 22; }

        @media screen {
            .sticky-col {
                position: sticky;
                background-color: #fff;
                z-index: 10;
                /* Ensure borders are visible in sticky columns */
                border-left: 1px solid #333 !important;
                border-right: 1px solid #333 !important;
            }
            
            /* Intersection of sticky row and sticky column */
            .datatable3 th.sticky-col { 
                z-index: 50 !important; /* Bumped to ensure it's above all other headers */
                background-color: #024a75;
                border-left: 1px solid #333 !important;
                border-right: 1px solid #333 !important;
            }

            .first-col { left: 0; width: 40px; min-width: 40px; max-width: 40px; }
            .second-col { left: 40px; width: 80px; min-width: 80px; max-width: 80px; }
            .third-col { left: 120px; width: 180px; min-width: 180px; max-width: 180px; }
            .fourth-col { left: 300px; width: 130px; min-width: 130px; max-width: 130px; }
            .fifth-col { left: 430px; width: 60px; min-width: 60px; max-width: 60px; }
            
            /* To ensure content doesn't bleed through borders when scrolling */
            .datatable3 td, .datatable3 th {
                background-clip: padding-box;
            }
        }

        @media print {
            .table-responsive { overflow: visible; border: none; max-height: none; }
            .datatable3 { width: 100%; table-layout: auto; border-collapse: collapse; }
            .btn { display: none; }
            .sticky-col { position: static !important; background-color: transparent !important; }
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
                        LAPORAN PRESENSI
                        <br>
                        {{ $generalsetting->nama_perusahaan }}
                        <br>
                        PERIODE {{ date('d-m-Y', strtotime($periode_dari)) }} - {{ date('d-m-Y', strtotime($periode_sampai)) }}
                    </h4>
                    <span style="font-style: italic;">{{ $generalsetting->alamat }}</span><br>
                    <span style="font-style: italic;">{{ $generalsetting->telepon }}</span>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <form id="formKunciLaporan" method="POST" action="{{ route('laporan.kuncilaporan') }}" style="display: inline-block;">
                        @csrf
                        @foreach($request_params as $k => $v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endforeach
                        <button type="submit" class="btn btn-warning">Kunci Laporan</button>
                    </form>
                    <form id="formBatalkanKunciLaporan" method="POST" action="{{ route('laporan.batalkankuncilaporan') }}" style="display: inline-block;">
                        @csrf
                        @foreach($request_params as $k => $v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endforeach
                        <button type="submit" class="btn btn-secondary" style="background-color: #6c757d; color: #fff;">Batalkan Kunci</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-responsive">
        <table class="datatable3">
            <thead>
                <tr>
                    <th rowspan="3" class="sticky-col first-col">No</th>
                    <th rowspan="3" class="sticky-col second-col">Nik</th>
                    <th rowspan="3" class="sticky-col third-col">Nama Karyawan</th>
                    <th rowspan="3" class="sticky-col fourth-col">Jabatan</th>
                    <th rowspan="3" class="sticky-col fifth-col">Dept</th>
                    <th colspan="{{ $jmlhari }}">Tanggal</th>
                    <th rowspan="3">Denda</th>
                    <th rowspan="3">Pot. Jam</th>
                    <th rowspan="3">Lembur</th>
                    <th colspan="9">Rekap</th>
                </tr>
                <tr>
                    @php $tanggal_presensi = $periode_dari; @endphp
                    @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                        <th>{{ getHari(date('Y-m-d', strtotime($tanggal_presensi))) }}</th>
                        @php $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi))); @endphp
                    @endwhile
                    <th rowspan="2">Hadir</th>
                    <th rowspan="2">Izin</th>
                    <th rowspan="2">Sakit</th>
                    <th rowspan="2">Alfa</th>
                    <th rowspan="2">Libur</th>
                    <th rowspan="2">Terlambat</th>
                    <th rowspan="2">T.S.M</th>
                    <th rowspan="2">T.S.P</th>
                    <th rowspan="2">P.C</th>
                </tr>
                <tr>
                    @php $tanggal_presensi = $periode_dari; @endphp
                    @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                        <th>{{ date('d', strtotime($tanggal_presensi)) }}</th>
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
                        $lemburKhusus = $lembur_khusus_map[$d['nik']] ?? null;
                    @endphp
                    <tr>
                        <td class="sticky-col first-col" style="text-align: center">{{ $loop->iteration }}</td>
                        <td class="sticky-col second-col">'{{ $d['nik_show'] ?? $d['nik'] }}</td>
                        <td class="sticky-col third-col">{{ $d['nama_karyawan'] }}</td>
                        <td class="sticky-col fourth-col">{{ $d['nama_jabatan'] }}</td>
                        <td class="sticky-col fifth-col" style="text-align: center">{{ $d['kode_dept'] }}</td>
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
                                    $has_schedule = false; $nama_hari_check = getHari($tanggal_presensi);
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
                                    $jml_jam_lembur = $d[$tanggal_presensi]['is_lembur_khusus'] ? $d[$tanggal_presensi]['jam_lembur_aktual'] : $d[$tanggal_presensi]['jam_lembur_netto'];
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
                                        if ($denda > 0) $ket .= "<br/><span style='color:red'>Denda: " . formatAngka($denda) . "</span>";
                                        if ($potongan_jam > 0) $ket .= "<br/><span style='color:red'>PJ: " . formatAngkaDesimal($potongan_jam) . " Jam</span>";
                                        if ($jml_jam_lembur > 0) $ket .= "<br/><span style='color:#0b99b3'>Lembur: " . formatAngkaDesimal($jml_jam_lembur) . " Jam</span>";
                                    @endphp
                                @else
                                    @php
                                        $status_map = ['i' => ['IZIN', '#dea51f', 'jml_izin'], 's' => ['SAKIT', '#c8075b', 'jml_sakit'], 'c' => ['CUTI', '#0164b5', 'jml_cuti'], 'a' => ['ALPA', 'red', 'jml_alfa']];
                                        $st = $status_map[$d[$tanggal_presensi]['status']];
                                        $bgcolor = $st[1]; $textcolor = 'white'; ${$st[2]}++;
                                        $ket = "<b>" . $st[0] . "</b><br/>" . e($d[$tanggal_presensi]['keterangan_izin_absen'] ?? ($d[$tanggal_presensi]['keterangan_izin_sakit'] ?? ($d[$tanggal_presensi]['keterangan_izin_cuti'] ?? "")));
                                        if ($d[$tanggal_presensi]['status'] == 'a' || $d[$tanggal_presensi]['status'] == 'i') {
                                            $potongan_jam = ($d[$tanggal_presensi]['status_potongan'] ?? $generalsetting->status_potongan_jam) == 1 ? $d[$tanggal_presensi]['total_jam'] : 0;
                                            if ($potongan_jam > 0) $ket .= "<br/>PJ: " . formatAngkaDesimal($potongan_jam) . " Jam";
                                        }
                                        $denda = $d[$tanggal_presensi]['denda'] ?? 0;
                                        if ($denda > 0) $ket .= "<br/><span style='color:red'>Denda: " . formatAngka($denda) . "</span>";
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
                                            if ($potongan_jam > 0) $ket .= "<br/>PJ: " . formatAngkaDesimal($potongan_jam) . " Jam";
                                        }
                                    }
                                    if ($is_libur && empty($ceklibur)) { $bgcolor = 'orange'; $textcolor = 'white'; $ket = "<b>LB-K</b>"; }
                                @endphp
                            @endif
                            @php
                                $total_denda += $denda; $total_potongan_jam += $potongan_jam; $total_jam_lembur += $jml_jam_lembur;
                                $bgcolor = !empty($ceklibur) ? '#006400' : ($is_libur ? 'orange' : $bgcolor);
                                if (!empty($ceklibur) || $is_libur) $textcolor = 'white';
                                $is_locked = isset($d[$tanggal_presensi]['status_potongan']) && $d[$tanggal_presensi]['status_potongan'] !== null;
                            @endphp
                            <td style="background-color:{{ $bgcolor }}; color:{{ $textcolor }}; text-align: center; position: relative;">
                                @if ($is_locked)
                                    <span style="position: absolute; top: 2px; right: 2px; color: #ff9f43;" title="Terkunci"><i class="ti ti-lock" style="font-size: 10px;"></i></span>
                                @endif
                                {!! $ket !!}
                            </td>
                            @php $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi))); @endphp
                        @endwhile
                        <td style="text-align: right">{{ formatAngka($total_denda) }}</td>
                        <td style="text-align: center">{{ formatAngkaDesimal($total_potongan_jam) }}</td>
                        <td style="text-align: center">
                            {{ formatAngkaDesimal($total_jam_lembur) }}
                            @if ($lemburKhusus) <span style="font-size: 10px; color: #ea580c;">★</span> @endif
                        </td>
                        <td style="text-align: center">{{ $jml_hadir }}</td>
                        <td style="text-align: center">{{ $jml_izin }}</td>
                        <td style="text-align: center">{{ $jml_sakit }}</td>
                        <td style="text-align: center">{{ $jml_alfa }}</td>
                        <td style="text-align: center">{{ $jml_libur }}</td>
                        <td style="text-align: center">{{ $jml_terlambat }}</td>
                        <td style="text-align: center">{{ $jml_tidakscanmasuk }}</td>
                        <td style="text-align: center">{{ $jml_tidakscanpulang }}</td>
                        <td style="text-align: center">{{ $jml_pulangcepat }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        $(function() {
            $('#formKunciLaporan, #formBatalkanKunciLaporan').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let url = form.attr('action');
                let data = form.serialize();
                let actionText = form.attr('id') === 'formKunciLaporan' ? 'mengunci' : 'membatalkan kunci';
                
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: `Anda akan ${actionText} laporan ini!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Mohon Tunggu...',
                            text: 'Sedang memproses data...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        $.ajax({
                            type: 'POST',
                            url: url,
                            data: data,
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: response.message || 'Terjadi kesalahan.',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errMsg = 'Terjadi kesalahan pada server.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errMsg = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    title: 'Error!',
                                    text: errMsg,
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
