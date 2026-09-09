<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Pengumuman;
use App\Models\User;
use App\Models\Userkaryawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Fetch mobile dashboard data.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $userKaryawan = $user->userkaryawan;
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $nik = $userKaryawan->nik;
        $hari_ini = Carbon::now(config('app.timezone'))->format('Y-m-d');

        // 1. Get today's attendance status
        $presensi = Presensi::where('nik', $nik)
            ->where('tanggal', $hari_ini)
            ->first();

        // 2. Get monthly recap stats
        $rekap = Presensi::select(
            DB::raw("SUM(IF(status='h', 1, 0)) as hadir"),
            DB::raw("SUM(IF(status='i', 1, 0)) as izin"),
            DB::raw("SUM(IF(status='s', 1, 0)) as sakit"),
            DB::raw("SUM(IF(status='c', 1, 0)) as cuti"),
            DB::raw("SUM(IF(status='a', 1, 0)) as alpa")
        )
            ->where('nik', $nik)
            ->whereRaw('MONTH(tanggal) = MONTH(?)', [$hari_ini])
            ->whereRaw('YEAR(tanggal) = YEAR(?)', [$hari_ini])
            ->groupBy('nik')
            ->first();

        // 3. Check for contract warning (ends within 30 days)
        $kontrak = DB::table('kontrak')
            ->where('nik', $nik)
            ->where('status_kontrak', '1')
            ->where('jenis_kontrak', '!=', 'T')
            ->orderBy('sampai', 'desc')
            ->first();

        $notif_kontrak = null;
        if ($kontrak) {
            $tgl_akhir = Carbon::parse($kontrak->sampai);
            $today = Carbon::now(config('app.timezone'));
            $sisa_hari = $today->diffInDays($tgl_akhir, false);

            if ($sisa_hari >= 0 && $sisa_hari <= 30) {
                $notif_kontrak = [
                    'sisa_hari' => $sisa_hari,
                    'tanggal_akhir' => $tgl_akhir->translatedFormat('d F Y')
                ];
            }
        }

        // 4. Check for active SP (discipline warning)
        $todayStr = Carbon::now(config('app.timezone'))->toDateString();
        $notif_sp = DB::table('pelanggaran')
            ->where('nik', $nik)
            ->where('dari', '<=', $todayStr)
            ->where('sampai', '>=', $todayStr)
            ->first();

        // 5. Get latest announcement
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->first();

        // 6. Check if it's user's birthday
        $karyawan = $userKaryawan->karyawan;
        $is_birthday = false;
        $umur = null;
        if ($karyawan && $karyawan->tanggal_lahir) {
            $tanggalLahir = Carbon::parse($karyawan->tanggal_lahir);
            $today = Carbon::now();
            if ($tanggalLahir->month == $today->month && $tanggalLahir->day == $today->day) {
                $is_birthday = true;
                $umur = $tanggalLahir->age;
            }
        }

        // 7. Get Branch & Active Jam Kerja for GPS presence
        $cabang = null;
        $jamkerja = null;
        if ($karyawan) {
            $cabang = \App\Models\Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
            $general_setting = \App\Models\Pengaturanumum::where('id', 1)->first();
            $timezone_cabang = $cabang->timezone ?? $general_setting->timezone ?? config('app.timezone');
            $carbon_now = Carbon::now($timezone_cabang);
            $hariini = $carbon_now->format('Y-m-d');
            $jamsekarang = $carbon_now->format('H:i');
            $tgl_sebelumnya = $carbon_now->copy()->subDay()->format('Y-m-d');
            
            $cekpresensi_sebelumnya = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('tanggal', $tgl_sebelumnya)
                ->where('nik', $karyawan->nik)
                ->first();

            $ceklintashari_presensi = $cekpresensi_sebelumnya != null ? $cekpresensi_sebelumnya->lintashari : 0;

            if ($ceklintashari_presensi == 1 && ($cekpresensi_sebelumnya->jam_out == null)) {
                $batas_lh = $cekpresensi_sebelumnya->batas_presensi_pulang ?? $general_setting->batas_presensi_lintashari;
                if ($jamsekarang < $batas_lh) {
                    $hariini = $tgl_sebelumnya;
                }
            }

            $day = date('D', strtotime($hariini));
            $namahari = '';
            if ($day == 'Sun') {
                $namahari = 'minggu';
            } else if ($day == 'Mon') {
                $namahari = 'senin';
            } else if ($day == 'Tue') {
                $namahari = 'selasa';
            } else if ($day == 'Wed') {
                $namahari = 'rabu';
            } else if ($day == 'Thu') {
                $namahari = 'kamis';
            } else if ($day == 'Fri') {
                $namahari = 'jumat';
            } else if ($day == 'Sat') {
                $namahari = 'sabtu';
            }

            $kode_dept = $karyawan->kode_dept;

            // Cek Ajuan Jadwal yang sudah disetujui
            $ajuan_jadwal = \App\Models\AjuanJadwal::where('nik', $karyawan->nik)
                ->where('tanggal', $hariini)
                ->where('status', 'a')
                ->first();

            if ($ajuan_jadwal) {
                $jamkerja = \App\Models\Jamkerja::where('kode_jam_kerja', $ajuan_jadwal->kode_jam_kerja_tujuan)->first();
            } else {
                // Cek Jam Kerja By Date
                $jamkerja = \App\Models\Setjamkerjabydate::join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                    ->where('nik', $karyawan->nik)
                    ->where('tanggal', $hariini)
                    ->first();

                if ($jamkerja == null) {
                    // Cek Jam Kerja Grup
                    $cek_group = \App\Models\GrupDetail::where('nik', $karyawan->nik)->first();
                    if ($cek_group) {
                        $jamkerja = \App\Models\GrupJamkerjaBydate::where('kode_grup', $cek_group->kode_grup)
                            ->where('tanggal', $hariini)
                            ->join('presensi_jamkerja', 'grup_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                            ->first();
                    }

                    if ($jamkerja == null) {
                        // Cek Jam Kerja harian
                        $jamkerja = \App\Models\Setjamkerjabyday::join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                            ->where('nik', $karyawan->nik)->where('hari', $namahari)->first();
                    }

                    if ($jamkerja == null) {
                        // Cek Jam Kerja by Dept
                        $jamkerja = \App\Models\Detailsetjamkerjabydept::join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
                            ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                            ->where('kode_dept', $kode_dept)
                            ->where('kode_cabang', $karyawan->kode_cabang)
                            ->where('hari', $namahari)->first();
                    }

                    if ($jamkerja == null) {
                        // Fallback Jadwal Kerja Global
                        if ($general_setting && $general_setting->global_jamkerja_aktif) {
                            $globalJk = \App\Models\GlobalJamkerja::where('hari', $namahari)->first();
                            if ($globalJk && $globalJk->kode_jam_kerja) {
                                $jamkerja = \App\Models\Jamkerja::where('kode_jam_kerja', $globalJk->kode_jam_kerja)->first();
                            }
                        }
                    }
                }
            }
        }

        // 8. Get presence history
        $datapresensi = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('presensi.nik', $nik)
            ->leftJoin('presensi_izinabsen_approve', 'presensi.id', '=', 'presensi_izinabsen_approve.id_presensi')
            ->leftJoin('presensi_izinabsen', 'presensi_izinabsen_approve.kode_izin', '=', 'presensi_izinabsen.kode_izin')
            ->leftJoin('presensi_izinsakit_approve', 'presensi.id', '=', 'presensi_izinsakit_approve.id_presensi')
            ->leftJoin('presensi_izinsakit', 'presensi_izinsakit_approve.kode_izin_sakit', '=', 'presensi_izinsakit.kode_izin_sakit')
            ->leftJoin('presensi_izincuti_approve', 'presensi.id', '=', 'presensi_izincuti_approve.id_presensi')
            ->leftJoin('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti')
            ->leftJoin('mesin_fingerprints', 'presensi.id_mesin', '=', 'mesin_fingerprints.id')
            ->select(
                'presensi.*',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.total_jam',
                'presensi_jamkerja.lintashari',
                'presensi_izinabsen.keterangan as keterangan_izin',
                'presensi_izinsakit.keterangan as keterangan_izin_sakit',
                'presensi_izincuti.keterangan as keterangan_izin_cuti',
                'mesin_fingerprints.nama_mesin'
            )
            ->orderBy('presensi.tanggal', 'desc')
            ->limit(30)
            ->get();

        $history = [];
        foreach ($datapresensi as $d) {
            $keterangan = null;
            if ($d->status == 'i') {
                $keterangan = 'Izin: ' . $d->keterangan_izin;
            } else if ($d->status == 's') {
                $keterangan = 'Sakit: ' . $d->keterangan_izin_sakit;
            } else if ($d->status == 'c') {
                $keterangan = 'Cuti: ' . $d->keterangan_izin_cuti;
            } else if ($d->status == 'a') {
                $keterangan = 'Alpha';
            } else {
                $keterangan = 'Hadir';
            }

            $history[] = [
                'id' => $d->id,
                'tanggal' => $d->tanggal,
                'jam_in' => $d->jam_in ? date('H:i', strtotime($d->jam_in)) : null,
                'jam_out' => $d->jam_out ? date('H:i', strtotime($d->jam_out)) : null,
                'status' => $d->status,
                'nama_jam_kerja' => $d->nama_jam_kerja,
                'jam_masuk' => $d->jam_masuk ? date('H:i', strtotime($d->jam_masuk)) : null,
                'jam_pulang' => $d->jam_pulang ? date('H:i', strtotime($d->jam_pulang)) : null,
                'keterangan' => $keterangan,
                'foto_in' => $d->foto_in ? asset('storage/uploads/absensi/' . $d->foto_in) : null,
                'foto_out' => $d->foto_out ? asset('storage/uploads/absensi/' . $d->foto_out) : null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'history' => $history,
                'presensi' => $presensi ? [
                    'jam_in' => $presensi->jam_in ? date('H:i', strtotime($presensi->jam_in)) : null,
                    'jam_out' => $presensi->jam_out ? date('H:i', strtotime($presensi->jam_out)) : null,
                    'foto_in' => $presensi->foto_in ? asset('storage/uploads/absensi/' . $presensi->foto_in) : null,
                    'foto_out' => $presensi->foto_out ? asset('storage/uploads/absensi/' . $presensi->foto_out) : null,
                    'istirahat_out' => $presensi->istirahat_out ? date('H:i', strtotime($presensi->istirahat_out)) : null,
                    'istirahat_in' => $presensi->istirahat_in ? date('H:i', strtotime($presensi->istirahat_in)) : null,
                    'foto_istirahat_out' => $presensi->foto_istirahat_out ? asset('storage/uploads/istirahat/' . $presensi->foto_istirahat_out) : null,
                    'foto_istirahat_in' => $presensi->foto_istirahat_in ? asset('storage/uploads/istirahat/' . $presensi->foto_istirahat_in) : null,
                ] : null,
                'rekap' => [
                    'hadir' => (int) ($rekap->hadir ?? 0),
                    'sakit' => (int) ($rekap->sakit ?? 0),
                    'izin' => (int) ($rekap->izin ?? 0),
                    'cuti' => (int) ($rekap->cuti ?? 0),
                    'alpa' => (int) ($rekap->alpa ?? 0),
                ],
                'is_birthday' => $is_birthday,
                'umur' => $umur,
                'notif_kontrak' => $notif_kontrak,
                'notif_sp' => $notif_sp ? [
                    'id' => $notif_sp->no_sp,
                    'jenis_sp' => $notif_sp->jenis_sp ?? 'Peringatan Disiplin',
                    'sampai' => Carbon::parse($notif_sp->sampai)->translatedFormat('d F Y'),
                ] : null,
                'pengumuman' => $pengumuman ? [
                    'id' => $pengumuman->id,
                    'judul' => $pengumuman->judul,
                    'isi' => strip_tags($pengumuman->isi),
                    'created_at' => Carbon::parse($pengumuman->created_at)->translatedFormat('d F Y'),
                ] : null,
                'cabang' => $cabang ? [
                    'nama_cabang' => $cabang->nama_cabang,
                    'lokasi_cabang' => $cabang->lokasi_cabang,
                    'radius_cabang' => (int) $cabang->radius_cabang,
                ] : null,
                'lock_location' => $karyawan ? (int) $karyawan->lock_location : 1,
                'jam_kerja' => $jamkerja ? [
                    'kode_jam_kerja' => $jamkerja->kode_jam_kerja,
                    'nama_jam_kerja' => $jamkerja->nama_jam_kerja,
                    'jam_masuk' => $jamkerja->jam_masuk ? date('H:i', strtotime($jamkerja->jam_masuk)) : null,
                    'jam_pulang' => $jamkerja->jam_pulang ? date('H:i', strtotime($jamkerja->jam_pulang)) : null,
                    'lintashari' => (int) $jamkerja->lintashari,
                    'istirahat' => (int) ($jamkerja->istirahat ?? 0),
                    'jam_awal_istirahat' => $jamkerja->jam_awal_istirahat ? date('H:i', strtotime($jamkerja->jam_awal_istirahat)) : null,
                    'jam_akhir_istirahat' => $jamkerja->jam_akhir_istirahat ? date('H:i', strtotime($jamkerja->jam_akhir_istirahat)) : null,
                ] : null,
                'general_setting' => \App\Models\Pengaturanumum::where('id', 1)->first() ? [
                    'nama_perusahaan' => \App\Models\Pengaturanumum::where('id', 1)->first()->nama_perusahaan,
                    'logo' => \App\Models\Pengaturanumum::where('id', 1)->first()->logo ? asset('storage/logo/' . \App\Models\Pengaturanumum::where('id', 1)->first()->logo) : null,
                    'alamat' => \App\Models\Pengaturanumum::where('id', 1)->first()->alamat,
                    'absen_istirahat' => (\App\Models\Pengaturanumum::where('id', 1)->first()->absen_istirahat == 1 && \App\Models\KaryawanMenuSetting::isActive('absen_istirahat')) ? 1 : 0,
                    'mobile_theme_scheme' => \App\Models\Pengaturanumum::where('id', 1)->first()->mobile_theme_scheme ?? 'green',
                ] : null,
            ]
        ]);
    }

    public function mySchedule(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        
        $user = $request->user();
        $userkaryawan = $user->userkaryawan;
        if (!$userkaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }
        $nik = $userkaryawan->nik;
        
        $karyawan = \App\Models\Karyawan::where('nik', $nik)->first();
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.'
            ], 404);
        }

        $periode_dari = $tahun . '-' . $bulan . '-01';
        $periode_sampai = date('Y-m-t', strtotime($periode_dari));

        $jadwal_bydate_raw = DB::table('presensi_jamkerja_bydate')
            ->join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi_jamkerja_bydate.tanggal',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            )
            ->where('presensi_jamkerja_bydate.nik', $nik)
            ->whereBetween('presensi_jamkerja_bydate.tanggal', [$periode_dari, $periode_sampai])
            ->get();

        $jadwal_ajuan_raw = DB::table('ajuan_jadwal')
            ->join('presensi_jamkerja', 'ajuan_jadwal.kode_jam_kerja_tujuan', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'ajuan_jadwal.tanggal',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            )
            ->where('ajuan_jadwal.nik', $nik)
            ->where('ajuan_jadwal.status', 'a')
            ->whereBetween('ajuan_jadwal.tanggal', [$periode_dari, $periode_sampai])
            ->get();

        $jadwal_bydate = $jadwal_bydate_raw->concat($jadwal_ajuan_raw)->keyBy('tanggal');

        $jadwal_grup_bydate = DB::table('grup_detail')
            ->join('grup_jamkerja_bydate', 'grup_detail.kode_grup', '=', 'grup_jamkerja_bydate.kode_grup')
            ->join('presensi_jamkerja', 'grup_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'grup_jamkerja_bydate.tanggal',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            )
            ->where('grup_detail.nik', $nik)
            ->whereBetween('grup_jamkerja_bydate.tanggal', [$periode_dari, $periode_sampai])
            ->get()
            ->keyBy('tanggal');

        $jadwal_byday = DB::table('presensi_jamkerja_byday')
            ->join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi_jamkerja_byday.hari',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            )
            ->where('presensi_jamkerja_byday.nik', $nik)
            ->get()
            ->keyBy('hari');

        $jadwal_bydept = DB::table('presensi_jamkerja_bydept_detail')
            ->join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
            ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi_jamkerja_bydept_detail.hari',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            )
            ->where('presensi_jamkerja_bydept.kode_dept', $karyawan->kode_dept)
            ->where('presensi_jamkerja_bydept.kode_cabang', $karyawan->kode_cabang)
            ->get()
            ->keyBy('hari');

        $datalibur = getdatalibur($periode_dari, $periode_sampai);

        $schedule = [];
        $start = Carbon::parse($periode_dari);
        $end = Carbon::parse($periode_sampai);

        while ($start <= $end) {
            $date = $start->format('Y-m-d');
            $dayName = getHari($date);
            $info = null;

            if (isset($jadwal_bydate[$date])) {
                $info = $jadwal_bydate[$date];
            } elseif (isset($jadwal_grup_bydate[$date])) {
                $info = $jadwal_grup_bydate[$date];
            } elseif (isset($jadwal_byday[$dayName])) {
                $info = $jadwal_byday[$dayName];
            } elseif (isset($jadwal_bydept[$dayName])) {
                $info = $jadwal_bydept[$dayName];
            }

            if ($info === null) {
                $generalsetting = \App\Models\Pengaturanumum::where('id', 1)->first();
                if ($generalsetting && $generalsetting->global_jamkerja_aktif) {
                    $globalJk = \App\Models\GlobalJamkerja::where('hari', $dayName)
                        ->join('presensi_jamkerja', 'global_jamkerja.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                        ->select('presensi_jamkerja.nama_jam_kerja', 'presensi_jamkerja.jam_masuk', 'presensi_jamkerja.jam_pulang', 'presensi_jamkerja.color')
                        ->first();
                    if ($globalJk) {
                        $info = $globalJk;
                    }
                }
            }

            foreach ($datalibur as $libur) {
                if ($date == $libur['tanggal'] && !empty($libur['nik']) && $libur['nik'] == $nik) {
                    $info = (object)[
                        'nama_jam_kerja' => 'LIBUR - ' . $libur['keterangan'],
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'color' => '#ef4444'
                    ];
                    break;
                }
            }

            $isToday = $date == date('Y-m-d');
            $isWeekend = $start->isWeekend();
            
            $schedule[] = [
                'tanggal' => $date,
                'hari' => $dayName,
                'nama_jam_kerja' => $info ? $info->nama_jam_kerja : 'Tidak ada jadwal',
                'jam_masuk' => $info && $info->jam_masuk ? date('H:i', strtotime($info->jam_masuk)) : null,
                'jam_pulang' => $info && $info->jam_pulang ? date('H:i', strtotime($info->jam_pulang)) : null,
                'color' => $info && isset($info->color) && !empty($info->color) ? $info->color : ($isWeekend || ($info && str_contains(strtolower($info->nama_jam_kerja), 'libur')) ? '#ef4444' : '#10b981'),
                'is_today' => $isToday,
                'is_holiday' => $isWeekend || ($info && str_contains(strtolower($info->nama_jam_kerja), 'libur')),
            ];

            $start->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => $schedule
        ]);
    }
}

