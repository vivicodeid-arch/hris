@inject('pph21Service', 'App\Services\Pph21Service')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Gaji</title>
</head>
<body>
    @php
        $isPphActive = $pph21Service->isAktif();
        $pphSetting = $isPphActive ? $pph21Service->getSetting() : null;
        $isGrossUp = $isPphActive && $pphSetting->metode_tanggungan === 'GROSS_UP';
        
        $bulanNum = $bulan ?? request('bulan') ?? date('m', strtotime($periode_dari));
        $tahunNum = $tahun ?? request('tahun') ?? date('Y', strtotime($periode_dari));
        $slipGajiRecord = DB::table('slip_gaji')
            ->where('bulan', $bulanNum)
            ->where('tahun', $tahunNum)
            ->first();
        $kodeSlipGaji = $slipGajiRecord ? $slipGajiRecord->kode_slip_gaji : null;
        
        $colspanVal = 17 + count($jenis_tunjangan) + ($isPphActive ? 1 : 0) + ($isGrossUp ? 1 : 0);
    @endphp
    <table class="datatable3" style="width: 100%; border-collapse: collapse; border: 1px solid #000000">
        <thead>
            <tr>
                <td colspan="{{ $colspanVal }}">
                    <h4 style="line-height: 20px; margin-bottom: 5px; font-weight: bold; font-size: 14px">
                        LAPORAN GAJI<br>
                        {{ $generalsetting->nama_perusahaan }}<br>
                        PERIODE {{ date('d-m-Y', strtotime($periode_dari)) }} - {{ date('d-m-Y', strtotime($periode_sampai)) }}
                    </h4>
                    <span style="font-style: italic; font-size: 12px">{{ $generalsetting->alamat }}</span><br>
                    <span style="font-style: italic; font-size: 12px">{{ $generalsetting->telepon }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="{{ $colspanVal }}"></td>
            </tr>
            <tr>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">No</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Nik</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Nama Karyawan</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Jabatan</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Dept</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Cabang</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Gaji Pokok</th>
                @if(count($jenis_tunjangan) > 0 || $isGrossUp)
                    <th colspan="{{ count($jenis_tunjangan) + ($isGrossUp ? 1 : 0) }}" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Tunjangan</th>
                @endif
                <th rowspan="2" style="border: 1px solid #000000; background-color: orange; color: white; vertical-align: middle;">&#x3A3; Bruto</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">&#x3A3; Jam Kerja</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">Upah/Jam</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: red; color: white; vertical-align: middle;">Denda</th>
                <th colspan="2" style="border: 1px solid #000000; background-color: red; color: white; vertical-align: middle;">Pot. Jam</th>
                <th colspan="2" style="border: 1px solid #000000; background-color: red; color: white; vertical-align: middle;">BPJS</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: red; color: white; vertical-align: middle;">Pinjaman</th>
                @if ($isPphActive)
                    <th rowspan="2" style="border: 1px solid #000000; background-color: red; color: white; vertical-align: middle;">PPh 21</th>
                @endif
                <th rowspan="2" style="border: 1px solid #000000; background-color: red; color: white; vertical-align: middle;">Potongan</th>
                <th colspan="2" style="border: 1px solid #000000; background-color: #007148; color: white; vertical-align: middle;">Lembur</th>
                <th colspan="2" style="border: 1px solid #000000; background-color: #0176C5; color: white; vertical-align: middle;">Penyesuaian</th>
                <th rowspan="2" style="border: 1px solid #000000; background-color: #007148; color: white; vertical-align: middle;">Gaji Bersih</th>
            </tr>
            <tr>
                @foreach ($jenis_tunjangan as $j)
                    <th style="border: 1px solid #000000; background-color: #024a75; color: white; vertical-align: middle;">{{ $j->jenis_tunjangan }}</th>
                @endforeach
                @if ($isGrossUp)
                    <th style="border: 1px solid #000000; background-color: #007148; color: white; vertical-align: middle;">Tunj. Pajak</th>
                @endif
                <th style="border: 1px solid #000000; background-color: red; color: white; vertical-align: middle;">Jam</th>
                <th style="border: 1px solid #000000; background-color: red; color: white; vertical-align: middle;">Jumlah</th>

                <th style="border: 1px solid #000000; background-color: red; color: white; vertical-align: middle;">Kesehatan</th>
                <th style="border: 1px solid #000000; background-color: red; color: white; vertical-align: middle;">Tenaga Kerja</th>

                <th style="border: 1px solid #000000; background-color: #007148; color: white; vertical-align: middle;">Jam (A|N)</th>
                <th style="border: 1px solid #000000; background-color: #007148; color: white; vertical-align: middle;">Jumlah</th>

                <th style="border: 1px solid #000000; background-color: #0176C5; color: white; vertical-align: middle;">Penambah</th>
                <th style="border: 1px solid #000000; background-color: #0176C5; color: white; vertical-align: middle;">Pengurang</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_gaji_pokok = 0;
                foreach ($jenis_tunjangan as $j) {
                    ${'total_tunjangan_' . $j->kode_jenis_tunjangan} = 0;
                }
                $total_bruto = 0;
                $total_all_denda = 0;
                $total_jumlah_potongan_jam = 0;
                $total_gaji_bersih = 0;
                $total_bpjs_kesehatan = 0;
                $total_bpjs_tenagakerja = 0;
                $total_all_potongan = 0;
                $total_upah_lembur = 0;
                $total_penambah = 0;
                $total_pengurang = 0;
                $total_pph21 = 0;
                $total_tunjangan_pajak = 0;
            @endphp
            @foreach ($laporan_presensi as $d)
                @php
                    $tanggal_presensi = $periode_dari;
                    // Mapping jadwal untuk NIK ini dari berbagai sumber
                    $mapJadwalByDate = $jadwal_bydate[$d['nik']] ?? [];
                    $mapJadwalGrupByDate = $jadwal_grup_bydate[$d['nik']] ?? [];
                    $mapJadwalByDay = $jadwal_byday[$d['nik']] ?? [];

                    $total_tunjangan = 0;
                    foreach ($jenis_tunjangan as $j) {
                        $total_tunjangan += $d[$j->kode_jenis_tunjangan];
                    }

                    $total_denda = 0;
                    $total_potongan_jam = 0;
                    $total_jam_lembur_aktual = 0;
                    $total_jam_netto_lembur = 0;
                    $total_nominal_lembur_snapshot = 0;
                    $has_lembur_snapshot = false;
                    $lemburKhusus = $lembur_khusus_map[$d['nik']] ?? null;

                    while (strtotime($tanggal_presensi) <= strtotime($periode_sampai)) {
                        $denda = 0;
                        $potongan_jam = 0;

                        // Optimized: Check libur using pre-loaded data (no DB query)
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
                            }
                            $is_libur = !$has_schedule;
                        }
                        $tipe_hari = $is_libur ? 2 : 1;

                        // Cek snapshot lembur (data terkunci)
                        $lembur_key = $d['nik'] . '|' . $tanggal_presensi;
                        $ceklembur_data = $datalembur_indexed[$lembur_key] ?? [];
                        $snapshot_lembur = isset($d[$tanggal_presensi]) && ($d[$tanggal_presensi]['jam_lembur_aktual'] ?? null) !== null;
                        if ($snapshot_lembur) {
                            $has_lembur_snapshot = true;
                            $jml_jam_lembur = $d[$tanggal_presensi]['jam_lembur_aktual'];
                            $jam_netto_harian = $d[$tanggal_presensi]['jam_lembur_netto'];
                            $total_nominal_lembur_snapshot += $d[$tanggal_presensi]['nominal_lembur'] ?? 0;
                        } else {
                            $lembur_aktual = hitungLembur($ceklembur_data);
                            if ($lembur_aktual > 0) {
                                $jml_jam_lembur = $lembur_aktual;
                                $jam_netto_harian = hitungJamNetto($lembur_aktual, $tipe_hari);
                            } else {
                                $jml_jam_lembur = 0;
                                $jam_netto_harian = 0;
                            }
                        }
                        $nama_hari = getHari($tanggal_presensi);

                        $libur_key = $d['nik'] . '|' . $tanggal_presensi;
                        $ceklibur = $datalibur_indexed[$libur_key] ?? ($datalibur_by_tanggal[$tanggal_presensi] ?? []);

                        if (isset($d[$tanggal_presensi])) {
                            if ($d[$tanggal_presensi]['status'] == 'h') {
                                $jam_masuk = $tanggal_presensi . ' ' . $d[$tanggal_presensi]['jam_masuk'];
                                $terlambat = hitungjamterlambat($d[$tanggal_presensi]['jam_in'], $jam_masuk);
                                
                                $denda_dari_db = isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;

                                if ($denda_dari_db !== null) {
                                    $denda = $denda_dari_db;
                                    if ($terlambat != null) {
                                        if ($terlambat['desimal_terlambat'] < 1) {
                                            $potongan_jam_terlambat = 0;
                                        } else {
                                            $potongan_jam_terlambat = $terlambat['desimal_terlambat'] > $d[$tanggal_presensi]['total_jam']
                                                ? $d[$tanggal_presensi]['total_jam']
                                                : $terlambat['desimal_terlambat'];
                                        }
                                    } else {
                                        $potongan_jam_terlambat = 0;
                                    }
                                } else {
                                    if ($terlambat != null) {
                                        if ($terlambat['desimal_terlambat'] < 1) {
                                            $potongan_jam_terlambat = 0;
                                            $denda = hitungdenda($denda_list, $terlambat['menitterlambat']);
                                        } else {
                                            $potongan_jam_terlambat = $terlambat['desimal_terlambat'] > $d[$tanggal_presensi]['total_jam']
                                                ? $d[$tanggal_presensi]['total_jam']
                                                : $terlambat['desimal_terlambat'];
                                            $denda = 0;
                                        }
                                    } else {
                                        $potongan_jam_terlambat = 0;
                                        $denda = 0;
                                    }
                                }

                                $pulangcepat = hitungpulangcepat(
                                    $tanggal_presensi,
                                    $d[$tanggal_presensi]['jam_out'],
                                    $d[$tanggal_presensi]['jam_pulang'],
                                    $d[$tanggal_presensi]['istirahat'],
                                    $d[$tanggal_presensi]['jam_awal_istirahat'],
                                    $d[$tanggal_presensi]['jam_akhir_istirahat'],
                                    $d[$tanggal_presensi]['lintashari']
                                );
                                $pulangcepat = $pulangcepat > $d[$tanggal_presensi]['total_jam'] ? $d[$tanggal_presensi]['total_jam'] : $pulangcepat;
                                
                                $potongan_tidak_absen_masuk_atau_pulang = empty($d[$tanggal_presensi]['jam_out']) || empty($d[$tanggal_presensi]['jam_in'])
                                    ? $d[$tanggal_presensi]['total_jam']
                                    : 0;
                                $potongan_istirahat = hitungPotonganIstirahat(
                                    $d[$tanggal_presensi]['istirahat_out'],
                                    $d[$tanggal_presensi]['istirahat_in'],
                                    $d[$tanggal_presensi]['jam_awal_istirahat'],
                                    $d[$tanggal_presensi]['jam_akhir_istirahat']
                                );
                                $status_potongan_istirahat = $d[$tanggal_presensi]['status_potongan_istirahat'] ?? $generalsetting->potongan_istirahat;
                                $potongan_jam = $potongan_tidak_absen_masuk_atau_pulang == 0
                                    ? $pulangcepat + $potongan_jam_terlambat + ($status_potongan_istirahat == 1 ? $potongan_istirahat : 0)
                                    : $potongan_tidak_absen_masuk_atau_pulang;
                            } elseif ($d[$tanggal_presensi]['status'] == 'i') {
                                $potongan_jam = $d[$tanggal_presensi]['total_jam'];
                                $denda_dari_db = isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;
                                $denda = $denda_dari_db !== null ? $denda_dari_db : 0;
                            } elseif ($d[$tanggal_presensi]['status'] == 's') {
                                $denda_dari_db = isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;
                                $denda = $denda_dari_db !== null ? $denda_dari_db : 0;
                            } elseif ($d[$tanggal_presensi]['status'] == 'c') {
                                $denda_dari_db = isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;
                                $denda = $denda_dari_db !== null ? $denda_dari_db : 0;
                            } elseif ($d[$tanggal_presensi]['status'] == 'a') {
                                $potongan_jam = $d[$tanggal_presensi]['total_jam'];
                                $denda_dari_db = isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;
                                $denda = $denda_dari_db !== null ? $denda_dari_db : 0;
                            }
                        } else {
                            $potongan_jam = 0;
                            if (empty($ceklibur)) {
                                $totalJamJadwal = $mapJadwalByDate[$tanggal_presensi] ?? null;
                                if ($totalJamJadwal === null) {
                                    $totalJamJadwal = $mapJadwalGrupByDate[$tanggal_presensi] ?? null;
                                }
                                if ($totalJamJadwal === null) {
                                    $totalJamJadwal = $mapJadwalByDay[$nama_hari] ?? null;
                                }
                                if ($totalJamJadwal === null) {
                                    $keyDeptCabang = $d['kode_dept'] . '|' . $d['kode_cabang'];
                                    $mapDept = $jadwal_bydept[$keyDeptCabang] ?? [];
                                    $totalJamJadwal = $mapDept[$nama_hari] ?? null;
                                }
                                if ($totalJamJadwal !== null) {
                                    $potongan_jam = is_array($totalJamJadwal) ? $totalJamJadwal['total_jam'] : $totalJamJadwal;
                                }
                            }
                        }

                        $status_potongan_harian = isset($d[$tanggal_presensi]['status_potongan']) ? $d[$tanggal_presensi]['status_potongan'] : $generalsetting->status_potongan_jam;
                        if ($status_potongan_harian == 0) {
                            $potongan_jam = 0;
                        }
                        $total_denda += $denda;
                        $total_potongan_jam += $potongan_jam;
                        $total_jam_lembur_aktual += $jml_jam_lembur;
                        $total_jam_netto_lembur += $jam_netto_harian;
                        $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                    }

                    if ($total_potongan_jam > $generalsetting->total_jam_bulan) {
                        $total_potongan_jam = $generalsetting->total_jam_bulan;
                    }
                    
                    $upah_perjam = $d['gaji_pokok'] / $generalsetting->total_jam_bulan;
                    $jumlah_potongan_jam = ROUND($upah_perjam) * $total_potongan_jam;

                    if ($has_lembur_snapshot) {
                        $upah_lembur = $total_nominal_lembur_snapshot;
                    } elseif ($lemburKhusus) {
                        $upah_lembur = $lemburKhusus->upah_perjam * $total_jam_lembur_aktual;
                    } else {
                        $upah_perjam_lembur = ($d['gaji_pokok'] + $total_tunjangan) / ($generalsetting->total_jam_bulan ?: 173);
                        $upah_lembur = ROUND($upah_perjam_lembur) * $total_jam_netto_lembur;
                    }

                    // --- PPh 21 Calculation ---
                    $pph21_terutang = 0;
                    $pph21_ditanggung_perusahaan = 0;
                    $tunjangan_pajak = 0;
                    $potongan_pph21 = 0;
                    $active_metode_tanggungan = 'GROSS';

                    if ($isPphActive) {
                        $snapshot = null;
                        if ($kodeSlipGaji) {
                            $snapshot = $pph21Service->getSnapshot($kodeSlipGaji, $d['nik']);
                        }

                        if ($snapshot) {
                            $pph21_terutang = $snapshot->pph21_terutang;
                            $pph21_ditanggung_perusahaan = $snapshot->pph21_ditanggung_perusahaan;
                            $active_metode_tanggungan = $snapshot->metode_tanggungan;
                        } else {
                            $hitungPph = (($d['hitung_pph21'] ?? 1) == 1);
                            $pphSetting = $pph21Service->getSetting();
                            $active_metode_tanggungan = $pphSetting->metode_tanggungan;

                            if ($hitungPph) {
                                $tunjanganMap = [];
                                foreach ($jenis_tunjangan as $j) {
                                    $tunjanganMap[$j->kode_jenis_tunjangan] = $d[$j->kode_jenis_tunjangan] ?? 0;
                                }
                                $nilaiKomponen = [
                                    'gaji_pokok' => $d['gaji_pokok'],
                                    'bpjs_kesehatan' => $d['bpjs_kesehatan'],
                                    'bpjs_tenagakerja' => $d['bpjs_tenagakerja'],
                                    'lembur' => $upah_lembur,
                                    'tunjangan' => $tunjanganMap,
                                ];

                                $totalPphJanNov = 0;
                                $totalBrutoJanNov = 0;
                                if ($bulanNum == 12 && isset($janNovStatsAll[$d['nik']])) {
                                    $totalPphJanNov = $janNovStatsAll[$d['nik']]->total_pph ?? 0;
                                    $totalBrutoJanNov = $janNovStatsAll[$d['nik']]->total_bruto ?? 0;
                                }

                                $pphResult = $pph21Service->hitung(
                                    $nilaiKomponen,
                                    $d['kode_status_kawin'] ?? null,
                                    (int)$bulanNum,
                                    (int)$totalPphJanNov,
                                    (float)$totalBrutoJanNov
                                );

                                $pph21_terutang = $pphResult['pph21_terutang'] ?? 0;
                                $pph21_ditanggung_perusahaan = $pphResult['pph21_ditanggung_perusahaan'] ?? 0;
                            }
                        }

                        if ($active_metode_tanggungan === 'GROSS_UP') {
                            $tunjangan_pajak = $pph21_terutang + $pph21_ditanggung_perusahaan;
                            $potongan_pph21 = $pph21_terutang + $pph21_ditanggung_perusahaan;
                        } else {
                            $tunjangan_pajak = 0;
                            $potongan_pph21 = $pph21_terutang;
                        }
                    }

                    $bruto = $d['gaji_pokok'] + $total_tunjangan + $tunjangan_pajak;
                    $total_potongan = ROUND($jumlah_potongan_jam) + $total_denda + $d['bpjs_kesehatan'] + $d['bpjs_tenagakerja'] + ($d['cicilan_pinjaman'] ?? 0) + $potongan_pph21;

                    // Accumulate totals
                    $total_all_potongan += $total_potongan;
                    $total_upah_lembur += $upah_lembur;
                    $total_gaji_pokok += $d['gaji_pokok'];
                    $total_bpjs_kesehatan += $d['bpjs_kesehatan'];
                    $total_bpjs_tenagakerja += $d['bpjs_tenagakerja'];
                    $total_penambah += $d['penambah'];
                    $total_pengurang += $d['pengurang'];
                    $total_bruto += $bruto;
                    $total_all_denda += $total_denda;
                    $total_jumlah_potongan_jam += $jumlah_potongan_jam;
                    $total_pph21 += $potongan_pph21;
                    $total_tunjangan_pajak += $tunjangan_pajak;

                    $gaji_bersih = $d['gaji_pokok'] + $total_tunjangan + $tunjangan_pajak - $total_potongan + $d['penambah'] - $d['pengurang'] + $upah_lembur;
                    $total_gaji_bersih += $gaji_bersih;
                @endphp
                <tr>
                    <td style="border: 1px solid #000000; vertical-align: middle;">{{ $loop->iteration }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">'{{ $d['nik_show'] ?? $d['nik'] }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">{{ $d['nama_karyawan'] }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">{{ $d['nama_jabatan'] }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">{{ $d['kode_dept'] }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">{{ $d['kode_cabang'] }}</td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($d['gaji_pokok']) }}</td>
                    @foreach ($jenis_tunjangan as $j)
                        @php
                            ${'total_tunjangan_' . $j->kode_jenis_tunjangan} += $d[$j->kode_jenis_tunjangan];
                        @endphp
                        <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($d[$j->kode_jenis_tunjangan]) }}</td>
                    @endforeach
                    @if ($isGrossUp)
                        <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($tunjangan_pajak) }}</td>
                    @endif
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">
                        {{ formatAngka($bruto) }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $generalsetting->total_jam_bulan }}</td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">
                        {{ formatAngka($upah_perjam) }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($total_denda) }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ formatAngkaDesimal($total_potongan_jam) }}</td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">
                        {{ formatAngka($jumlah_potongan_jam) }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($d['bpjs_kesehatan']) }}</td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($d['bpjs_tenagakerja']) }}</td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($d['cicilan_pinjaman'] ?? 0) }}</td>
                    @if ($isPphActive)
                        <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($potongan_pph21) }}</td>
                    @endif
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($total_potongan) }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">
                        @if ($lemburKhusus)
                            {{ formatAngkaDesimal($total_jam_lembur_aktual) }} ★
                        @else
                            {{ formatAngkaDesimal($total_jam_netto_lembur) }}
                        @endif
                    </td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($upah_lembur) }}</td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($d['penambah']) }}</td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($d['pengurang']) }}</td>
                    <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ formatAngka($gaji_bersih) }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="6" style="border: 1px solid #000000; vertical-align: middle; text-align: center; font-weight: bold;">TOTAL</th>
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_gaji_pokok) }}</th>
                @foreach ($jenis_tunjangan as $d_tj)
                    <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">
                        {{ formatAngka(${'total_tunjangan_' . $d_tj->kode_jenis_tunjangan}) }}</th>
                @endforeach
                @if ($isGrossUp)
                    <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_tunjangan_pajak) }}</th>
                @endif
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_bruto) }}</th>
                <th colspan="2" style="border: 1px solid #000000;"></th>
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_all_denda) }}</th>
                <th style="border: 1px solid #000000;"></th>
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_jumlah_potongan_jam) }}</th>
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_bpjs_kesehatan) }}</th>
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_bpjs_tenagakerja) }}</th>
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($laporan_presensi->sum('cicilan_pinjaman')) }}</th>
                @if ($isPphActive)
                    <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_pph21) }}</th>
                @endif
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_all_potongan) }}</th>
                <th style="border: 1px solid #000000;"></th>
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_upah_lembur) }}</th>
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_penambah) }}</th>
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_pengurang) }}</th>
                <th style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold;">{{ formatAngka($total_gaji_bersih) }}</th>
            </tr>
        </tbody>
    </table>
</body>
</html>
