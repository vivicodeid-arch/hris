@inject('pph21Service', 'App\Services\Pph21Service')
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Slip Gaji {{ date('Y-m-d H:i:s') }}</title>
    <style>
        body {
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            margin: 0;
            padding: 20px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
        }

        .slip-card {
            width: 320px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.025);
            padding: 20px;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .slip-header {
            border-bottom: 1.5px dashed #cbd5e1;
            padding-bottom: 12px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-info {
            text-align: left;
        }

        .company-name {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
        }

        .company-sub {
            font-size: 9px;
            color: #64748b;
            font-weight: 500;
        }

        .slip-title-block {
            text-align: right;
        }

        .slip-title {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
        }

        .slip-periode {
            font-size: 9px;
            color: #475569;
            font-weight: 600;
            margin-top: 2px;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        .employee-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1.5px dashed #cbd5e1;
        }

        .employee-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .employee-label {
            font-size: 8px;
            font-weight: 700;
            color: #94a3b8;
        }

        .employee-value {
            font-size: 11px;
            font-weight: 600;
            color: #334155;
        }

        .work-summary-badge {
            grid-column: span 2;
            border-top: 1.5px dashed #e2e8f0;
            padding-top: 6px;
            margin-top: 2px;
            font-size: 9px;
            color: #64748b;
            font-weight: 500;
            line-height: 1.3;
        }

        .table-section {
            margin-bottom: 12px;
        }

        .section-header {
            font-size: 10px;
            font-weight: 700;
            padding: 4px 0;
            margin-bottom: 6px;
            border-bottom: 1px solid #cbd5e1;
        }

        .section-header.earning {
            color: #047857;
        }

        .section-header.deduction {
            color: #be123c;
        }

        .section-header.adjustment {
            color: #0369a1;
        }

        .slip-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            border-bottom: 1px dashed #f1f5f9;
            font-size: 11px;
        }

        .slip-row:last-child {
            border-bottom: none;
        }

        .slip-row.subtotal {
            font-weight: 700;
            border-top: 1px dashed #cbd5e1;
            border-bottom: none;
            padding-top: 6px;
            color: #1e293b;
            margin-top: 2px;
        }

        .slip-row-label {
            color: #475569;
        }

        .slip-row-value {
            font-weight: 600;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
        }

        .net-salary-card {
            border-top: 1.5px dashed #cbd5e1;
            border-bottom: 1.5px dashed #cbd5e1;
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 14px 0;
        }

        .net-salary-label {
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
        }

        .net-salary-value {
            font-size: 14px;
            font-weight: 800;
            color: #047857;
            font-variant-numeric: tabular-nums;
        }

        .slip-footer {
            border-top: 1.5px dashed #cbd5e1;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 6px;
        }

        .system-info {
            font-size: 8px;
            color: #94a3b8;
            line-height: 1.3;
        }

        .signature-block {
            text-align: center;
            min-width: 100px;
        }

        .signature-title {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 25px;
        }

        .signature-line {
            border-bottom: 1px solid #cbd5e1;
            width: 100%;
            margin-bottom: 2px;
        }

        .signature-subtitle {
            font-size: 7px;
            color: #94a3b8;
            font-weight: 500;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0;
            }

            .container {
                gap: 10px;
            }

            .slip-card {
                border: 1px solid #e2e8f0 !important;
                box-shadow: none !important;
                margin-bottom: 10px;
            }

            .net-salary-card {
                border: 1px solid #0f172a !important;
                color: #0f172a !important;
                background: #f8fafc !important;
            }

            .net-salary-value {
                color: #0f172a !important;
            }
        }

        @media (max-width: 768px) {
            .container {
                justify-content: center;
            }

            .slip-card {
                width: 100%;
                max-width: 340px;
            }
        }
    </style>
</head>

<body>
    @php
        $isPphActive = $pph21Service->isAktif();
        $pphSetting = $isPphActive ? $pph21Service->getSetting() : null;
        
        $bulanNum = $bulan ?? request('bulan') ?? date('m', strtotime($periode_dari));
        $tahunNum = $tahun ?? request('tahun') ?? date('Y', strtotime($periode_dari));
        $slipGajiRecord = DB::table('slip_gaji')
            ->where('bulan', $bulanNum)
            ->where('tahun', $tahunNum)
            ->first();
        $kodeSlipGaji = $slipGajiRecord ? $slipGajiRecord->kode_slip_gaji : null;
        $isPublished = $slipGajiRecord && $slipGajiRecord->status == 1;
        $janNovStats = $janNovStatsAll ?? collect();
    @endphp

    <!-- Container untuk layout horizontal -->
    <div class="container">
        @foreach ($laporan_presensi as $d)
            @php
                $tanggal_presensi = $periode_dari;
                $total_denda = 0;
                $total_potongan_jam = 0;
                $total_tunjangan = 0;
                $total_jam_lembur_aktual = 0;
                $total_jam_netto_lembur = 0;
                $total_nominal_lembur_snapshot = 0;
                $has_lembur_snapshot = false;
                $lemburKhusus = $lembur_khusus_map[$d['nik']] ?? null;

                // Mapping jadwal untuk NIK ini dari berbagai sumber (sama seperti presensi_cetak & gaji_cetak)
                $mapJadwalByDate = $jadwal_bydate[$d['nik']] ?? [];
                $mapJadwalGrupByDate = $jadwal_grup_bydate[$d['nik']] ?? [];
                $mapJadwalByDay = $jadwal_byday[$d['nik']] ?? [];

                // Kalkulasi tunjangan
                foreach ($jenis_tunjangan as $j) {
                    $total_tunjangan += $d[$j->kode_jenis_tunjangan];
                }

                // Kalkulasi upah per jam
                $upah_perjam = $d['gaji_pokok'] / $generalsetting->total_jam_bulan;
            @endphp

            {{-- Proses kalkulasi denda dan potongan jam --}}
            @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                @php
                    $denda = 0;
                    $potongan_jam = 0;

                    // Optimized: Check libur using pre-loaded data (no DB query)
                    $is_libur_nasional = isset($libur_nasional_dates[$tanggal_presensi]);
                    if ($is_libur_nasional) {
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

                    // O(1) libur lookup
                    $libur_key = $d['nik'] . '|' . $tanggal_presensi;
                    $ceklibur = $datalibur_indexed[$libur_key] ?? ($datalibur_by_tanggal[$tanggal_presensi] ?? []);
                @endphp

                @if (isset($d[$tanggal_presensi]))
                    @if ($d[$tanggal_presensi]['status'] == 'h')
                        @php
                            $jam_masuk = $tanggal_presensi . ' ' . $d[$tanggal_presensi]['jam_masuk'];
                            $terlambat = hitungjamterlambat($d[$tanggal_presensi]['jam_in'], $jam_masuk);

                            // Jika denda sudah dikunci di database, gunakan nilai tersebut
                            $denda_dari_db =
                                isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;

                            if ($denda_dari_db !== null) {
                                // Denda sudah dikunci, gunakan dari DB
                                $denda = $denda_dari_db;

                                // Potongan jam tetap dihitung dengan rumus
                                if ($terlambat != null) {
                                    if ($terlambat['desimal_terlambat'] < 1) {
                                        $potongan_jam_terlambat = 0;
                                    } else {
                                        $potongan_jam_terlambat =
                                            $terlambat['desimal_terlambat'] > $d[$tanggal_presensi]['total_jam']
                                                ? $d[$tanggal_presensi]['total_jam']
                                                : $terlambat['desimal_terlambat'];
                                    }
                                } else {
                                    $potongan_jam_terlambat = 0;
                                }
                            } else {
                                // Belum dikunci → gunakan rumus hitungdenda seperti biasa
                                if ($terlambat != null) {
                                    if ($terlambat['desimal_terlambat'] < 1) {
                                        $potongan_jam_terlambat = 0;
                                        $denda = hitungdenda($denda_list, $terlambat['menitterlambat']);
                                    } else {
                                        $potongan_jam_terlambat =
                                            $terlambat['desimal_terlambat'] > $d[$tanggal_presensi]['total_jam']
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
                                $d[$tanggal_presensi]['lintashari'],
                            );
                            $pulangcepat = $pulangcepat > $d[$tanggal_presensi]['total_jam'] ? $d[$tanggal_presensi]['total_jam'] : $pulangcepat;
                            $potongan_tidak_absen_masuk_atau_pulang =
                                empty($d[$tanggal_presensi]['jam_out']) || empty($d[$tanggal_presensi]['jam_in'])
                                    ? $d[$tanggal_presensi]['total_jam']
                                    : 0;
                            $potongan_istirahat = hitungPotonganIstirahat(
                                $d[$tanggal_presensi]['istirahat_out'],
                                $d[$tanggal_presensi]['istirahat_in'],
                                $d[$tanggal_presensi]['jam_awal_istirahat'],
                                $d[$tanggal_presensi]['jam_akhir_istirahat']
                            );
                            $status_potongan_istirahat = $d[$tanggal_presensi]['status_potongan_istirahat'] ?? $generalsetting->potongan_istirahat;
                            $potongan_jam =
                                $potongan_tidak_absen_masuk_atau_pulang == 0
                                    ? $pulangcepat + $potongan_jam_terlambat + ($status_potongan_istirahat == 1 ? $potongan_istirahat : 0)
                                    : $potongan_tidak_absen_masuk_atau_pulang;
                        @endphp
                    @elseif($d[$tanggal_presensi]['status'] == 'i')
                        @php
                            $potongan_jam = $d[$tanggal_presensi]['total_jam'];

                            // Izin: jika denda sudah dikunci, ambil dari DB, jika tidak 0
                            $denda_dari_db =
                                isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;
                            $denda = $denda_dari_db !== null ? $denda_dari_db : 0;
                        @endphp
                    @elseif($d[$tanggal_presensi]['status'] == 'a')
                        @php
                            $potongan_jam = $d[$tanggal_presensi]['total_jam'];

                            // Alpa: jika denda sudah dikunci, ambil dari DB, jika tidak 0
                            $denda_dari_db =
                                isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;
                            $denda = $denda_dari_db !== null ? $denda_dari_db : 0;
                        @endphp
                    @endif
                @else
                    @php
                        // Tidak ada data presensi di tanggal ini
                        // Jika hari libur, tidak ada potongan jam
                        if (empty($ceklibur)) {
                            // Bukan libur → cek jadwal berurutan (sama seperti presensi_cetak & gaji_cetak)
                            // 1) Jadwal by-date per karyawan
                            $totalJamJadwal = $mapJadwalByDate[$tanggal_presensi] ?? null;

                            // 2) Kalau kosong, cek jadwal grup by-date
                            if ($totalJamJadwal === null) {
                                $totalJamJadwal = $mapJadwalGrupByDate[$tanggal_presensi] ?? null;
                            }

                            // 3) Kalau masih kosong, cek jadwal by-day per karyawan
                            if ($totalJamJadwal === null) {
                                $nama_hari = getHari($tanggal_presensi);
                                $totalJamJadwal = $mapJadwalByDay[$nama_hari] ?? null;
                            }

                            // 4) Kalau masih kosong, cek jadwal by-day per departemen & cabang
                            if ($totalJamJadwal === null) {
                                $nama_hari = isset($nama_hari) ? $nama_hari : getHari($tanggal_presensi);
                                $keyDeptCabang = $d['kode_dept'] . '|' . $d['kode_cabang'];
                                $mapDept = $jadwal_bydept[$keyDeptCabang] ?? [];
                                $totalJamJadwal = $mapDept[$nama_hari] ?? null;
                            }

                            // Jika ada jadwal tapi tidak ada presensi sama sekali → potongan jam = total_jam jadwal
                            $is_future = strtotime($tanggal_presensi) > strtotime(date('Y-m-d'));
                            if ($totalJamJadwal !== null && !$is_future) {
                                $potongan_jam = is_array($totalJamJadwal) ? $totalJamJadwal['total_jam'] : $totalJamJadwal;
                            }
                        }
                    @endphp
                @endif

                @php
                    $status_potongan_harian = isset($d[$tanggal_presensi]['status_potongan']) ? $d[$tanggal_presensi]['status_potongan'] : $generalsetting->status_potongan_jam;
                    if ($status_potongan_harian == 0) {
                        $potongan_jam = 0;
                    }
                    $total_denda += $denda;
                    $total_potongan_jam += $potongan_jam;
                    $total_jam_lembur_aktual += $jml_jam_lembur;
                    $total_jam_netto_lembur += $jam_netto_harian;
                    $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                @endphp
            @endwhile

            @php
                // Final calculations
                if ($total_potongan_jam > $generalsetting->total_jam_bulan) {
                    $total_potongan_jam = $generalsetting->total_jam_bulan;
                }
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

                        $pphResult = [
                            'status_aktif' => true,
                            'metode' => $pphSetting->metode,
                            'metode_tanggungan' => $active_metode_tanggungan,
                            'bruto' => 0,
                            'detail_komponen' => [],
                            'kategori_ter' => 'A',
                            'tarif_ter_persen' => 0,
                            'biaya_jabatan' => 0,
                            'ptkp' => 0,
                            'pkp_setahun' => 0,
                            'pph21_terutang' => 0,
                            'pph21_ditanggung_perusahaan' => 0,
                            'bulan' => (int)$bulanNum
                        ];

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
                            if ($bulanNum == 12 && isset($janNovStats[$d['nik']])) {
                                $totalPphJanNov = $janNovStats[$d['nik']]->total_pph ?? 0;
                                $totalBrutoJanNov = $janNovStats[$d['nik']]->total_bruto ?? 0;
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

                        // Simpan snapshot jika slip status = Published (1)
                        if ($isPublished && $kodeSlipGaji) {
                            $pph21Service->simpanSnapshot($kodeSlipGaji, $d['nik'], $d['kode_status_kawin'] ?? null, $pphResult);
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

                $total_potongan = ROUND($jumlah_potongan_jam) + $total_denda + $d['bpjs_kesehatan'] + $d['bpjs_tenagakerja'] + ($d['cicilan_pinjaman'] ?? 0) + $potongan_pph21;
                $bruto_total = $d['gaji_pokok'] + $total_tunjangan + $tunjangan_pajak + ROUND($upah_lembur);
                $gaji_bersih = $d['gaji_pokok'] + $total_tunjangan + $tunjangan_pajak - $total_potongan + $d['penambah'] - $d['pengurang'] + ROUND($upah_lembur);
            @endphp

            <div class="slip-card">
                <!-- Header -->
                <div class="slip-header">
                    <div class="company-info">
                        <div class="company-name">{{ $generalsetting->nama_perusahaan }}</div>
                        <div class="company-sub">Sistem Payroll Modern</div>
                    </div>
                    <div class="slip-title-block">
                        <div class="slip-title">Slip Gaji</div>
                        <div class="slip-periode">{{ date('d/m/Y', strtotime($periode_dari)) }} - {{ date('d/m/Y', strtotime($periode_sampai)) }}</div>
                    </div>
                </div>

                <!-- Employee Info -->
                <div class="employee-grid">
                    <div class="employee-item">
                        <span class="employee-label">NIK</span>
                        <span class="employee-value">{{ $d['nik_show'] ?? $d['nik'] }}</span>
                    </div>
                    <div class="employee-item">
                        <span class="employee-label">No Rekening</span>
                        <span class="employee-value">{{ $d['no_rekening'] ?? '-' }} {{ !empty($d['nama_bank']) ? '(' . strtoupper($d['nama_bank']) . ')' : '' }}</span>
                    </div>
                    <div class="employee-item">
                        <span class="employee-label">Nama Rekening</span>
                        <span class="employee-value">{{ $d['nama_rekening'] ?? '-' }}</span>
                    </div>
                    <div class="employee-item">
                        <span class="employee-label">Nama Karyawan</span>
                        <span class="employee-value">{{ $d['nama_karyawan'] }}</span>
                    </div>
                    <div class="employee-item">
                        <span class="employee-label">Jabatan</span>
                        <span class="employee-value">{{ $d['nama_jabatan'] }}</span>
                    </div>
                    <div class="employee-item">
                        <span class="employee-label">Departemen</span>
                        <span class="employee-value">{{ $d['kode_dept'] }}</span>
                    </div>
                    @if ($generalsetting->show_rate_slip ?? true)
                    <div class="work-summary-badge">
                        <div style="display: flex; justify-content: space-between; width: 100%;">
                            <span>Jadwal Pokok: {{ $generalsetting->total_jam_bulan }} jam</span>
                            <span>Rate: Rp {{ number_format($upah_perjam, 0, ',', '.') }}/jam</span>
                            <span>Potongan: {{ number_format($total_potongan_jam, 2) }} jam</span>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Penghasilan -->
                <div class="table-section">
                    <div class="section-header earning">Penghasilan</div>
                    <div class="slip-row">
                        <span class="slip-row-label">Gaji Pokok</span>
                        <span class="slip-row-value">Rp {{ number_format($d['gaji_pokok'], 0, ',', '.') }}</span>
                    </div>
                    @foreach ($jenis_tunjangan as $j)
                        @if ($d[$j->kode_jenis_tunjangan] > 0)
                            <div class="slip-row">
                                <span class="slip-row-label">{{ $j->jenis_tunjangan }}</span>
                                <span class="slip-row-value">Rp {{ number_format($d[$j->kode_jenis_tunjangan], 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if ($isPphActive && $active_metode_tanggungan === 'GROSS_UP' && $tunjangan_pajak > 0)
                        <div class="slip-row">
                            <span class="slip-row-label">Tunjangan Pajak (PPh 21)</span>
                            <span class="slip-row-value">Rp {{ number_format($tunjangan_pajak, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($total_jam_netto_lembur > 0 || $total_jam_lembur_aktual > 0)
                        <div class="slip-row">
                            <span class="slip-row-label">
                                <a href="{{ route('laporan.lemburdetail', [$d['nik'], $periode_dari, $periode_sampai]) }}" target="_blank" style="color: #0ea5e9; text-decoration: underline; font-weight: 500;">
                                    @if ($lemburKhusus)
                                        Lembur ({{ formatAngkaDesimal($total_jam_lembur_aktual) }} jam) ★
                                    @else
                                        Lembur ({{ formatAngkaDesimal($total_jam_netto_lembur) }} jam)
                                    @endif
                                </a>
                            </span>
                            <span class="slip-row-value">
                                Rp {{ number_format($upah_lembur, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif
                    <div class="slip-row subtotal">
                        <span class="slip-row-label">Total Penghasilan (Bruto)</span>
                        <span class="slip-row-value">Rp {{ number_format($bruto_total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Potongan -->
                <div class="table-section">
                    <div class="section-header deduction">Potongan</div>
                    @if ($total_denda > 0)
                        <div class="slip-row">
                            <span class="slip-row-label">Denda Keterlambatan</span>
                            <span class="slip-row-value">Rp {{ number_format($total_denda, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($jumlah_potongan_jam > 0)
                        <div class="slip-row">
                            <span class="slip-row-label">Potongan Jam Kerja ({{ number_format($total_potongan_jam, 2) }} jam)</span>
                            <span class="slip-row-value">Rp {{ number_format($jumlah_potongan_jam, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($d['bpjs_kesehatan'] > 0)
                        <div class="slip-row">
                            <span class="slip-row-label">BPJS Kesehatan</span>
                            <span class="slip-row-value">Rp {{ number_format($d['bpjs_kesehatan'], 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($d['bpjs_tenagakerja'] > 0)
                        <div class="slip-row">
                            <span class="slip-row-label">BPJS Ketenagakerjaan</span>
                            <span class="slip-row-value">Rp {{ number_format($d['bpjs_tenagakerja'], 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if (($d['cicilan_pinjaman'] ?? 0) > 0)
                        <div class="slip-row">
                            <span class="slip-row-label">Cicilan Pinjaman</span>
                            <span class="slip-row-value">Rp {{ number_format($d['cicilan_pinjaman'], 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($isPphActive && $potongan_pph21 > 0)
                        <div class="slip-row">
                            <span class="slip-row-label">PPh 21</span>
                            <span class="slip-row-value">Rp {{ number_format($potongan_pph21, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="slip-row subtotal">
                        <span class="slip-row-label">Total Potongan</span>
                        <span class="slip-row-value">Rp {{ number_format($total_potongan, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Penyesuaian -->
                @if ($d['penambah'] > 0 || $d['pengurang'] > 0)
                    <div class="table-section">
                        <div class="section-header adjustment">Penyesuaian</div>
                        @if ($d['penambah'] > 0)
                            <div class="slip-row">
                                <span class="slip-row-label">Tunjangan Penambah (Lain-lain)</span>
                                <span class="slip-row-value">Rp {{ number_format($d['penambah'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if ($d['pengurang'] > 0)
                            <div class="slip-row">
                                <span class="slip-row-label">Potongan Pengurang (Lain-lain)</span>
                                <span class="slip-row-value">Rp {{ number_format($d['pengurang'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if(!empty($d['keterangan_penyesuaian']))
                            <div style="font-size: 9px; color: #64748b; margin-top: 4px; border-top: 1px dashed #f1f5f9; padding-top: 4px;">
                                <strong>Keterangan:</strong> {{ $d['keterangan_penyesuaian'] }}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Total -->
                <div class="net-salary-card">
                    <span class="net-salary-label">Gaji Bersih (Take Home Pay)</span>
                    <span class="net-salary-value">
                        Rp {{ number_format($gaji_bersih, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Footer -->
                <div class="slip-footer">
                    <div class="system-info">
                        Dicetak: {{ date('d/m/Y H:i') }}<br>
                        Sistem Payroll Premium v2.0
                    </div>
                    <div class="signature-block">
                        <div class="signature-title">Manager Payroll</div>
                        <div class="signature-line"></div>
                        <div class="signature-subtitle">{{ $generalsetting->nama_perusahaan }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>
