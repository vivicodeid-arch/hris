<?php

namespace App\Http\Controllers;

use App\Models\Bpjskesehatan;
use App\Models\Bpjstenagakerja;
use App\Models\Cabang;
use App\Models\Denda;
use App\Models\Departemen;
use App\Models\Detailpenyesuaiangaji;
use App\Models\Detailtunjangan;
use App\Models\Gajipokok;
use App\Models\Jenistunjangan;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\PresensiExport;
use App\Exports\GajiExport;
use App\Exports\PresensiKaryawanExport;
use App\Exports\CutiExport;
use App\Exports\JadwalExport;
use App\Exports\ReimbursementExport;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function cuti()
    {
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        $data['cuti'] = \App\Models\Cuti::orderBy('kode_cuti')->get();
        return view('laporan.cuti', $data);
    }

    public function cetakcuti(Request $request)
    {
        $tahun = $request->tahun;
        $kode_cabang = $request->kode_cabang;
        $kode_dept = $request->kode_dept;
        $kode_cuti = $request->kode_cuti;
        $generalsetting = \App\Models\Pengaturanumum::where('id', 1)->first();

        // Get Master Cuti info if specific cuti selected
        $master_cuti = null;
        if (!empty($kode_cuti)) {
            $master_cuti = \App\Models\Cuti::where('kode_cuti', $kode_cuti)->first();
        }

        // Get Employees Query
        $query = Karyawan::query();
        $query->orderBy('nama_karyawan');
        
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($kode_cabang)) {
                if (in_array($kode_cabang, $userCabangs)) {
                    $query->where('kode_cabang', $kode_cabang);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereIn('kode_cabang', $userCabangs);
            }

            if (!empty($kode_dept)) {
                if (in_array($kode_dept, $userDepartemens)) {
                    $query->where('kode_dept', $kode_dept);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereIn('kode_dept', $userDepartemens);
            }
        } else {
            if (!empty($kode_cabang)) {
                $query->where('kode_cabang', $kode_cabang);
            }
            if (!empty($kode_dept)) {
                $query->where('kode_dept', $kode_dept);
            }
        }
        $periode_dari = $tahun . '-01-01';
        $query->where(function($q) use ($periode_dari) {
            $q->where('karyawan.status_aktif_karyawan', 1)
              ->orWhere('karyawan.tanggal_nonaktif', '>=', $periode_dari);
        });
        $karyawan = $query->get();

        // Get Approved Leave Data (Days)
        // Join with Presensi and IzinCuti
        $cuti_data = DB::table('presensi_izincuti_approve')
            ->join('presensi', 'presensi_izincuti_approve.id_presensi', '=', 'presensi.id')
            ->join('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti')
            ->select('presensi.nik', 'presensi.tanggal', 'presensi_izincuti.kode_cuti')
            ->whereRaw('YEAR(presensi.tanggal) = ?', [$tahun])
            ->get();

        // Process data structure
        $rekap_cuti = [];
        foreach ($karyawan as $k) {
            $rekap_cuti[$k->nik] = [
                'nama' => $k->nama_karyawan,
                'nik_show' => $k->nik_show,
                'bulan' => array_fill(1, 12, 0),
                'total_ambil' => 0,
                'sisa' => 0 
            ];
        }

        foreach ($cuti_data as $d) {
            // Check if employee exists in the filtered list
            if (isset($rekap_cuti[$d->nik])) {
                // Filter by specific cuti type if requested
                if (!empty($kode_cuti) && $d->kode_cuti != $kode_cuti) {
                    continue;
                }

                $bulan = (int)date('m', strtotime($d->tanggal));
                $rekap_cuti[$d->nik]['bulan'][$bulan]++;
                $rekap_cuti[$d->nik]['total_ambil']++;
            }
        }
        
        $data['tahun'] = $tahun;
        $data['rekap_cuti'] = $rekap_cuti;
        $data['master_cuti'] = $master_cuti;
        $data['namacabang'] = !empty($kode_cabang) ? Cabang::where('kode_cabang', $kode_cabang)->first()->nama_cabang : 'Semua Cabang';
        $data['namadept'] = !empty($kode_dept) ? Departemen::where('kode_dept', $kode_dept)->first()->nama_dept : 'Semua Departemen';
        $data['jenis_cuti'] = !empty($master_cuti) ? $master_cuti->jenis_cuti : 'Semua Jenis Cuti';
        $data['generalsetting'] = $generalsetting;

        if ($request->has('exportButton')) {
            return Excel::download(new CutiExport($data), 'Laporan Cuti Karyawan ' . $tahun . '.xlsx');
        }
        
        return view('laporan.cetak_cuti', $data);

    }

    public function presensi()
    {
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        return view('laporan.presensi', $data);
    }

    public function gaji()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        return view('laporan.gaji', $data);
    }


    public function cetakpresensi(Request $request)
    {

        $user = User::where('id', Auth::user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $periode_laporan_dari = $generalsetting->periode_laporan_dari;
        $periode_laporan_sampai = $generalsetting->periode_laporan_sampai;
        $periode_laporan_lintas_bulan = $generalsetting->periode_laporan_next_bulan;
        if ($request->periode_laporan == 1) {
            if ($periode_laporan_lintas_bulan == 1) {
                if ($request->bulan == 1) {
                    $bulan_dari = 12;
                    $tahun_dari = $request->tahun - 1;
                } else {
                    $bulan_dari = $request->bulan - 1;
                    $tahun_dari = $request->tahun;
                }
                $bulan_sampai = $request->bulan;
                $tahun_sampai = $request->tahun;
            } elseif ($periode_laporan_lintas_bulan == 2) {
                $bulan_dari = $request->bulan;
                $tahun_dari = $request->tahun;
                if ($request->bulan == 12) {
                    $bulan_sampai = 1;
                    $tahun_sampai = $request->tahun + 1;
                } else {
                    $bulan_sampai = $request->bulan + 1;
                    $tahun_sampai = $request->tahun;
                }
            } else {
                $bulan_dari = $request->bulan;
                $tahun_dari = $request->tahun;
                $bulan_sampai = $request->bulan;
                $tahun_sampai = $request->tahun;
            }

            $bulan_dari = str_pad($bulan_dari, 2, '0', STR_PAD_LEFT);
            $last_day_start = date('t', strtotime($tahun_dari . '-' . $bulan_dari . '-01'));
            $p_dari_val = min($periode_laporan_dari, $last_day_start);
            $p_dari = str_pad($p_dari_val, 2, '0', STR_PAD_LEFT);

            $bulan_sampai = str_pad($bulan_sampai, 2, '0', STR_PAD_LEFT);
            $last_day_end = date('t', strtotime($tahun_sampai . '-' . $bulan_sampai . '-01'));
            $p_sampai_val = min($periode_laporan_sampai, $last_day_end);
            $p_sampai = str_pad($p_sampai_val, 2, '0', STR_PAD_LEFT);

            $periode_dari = $tahun_dari . '-' . $bulan_dari . '-' . $p_dari;
            $periode_sampai = $tahun_sampai . '-' . $bulan_sampai . '-' . $p_sampai;
        } elseif ($request->periode_laporan == 2) {
            // Menambahkan nol di depan bulan jika bulan kurang dari 10

            $bulan = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
            $periode_dari = $request->tahun . '-' . $bulan . '-01';
            $periode_sampai = date('Y-m-t', strtotime($periode_dari));
        } else {
            $periode_dari = $request->dari;
            $periode_sampai = $request->sampai;
        }




        $presensi_detail  = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->leftJoin('presensi_izinabsen_approve', 'presensi.id', '=', 'presensi_izinabsen_approve.id_presensi')
            ->leftJoin('presensi_izinabsen', 'presensi_izinabsen_approve.kode_izin', '=', 'presensi_izinabsen.kode_izin')
            ->leftJoin('presensi_izinsakit_approve', 'presensi.id', '=', 'presensi_izinsakit_approve.id_presensi')
            ->leftJoin('presensi_izinsakit', 'presensi_izinsakit_approve.kode_izin_sakit', '=', 'presensi_izinsakit.kode_izin_sakit')
            ->leftJoin('presensi_izincuti_approve', 'presensi.id', '=', 'presensi_izincuti_approve.id_presensi')
            ->leftJoin('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti')
            ->select(
                'presensi.*',
                'nama_jam_kerja',
                'jam_masuk',
                'jam_pulang',
                'istirahat',
                'jam_awal_istirahat',
                'jam_akhir_istirahat',
                'lintashari',
                'total_jam',
                'presensi_izinabsen.keterangan as keterangan_izin_absen',
                'presensi_izinsakit.keterangan as keterangan_izin_sakit',
                'presensi_izincuti.keterangan as keterangan_izin_cuti'
            )
            ->whereBetween('presensi.tanggal', [$periode_dari, $periode_sampai]);

        /**
         * Mapping jadwal kerja per karyawan dengan prioritas:
         * 1. presensi_jamkerja_bydate (per karyawan per tanggal)
         * 2. grup_jamkerja_bydate (berdasarkan grup karyawan)
         * 3. presensi_jamkerja_byday (per karyawan per hari)
         * 4. presensi_jamkerja_bydept_detail (per departemen & cabang per hari)
         *
         * Agar laporan tidak berat, semua jadwal diambil sekali di sini
         * lalu dikonversi menjadi array PHP sederhana yang dipakai di view.
         */

        // 1) Jadwal by-date per karyawan (presensi_jamkerja_bydate)
        $jadwal_bydate_raw = DB::table('presensi_jamkerja_bydate')
            ->join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi_jamkerja_bydate.nik',
                'presensi_jamkerja_bydate.tanggal',
                'presensi_jamkerja.total_jam',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang'
            )
            ->whereBetween('presensi_jamkerja_bydate.tanggal', [$periode_dari, $periode_sampai])
            ->get();

        // 1.5) Jadwal approved from ajuan_jadwal
        $jadwal_ajuan_raw = DB::table('ajuan_jadwal')
            ->join('presensi_jamkerja', 'ajuan_jadwal.kode_jam_kerja_tujuan', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'ajuan_jadwal.nik',
                'ajuan_jadwal.tanggal',
                'presensi_jamkerja.total_jam',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang'
            )
            ->where('ajuan_jadwal.status', 'a')
            ->whereBetween('ajuan_jadwal.tanggal', [$periode_dari, $periode_sampai])
            ->get();

        // Merge both into jadwal_bydate map
        $jadwal_bydate = $jadwal_bydate_raw->concat($jadwal_ajuan_raw)
            ->groupBy('nik')
            ->map(function ($rows) {
                $result = [];
                foreach ($rows as $row) {
                    $result[$row->tanggal] = [
                        'total_jam' => $row->total_jam,
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                    ];
                }
                return $result;
            });

        // 2) Jadwal grup by-date (grup_jamkerja_bydate)
        $jadwal_grup_bydate = DB::table('grup_detail')
            ->join('grup_jamkerja_bydate', 'grup_detail.kode_grup', '=', 'grup_jamkerja_bydate.kode_grup')
            ->join('presensi_jamkerja', 'grup_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'grup_detail.nik',
                'grup_jamkerja_bydate.tanggal',
                'presensi_jamkerja.total_jam',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang'
            )
            ->whereBetween('grup_jamkerja_bydate.tanggal', [$periode_dari, $periode_sampai])
            ->get()
            ->groupBy('nik')
            ->map(function ($rows) {
                $result = [];
                foreach ($rows as $row) {
                    $result[$row->tanggal] = [
                        'total_jam' => $row->total_jam,
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                    ];
                }
                return $result;
            });

        // 3) Jadwal by-day per karyawan (presensi_jamkerja_byday)
        $jadwal_byday = DB::table('presensi_jamkerja_byday')
            ->join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi_jamkerja_byday.nik',
                'presensi_jamkerja_byday.hari',
                'presensi_jamkerja.total_jam',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang'
            )
            ->get()
            ->groupBy('nik')
            ->map(function ($rows) {
                $result = [];
                foreach ($rows as $row) {
                    $result[$row->hari] = [
                        'total_jam' => $row->total_jam,
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                    ];
                }
                return $result;
            });

        // 4) Jadwal by-day per departemen & cabang (presensi_jamkerja_bydept_detail)
        $jadwal_bydept = DB::table('presensi_jamkerja_bydept_detail')
            ->join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
            ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi_jamkerja_bydept.kode_dept',
                'presensi_jamkerja_bydept.kode_cabang',
                'presensi_jamkerja_bydept_detail.hari',
                'presensi_jamkerja.total_jam',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang'
            )
            ->get()
            ->groupBy(function ($row) {
                return $row->kode_dept . '|' . $row->kode_cabang;
            })
            ->map(function ($rows) {
                $result = [];
                foreach ($rows as $row) {
                    $result[$row->hari] = [
                        'total_jam' => $row->total_jam,
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                    ];
                }
                return $result;
            });

        // 5) Jadwal Global (Jika diaktifkan)
        $jadwal_global = [];
        if ($generalsetting->global_jamkerja_aktif) {
            $jadwal_global = DB::table('global_jamkerja')
                ->join('presensi_jamkerja', 'global_jamkerja.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->select(
                    'global_jamkerja.hari',
                    'presensi_jamkerja.total_jam',
                    'presensi_jamkerja.nama_jam_kerja',
                    'presensi_jamkerja.jam_masuk',
                    'presensi_jamkerja.jam_pulang'
                )
                ->get()
                ->keyBy('hari')
                ->map(function ($row) {
                    return [
                        'total_jam' => $row->total_jam,
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                    ];
                })
                ->toArray();
        }


        $gaji_pokok = Gajipokok::select(
            'nik',
            'jumlah',
            'jenis_upah'
        )
            ->whereIn('kode_gaji', function ($query) use ($periode_sampai) {
                $query->select(DB::raw('MAX(kode_gaji)'))
                    ->from('karyawan_gaji_pokok')
                    ->where('tanggal_berlaku', '<=', $periode_sampai)
                    ->groupBy('nik');
            });



        $bpjs_kesehatan = Bpjskesehatan::select(
            'nik',
            'jumlah'
        )
            ->whereIn('kode_bpjs_kesehatan', function ($query) use ($periode_sampai) {
                $query->select(DB::raw('MAX(kode_bpjs_kesehatan)'))
                    ->from('karyawan_bpjskesehatan')
                    ->where('tanggal_berlaku', '<=', $periode_sampai)
                    ->groupBy('nik');
            });


        $bpjs_tenagakerja = Bpjstenagakerja::select(
            'nik',
            'jumlah'
        )
            ->whereIn('kode_bpjs_tk', function ($query) use ($periode_sampai) {
                $query->select(DB::raw('MAX(kode_bpjs_tk)'))
                    ->from('karyawan_bpjstenagakerja')
                    ->where('tanggal_berlaku', '<=', $periode_sampai)
                    ->groupBy('nik');
            });


        //Tunjangan
        $jenis_tunjangan = Jenistunjangan::orderBy('kode_jenis_tunjangan')->get();
        $select_tunjangan = [];
        $select_field_tunjangan = [];
        foreach ($jenis_tunjangan as $d) {
            $select_tunjangan[] = DB::raw('SUM(IF(karyawan_tunjangan_detail.kode_jenis_tunjangan = "' . $d->kode_jenis_tunjangan . '", karyawan_tunjangan_detail.jumlah, 0)) as jumlah_' . $d->kode_jenis_tunjangan);
            $select_field_tunjangan[] = 'jumlah_' . $d->kode_jenis_tunjangan;
        }
        $tunjangan = Detailtunjangan::query();
        $tunjangan->join('karyawan_tunjangan', 'karyawan_tunjangan_detail.kode_tunjangan', '=', 'karyawan_tunjangan.kode_tunjangan');
        $tunjangan->select(
            'karyawan_tunjangan.nik',
            ...$select_tunjangan
        );
        $tunjangan->whereIn('karyawan_tunjangan_detail.kode_tunjangan', function ($query) use ($periode_sampai) {
            $query->select(DB::raw('MAX(kode_tunjangan)'))
                ->from('karyawan_tunjangan')
                ->where('tanggal_berlaku', '<=', $periode_sampai)
                ->groupBy('nik');
        });

        $tunjangan->groupBy('karyawan_tunjangan.nik');


        $penyesuaian_gaji = Detailpenyesuaiangaji::select('nik', 'penambah', 'pengurang', 'keterangan')
            ->join('karyawan_penyesuaian_gaji', 'karyawan_penyesuaian_gaji_detail.kode_penyesuaian_gaji', '=', 'karyawan_penyesuaian_gaji.kode_penyesuaian_gaji')
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun);

        $pinjaman = DB::table('pembayaran_pinjaman')
            ->select('pinjaman.nik', DB::raw('SUM(pembayaran_pinjaman.jumlah_bayar) as total_cicilan'))
            ->join('pinjaman', 'pembayaran_pinjaman.pinjaman_id', '=', 'pinjaman.id')
            ->where('pembayaran_pinjaman.bulan_gaji', $request->bulan)
            ->where('pembayaran_pinjaman.tahun_gaji', $request->tahun)
            ->where('pembayaran_pinjaman.jenis_pembayaran', 'C')
            ->groupBy('pinjaman.nik');

        $q_presensi = Karyawan::query();
        $q_presensi->where(function($q) use ($periode_dari) {
            $q->where('karyawan.status_aktif_karyawan', 1)
              ->orWhere('karyawan.tanggal_nonaktif', '>=', $periode_dari);
        });
        $q_presensi->select(
            'karyawan.nik',
            'karyawan.nik_show',
            'karyawan.no_rekening',
            'karyawan.nama_bank',
            'karyawan.nama_rekening',
            'nama_karyawan',
            'nama_jabatan',
            'karyawan.kode_dept',
            'nama_dept',
            'karyawan.kode_cabang',
            'karyawan.hitung_pph21',
            'karyawan.kode_status_kawin',
            'presensi.tanggal',
            'presensi.status',
            'presensi.kode_jam_kerja',
            'presensi.nama_jam_kerja',
            'presensi.jam_masuk',
            'presensi.jam_pulang',
            'presensi.jam_in',
            'presensi.jam_out',
            'presensi.istirahat_in',
            'presensi.istirahat_out',
            'presensi.istirahat',
            'presensi.jam_awal_istirahat',
            'presensi.jam_akhir_istirahat',
            'presensi.lintashari',
            'presensi.keterangan_izin_absen',
            'presensi.keterangan_izin_sakit',
            'presensi.keterangan_izin_cuti',
            'presensi.total_jam',
            'presensi.denda',
            'presensi.status_potongan',
            'presensi.status_potongan_istirahat',
            'presensi.jam_lembur_aktual',
            'presensi.jam_lembur_netto',
            'presensi.nominal_lembur',
            'presensi.is_lembur_khusus',
            'gaji_pokok.jumlah as gaji_pokok',
            'gaji_pokok.jenis_upah',
            'bpjs_kesehatan.jumlah as bpjs_kesehatan',
            'bpjs_tenagakerja.jumlah as bpjs_tenagakerja',
            'penambah',
            'pengurang',
            'penyesuaian_gaji.keterangan as keterangan_penyesuaian',
            'pinjaman_cicilan.total_cicilan',
            ...$select_field_tunjangan
        );
        $q_presensi->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $q_presensi->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $q_presensi->leftJoinSub($presensi_detail, 'presensi', function ($join) {
            $join->on('karyawan.nik', '=', 'presensi.nik');
        });
        $q_presensi->leftJoinSub($gaji_pokok, 'gaji_pokok', function ($join) {
            $join->on('karyawan.nik', '=', 'gaji_pokok.nik');
        });


        $q_presensi->leftJoinSub($bpjs_kesehatan, 'bpjs_kesehatan', function ($join) {
            $join->on('karyawan.nik', '=', 'bpjs_kesehatan.nik');
        });

        $q_presensi->leftJoinSub($bpjs_tenagakerja, 'bpjs_tenagakerja', function ($join) {
            $join->on('karyawan.nik', '=', 'bpjs_tenagakerja.nik');
        });


        $q_presensi->leftJoinSub($tunjangan, 'tunjangan', function ($join) {
            $join->on('karyawan.nik', '=', 'tunjangan.nik');
        });

        $q_presensi->leftJoinSub($penyesuaian_gaji, 'penyesuaian_gaji', function ($join) {
            $join->on('karyawan.nik', '=', 'penyesuaian_gaji.nik');
        });
        $q_presensi->leftJoinSub($pinjaman, 'pinjaman_cicilan', function ($join) {
            $join->on('karyawan.nik', '=', 'pinjaman_cicilan.nik');
        });

        if (!$user->isSuperAdmin() && !$user->hasRole('karyawan')) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();

            if (!empty($request->kode_cabang)) {
                if (in_array($request->kode_cabang, $userCabangs)) {
                    $q_presensi->where('karyawan.kode_cabang', $request->kode_cabang);
                } else {
                    $q_presensi->where('karyawan.kode_cabang', 'INVALID');
                }
            } else {
                $q_presensi->whereIn('karyawan.kode_cabang', $userCabangs);
            }

            if (!empty($request->kode_dept)) {
                if (in_array($request->kode_dept, $userDepartemens)) {
                    $q_presensi->where('karyawan.kode_dept', $request->kode_dept);
                } else {
                    $q_presensi->where('karyawan.kode_dept', 'INVALID');
                }
            } else {
                $q_presensi->whereIn('karyawan.kode_dept', $userDepartemens);
            }
        } else {
            if (!empty($request->kode_cabang)) {
                $q_presensi->where('karyawan.kode_cabang', $request->kode_cabang);
            }
            if (!empty($request->kode_dept)) {
                $q_presensi->where('karyawan.kode_dept', $request->kode_dept);
            }
        }

        if (!empty($request->nik)) {
            if (is_array($request->nik)) {
                $q_presensi->whereIn('karyawan.nik', $request->nik);
            } else {
                $q_presensi->where('karyawan.nik', $request->nik);
            }
        }

        if (!empty($request->jenis_upah)) {
            $q_presensi->where('gaji_pokok.jenis_upah', $request->jenis_upah);
        }

        if ($user->hasRole('karyawan')) {
            $q_presensi->where('karyawan.nik', $userkaryawan->nik);
        }
        $q_presensi->orderBy('karyawan.nama_karyawan');
        $q_presensi->orderBy('presensi.tanggal', 'asc');
        $presensi = $q_presensi->get();


        $data['periode_dari'] = $periode_dari;
        $data['periode_sampai'] = $periode_sampai;
        $data['jmlhari'] = hitungJumlahHari($periode_dari, $periode_sampai) + 1;
        $data['denda_list'] = Denda::all()->toArray();
        $data['datalibur'] = getdatalibur($periode_dari, $periode_sampai);
        $data['datalembur'] = getlembur($periode_dari, $periode_sampai);
        $data['generalsetting'] = $generalsetting;
        // Kirim mapping jadwal ke view untuk dipakai saat karyawan tidak presensi
        $data['jadwal_bydate'] = $jadwal_bydate;
        $data['jadwal_grup_bydate'] = $jadwal_grup_bydate;
        $data['jadwal_byday'] = $jadwal_byday;
        $data['jadwal_bydept'] = $jadwal_bydept;
        $data['jadwal_global'] = $jadwal_global;

        // === PERFORMANCE OPTIMIZATION: Pre-load data to avoid N+1 queries in blade ===

        // 1. Pre-load LemburKaryawanKhusus for all employees → O(1) lookup by NIK
        $data['lembur_khusus_map'] = \App\Models\LemburKaryawanKhusus::where('status', 1)
            ->get()
            ->keyBy('nik');

        // 2. Pre-load libur nasional dates as indexed set for O(1) lookup
        $data['libur_nasional_dates'] = DB::table('hari_libur_detail')
            ->join('hari_libur', 'hari_libur_detail.kode_libur', '=', 'hari_libur.kode_libur')
            ->whereBetween('hari_libur.tanggal', [$periode_dari, $periode_sampai])
            ->pluck('hari_libur.tanggal')
            ->flip()
            ->toArray();

        // 3. Create indexed versions of datalibur for O(1) lookup (key = "nik|tanggal")
        $datalibur_indexed = [];
        foreach ($data['datalibur'] as $item) {
            $key = $item['nik'] . '|' . $item['tanggal'];
            $datalibur_indexed[$key][] = $item;
        }
        // Also index libur by tanggal only (for entries where nik is null = applies to all)
        $datalibur_by_tanggal = [];
        foreach ($data['datalibur'] as $item) {
            if (empty($item['nik'])) {
                $datalibur_by_tanggal[$item['tanggal']][] = $item;
            }
        }
        $data['datalibur_indexed'] = $datalibur_indexed;
        $data['datalibur_by_tanggal'] = $datalibur_by_tanggal;

        // 4. Create indexed versions of datalembur for O(1) lookup (key = "nik|tanggal")
        $datalembur_indexed = [];
        foreach ($data['datalembur'] as $item) {
            $key = $item['nik'] . '|' . $item['tanggal'];
            $datalembur_indexed[$key][] = $item;
        }
        $data['datalembur_indexed'] = $datalembur_indexed;
        // Simpan parameter request untuk button kunci laporan
        $data['request_params'] = [
            'periode_laporan' => $request->periode_laporan,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'kode_cabang' => $request->kode_cabang ?? '',
            'kode_dept' => $request->kode_dept ?? '',
            'nik' => $request->nik ?? '',
            'jenis_upah' => $request->jenis_upah ?? ''
        ];


        if (!empty($request->nik) && $request->format_laporan == 1) {
            $karyawan = Karyawan::join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
                ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
                ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
                ->select('karyawan.*', 'jabatan.nama_jabatan', 'departemen.nama_dept', 'cabang.nama_cabang')
                ->where('karyawan.nik', $request->nik)
                ->first();
            $data['karyawan'] = $karyawan;
            $data['presensi'] = $presensi;
            if ($request->has('exportButton')) {
                return Excel::download(new PresensiKaryawanExport($data), 'Laporan Presensi Karyawan ' . $periode_dari . ' - ' . $periode_sampai . '.xlsx');
            }
            return view('laporan.presensi_karyawan_cetak', $data);
        } else {
            $laporan_presensi = $presensi->groupBy('nik')->map(function ($rows) use ($jenis_tunjangan) {
                $data = [
                    'nik' => $rows->first()->nik,
                    'nik_show' => $rows->first()->nik_show,
                    'no_rekening' => $rows->first()->no_rekening,
                    'nama_bank' => $rows->first()->nama_bank,
                    'nama_rekening' => $rows->first()->nama_rekening,
                    'nama_karyawan' => $rows->first()->nama_karyawan,
                    'nama_jabatan' => $rows->first()->nama_jabatan,
                    'kode_dept' => $rows->first()->kode_dept,
                    'nama_dept' => $rows->first()->nama_dept,
                    'kode_cabang' => $rows->first()->kode_cabang,
                    'hitung_pph21' => $rows->first()->hitung_pph21,
                    'kode_status_kawin' => $rows->first()->kode_status_kawin,
                    'gaji_pokok' => $rows->first()->gaji_pokok,
                    'jenis_upah' => $rows->first()->jenis_upah,
                    'bpjs_kesehatan' => $rows->first()->bpjs_kesehatan,
                    'bpjs_tenagakerja' => $rows->first()->bpjs_tenagakerja,
                    'penambah' => $rows->first()->penambah,
                    'pengurang' => $rows->first()->pengurang,
                    'keterangan_penyesuaian' => $rows->first()->keterangan_penyesuaian,
                    'cicilan_pinjaman' => $rows->first()->total_cicilan,

                ];

                $total_hadir = 0;
                foreach ($rows as $row) {
                    if ($row->status == 'h') {
                        $total_hadir++;
                    }
                }

                foreach ($jenis_tunjangan as $j) {
                    $base_amount = (float)$rows->first()->{"jumlah_" . $j->kode_jenis_tunjangan};
                    if ($j->status_tunjangan === '0') {
                        $actual_amount = $base_amount * $total_hadir;
                    } else {
                        $actual_amount = $base_amount;
                    }
                    $data = [
                        ...$data,
                        $j->kode_jenis_tunjangan => $actual_amount
                    ];
                }

                foreach ($rows as $row) {
                    $data[$row->tanggal] = [
                        'status' => $row->status,
                        'kode_jam_kerja' => $row->kode_jam_kerja,
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                        'jam_in' => $row->jam_in,
                        'jam_out' => $row->jam_out,
                        'istirahat' => $row->istirahat,
                        'jam_awal_istirahat' => $row->jam_awal_istirahat,
                        'jam_akhir_istirahat' => $row->jam_akhir_istirahat,
                        'istirahat_in' => $row->istirahat_in,
                        'istirahat_out' => $row->istirahat_out,
                        'lintashari' => $row->lintashari,
                        'keterangan_izin_absen' => $row->keterangan_izin_absen,
                        'keterangan_izin_sakit' => $row->keterangan_izin_sakit,
                        'keterangan_izin_cuti' => $row->keterangan_izin_cuti,
                        'total_jam' => $row->total_jam,
                        'denda' => $row->denda ?? null,
                        'status_potongan' => $row->status_potongan ?? null,
                        'status_potongan_istirahat' => $row->status_potongan_istirahat ?? null,
                        'jam_lembur_aktual' => $row->jam_lembur_aktual ?? null,
                        'jam_lembur_netto' => $row->jam_lembur_netto ?? null,
                        'nominal_lembur' => $row->nominal_lembur ?? null,
                        'is_lembur_khusus' => $row->is_lembur_khusus ?? false
                    ];
                }
                return $data;
            });
            $data['laporan_presensi'] = $laporan_presensi;
            $data['jenis_tunjangan'] = $jenis_tunjangan;
            $data['bulan'] = $request->bulan;
            $data['tahun'] = $request->tahun;

            if ($request->bulan == 12) {
                $janNovStats = DB::table('pph21_slip_detail')
                    ->join('slip_gaji', 'pph21_slip_detail.kode_slip_gaji', '=', 'slip_gaji.kode_slip_gaji')
                    ->select(
                        'pph21_slip_detail.nik',
                        DB::raw('SUM(pph21_slip_detail.penghasilan_bruto) as total_bruto'),
                        DB::raw('SUM(pph21_slip_detail.pph21_terutang) as total_pph')
                    )
                    ->where('slip_gaji.tahun', $request->tahun)
                    ->whereBetween('slip_gaji.bulan', [1, 11])
                    ->groupBy('pph21_slip_detail.nik')
                    ->get()
                    ->keyBy('nik');
                $data['janNovStatsAll'] = $janNovStats;
            } else {
                $data['janNovStatsAll'] = collect();
            }


            if ($user->hasRole('karyawan')) {
                $first_row = $laporan_presensi->first();
                $jenis_upah = ($first_row && isset($first_row['jenis_upah'])) ? $first_row['jenis_upah'] : 'Bulanan';
                $view = ($jenis_upah == 'Harian') ? 'laporan.slip_karyawan_harian_cetak' : 'laporan.slip_karyawan_cetak';
                return view($view, $data);
            } else {
                if ($request->format_laporan == 1) {
                    if ($request->has('exportButton')) {
                        ini_set('memory_limit', '1024M');
                        ini_set('max_execution_time', 300);
                        $view_excel = $request->format_rekap == 2 ? 'laporan.presensi_excel_v2' : 'laporan.presensi_excel';
                        if ($request->format_rekap == 2) {
                            return Excel::download(new \App\Exports\PresensiExportV2($data), 'Rekap Presensi ' . $periode_dari . ' - ' . $periode_sampai . '.xlsx');
                        }
                        return Excel::download(new PresensiExport($data, $view_excel), 'Rekap Presensi ' . $periode_dari . ' - ' . $periode_sampai . '.xlsx');
                    }
                    $view = $request->format_rekap == 2 ? 'laporan.presensi_cetak_v2' : 'laporan.presensi_cetak';
                    return view($view, $data);
                } else if ($request->format_laporan == 2) {
                    if ($request->has('exportButton')) {
                        $view = $request->jenis_upah == 'Harian' ? 'laporan.gaji_harian_excel' : 'laporan.gaji_excel';
                        return Excel::download(new GajiExport($data, $view), 'Rekap Gaji ' . $periode_dari . ' - ' . $periode_sampai . '.xlsx');
                    }

                    if ($request->jenis_upah == 'Harian') {
                        return view('laporan.gaji_harian_cetak', $data);
                    }

                    return view('laporan.gaji_cetak', $data);
                } else if ($request->format_laporan == 3) {
                    $first_row = $laporan_presensi->first();
                    $jenis_upah = $request->jenis_upah ?: (($first_row && isset($first_row['jenis_upah'])) ? $first_row['jenis_upah'] : 'Bulanan');
                    
                    if ($user->hasRole('karyawan')) {
                        $view = $jenis_upah == 'Harian' ? 'laporan.slip_karyawan_harian_cetak' : 'laporan.slip_karyawan_cetak';
                        return view($view, $data);
                    }
                    $view = $jenis_upah == 'Harian' ? 'laporan.slip_harian_cetak' : 'laporan.slip_cetak';
                    return view($view, $data);
                }
            }
        }
    }

    public function kunciLaporan(Request $request)
    {
        try {
            $generalsetting = Pengaturanumum::where('id', 1)->first();
            $periode_laporan_dari = $generalsetting->periode_laporan_dari;
            $periode_laporan_sampai = $generalsetting->periode_laporan_sampai;
            $periode_laporan_lintas_bulan = $generalsetting->periode_laporan_next_bulan;
            
            if ($request->periode_laporan == 1) {
                if ($periode_laporan_lintas_bulan == 1) {
                    if ($request->bulan == 1) {
                        $bulan_dari = 12;
                        $tahun_dari = $request->tahun - 1;
                    } else {
                        $bulan_dari = $request->bulan - 1;
                        $tahun_dari = $request->tahun;
                    }
                    $bulan_sampai = $request->bulan;
                    $tahun_sampai = $request->tahun;
                } elseif ($periode_laporan_lintas_bulan == 2) {
                    $bulan_dari = $request->bulan;
                    $tahun_dari = $request->tahun;
                    if ($request->bulan == 12) {
                        $bulan_sampai = 1;
                        $tahun_sampai = $request->tahun + 1;
                    } else {
                        $bulan_sampai = $request->bulan + 1;
                        $tahun_sampai = $request->tahun;
                    }
                } else {
                    $bulan_dari = $request->bulan;
                    $tahun_dari = $request->tahun;
                    $bulan_sampai = $request->bulan;
                    $tahun_sampai = $request->tahun;
                }

                $bulan_dari = str_pad($bulan_dari, 2, '0', STR_PAD_LEFT);
                $last_day_start = date('t', strtotime($tahun_dari . '-' . $bulan_dari . '-01'));
                $p_dari_val = min($periode_laporan_dari, $last_day_start);
                $p_dari = str_pad($p_dari_val, 2, '0', STR_PAD_LEFT);
                $periode_dari = $tahun_dari . '-' . $bulan_dari . '-' . $p_dari;

                $bulan_sampai = str_pad($bulan_sampai, 2, '0', STR_PAD_LEFT);
                $last_day_end = date('t', strtotime($tahun_sampai . '-' . $bulan_sampai . '-01'));
                $p_sampai_val = min($periode_laporan_sampai, $last_day_end);
                $p_sampai = str_pad($p_sampai_val, 2, '0', STR_PAD_LEFT);
                $periode_sampai = $tahun_sampai . '-' . $bulan_sampai . '-' . $p_sampai;
            } else {
                $bulan = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
                $periode_dari = $request->tahun . '-' . $bulan . '-01';
                $periode_sampai = date('Y-m-t', strtotime($periode_dari));
            }

            // Ambil mapping jadwal kerja (sama seperti di cetakpresensi)
            // 1) Jadwal by-date per karyawan (presensi_jamkerja_bydate)
            $jadwal_bydate_raw = DB::table('presensi_jamkerja_bydate')
                ->join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->select(
                    'presensi_jamkerja_bydate.nik',
                    'presensi_jamkerja_bydate.tanggal',
                    'presensi_jamkerja.kode_jam_kerja',
                    'presensi_jamkerja.total_jam'
                )
                ->whereBetween('presensi_jamkerja_bydate.tanggal', [$periode_dari, $periode_sampai])
                ->get();

            // 1.5) Jadwal approved from ajuan_jadwal
            $jadwal_ajuan_raw = DB::table('ajuan_jadwal')
                ->join('presensi_jamkerja', 'ajuan_jadwal.kode_jam_kerja_tujuan', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->select(
                    'ajuan_jadwal.nik',
                    'ajuan_jadwal.tanggal',
                    'presensi_jamkerja.kode_jam_kerja',
                    'presensi_jamkerja.total_jam'
                )
                ->where('ajuan_jadwal.status', 'a')
                ->whereBetween('ajuan_jadwal.tanggal', [$periode_dari, $periode_sampai])
                ->get();

            // Merge both into jadwal_bydate map
            $jadwal_bydate = $jadwal_bydate_raw->concat($jadwal_ajuan_raw)
                ->groupBy('nik')
                ->map(function ($rows) {
                    $result = [];
                    foreach ($rows as $row) {
                        $result[$row->tanggal] = [
                            'kode_jam_kerja' => $row->kode_jam_kerja,
                            'total_jam' => $row->total_jam
                        ];
                    }
                    return $result;
                });

            // 2) Jadwal grup by-date
            $jadwal_grup_bydate = DB::table('grup_detail')
                ->join('grup_jamkerja_bydate', 'grup_detail.kode_grup', '=', 'grup_jamkerja_bydate.kode_grup')
                ->join('presensi_jamkerja', 'grup_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->select(
                    'grup_detail.nik',
                    'grup_jamkerja_bydate.tanggal',
                    'presensi_jamkerja.kode_jam_kerja',
                    'presensi_jamkerja.total_jam'
                )
                ->whereBetween('grup_jamkerja_bydate.tanggal', [$periode_dari, $periode_sampai])
                ->get()
                ->groupBy('nik')
                ->map(function ($rows) {
                    $result = [];
                    foreach ($rows as $row) {
                        $result[$row->tanggal] = [
                            'kode_jam_kerja' => $row->kode_jam_kerja,
                            'total_jam' => $row->total_jam
                        ];
                    }
                    return $result;
                });

            // 3) Jadwal by-day per karyawan
            $jadwal_byday = DB::table('presensi_jamkerja_byday')
                ->join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->select(
                    'presensi_jamkerja_byday.nik',
                    'presensi_jamkerja_byday.hari',
                    'presensi_jamkerja.kode_jam_kerja',
                    'presensi_jamkerja.total_jam'
                )
                ->get()
                ->groupBy('nik')
                ->map(function ($rows) {
                    $result = [];
                    foreach ($rows as $row) {
                        $result[$row->hari] = [
                            'kode_jam_kerja' => $row->kode_jam_kerja,
                            'total_jam' => $row->total_jam
                        ];
                    }
                    return $result;
                });

            // 4) Jadwal by-day per departemen & cabang
            $jadwal_bydept = DB::table('presensi_jamkerja_bydept_detail')
                ->join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
                ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->select(
                    'presensi_jamkerja_bydept.kode_dept',
                    'presensi_jamkerja_bydept.kode_cabang',
                    'presensi_jamkerja_bydept_detail.hari',
                    'presensi_jamkerja.kode_jam_kerja',
                    'presensi_jamkerja.total_jam'
                )
                ->get()
                ->groupBy(function ($row) {
                    return $row->kode_dept . '|' . $row->kode_cabang;
                })
                ->map(function ($rows) {
                    $result = [];
                    foreach ($rows as $row) {
                        $result[$row->hari] = [
                            'kode_jam_kerja' => $row->kode_jam_kerja,
                            'total_jam' => $row->total_jam
                        ];
                    }
                    return $result;
                });

            // 5) Jadwal Global (Jika diaktifkan)
            $jadwal_global = [];
            if ($generalsetting->global_jamkerja_aktif) {
                $jadwal_global = DB::table('global_jamkerja')
                    ->join('presensi_jamkerja', 'global_jamkerja.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                    ->select(
                        'global_jamkerja.hari',
                        'presensi_jamkerja.kode_jam_kerja',
                        'presensi_jamkerja.total_jam'
                    )
                    ->get()
                    ->keyBy('hari')
                    ->map(function ($row) {
                        return [
                            'kode_jam_kerja' => $row->kode_jam_kerja,
                            'total_jam' => $row->total_jam
                        ];
                    })
                    ->toArray();
            }

            // Ambil data libur dan lembur
            $datalibur = getdatalibur($periode_dari, $periode_sampai);
            $datalembur = getlembur($periode_dari, $periode_sampai);

            // Ambil data tunjangan untuk menghitung nominal lembur standar
            $jenis_tunjangan = Jenistunjangan::orderBy('kode_jenis_tunjangan')->get();
            $select_tunjangan = [];
            foreach ($jenis_tunjangan as $jt) {
                $select_tunjangan[] = DB::raw('SUM(IF(karyawan_tunjangan_detail.kode_jenis_tunjangan = "' . $jt->kode_jenis_tunjangan . '", karyawan_tunjangan_detail.jumlah, 0)) as jumlah_' . $jt->kode_jenis_tunjangan);
            }
            $tunjangan_data = DB::table('karyawan_tunjangan_detail')
                ->join('karyawan_tunjangan', 'karyawan_tunjangan_detail.kode_tunjangan', '=', 'karyawan_tunjangan.kode_tunjangan')
                ->select('karyawan_tunjangan.nik', ...$select_tunjangan)
                ->whereIn('karyawan_tunjangan_detail.kode_tunjangan', function ($query) use ($periode_sampai) {
                    $query->select(DB::raw('MAX(kode_tunjangan)'))
                        ->from('karyawan_tunjangan')
                        ->where('tanggal_berlaku', '<=', $periode_sampai)
                        ->groupBy('nik');
                })
                ->groupBy('karyawan_tunjangan.nik')
                ->get()
                ->keyBy('nik');

            // Ambil data gaji pokok sebagai collection terindeks NIK
            $gaji_pokok_data = Gajipokok::select('nik', 'jumlah', 'jenis_upah')
                ->whereIn('kode_gaji', function ($query) use ($periode_sampai) {
                    $query->select(DB::raw('MAX(kode_gaji)'))
                        ->from('karyawan_gaji_pokok')
                        ->where('tanggal_berlaku', '<=', $periode_sampai)
                        ->groupBy('nik');
                })
                ->get()
                ->keyBy('nik');

            // Ambil data presensi dalam periode dengan join ke karyawan untuk filter
            $presensi_query = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->leftJoin('karyawan', 'presensi.nik', '=', 'karyawan.nik')
                ->select(
                    'presensi.*',
                    'presensi_jamkerja.jam_masuk',
                    'presensi_jamkerja.jam_pulang'
                )
                ->whereBetween('presensi.tanggal', [$periode_dari, $periode_sampai])
                ->where('presensi.status', 'h'); // Hanya presensi dengan status hadir

            // Filter berdasarkan request
            if (!empty($request->kode_cabang)) {
                $presensi_query->where('karyawan.kode_cabang', $request->kode_cabang);
            }

            if (!empty($request->kode_dept)) {
                $presensi_query->where('karyawan.kode_dept', $request->kode_dept);
            }

            if (!empty($request->nik)) {
                $presensi_query->where('presensi.nik', $request->nik);
            }

            $presensi_list_raw = $presensi_query->get();
            $presensi_list = $presensi_list_raw->groupBy('nik');
            $denda_list = Denda::all()->toArray();

            $gaji_pokok = Gajipokok::select(
                'nik',
                'jumlah',
                'jenis_upah'
            )
                ->whereIn('kode_gaji', function ($query) use ($periode_sampai) {
                    $query->select(DB::raw('MAX(kode_gaji)'))
                        ->from('karyawan_gaji_pokok')
                        ->where('tanggal_berlaku', '<=', $periode_sampai)
                        ->groupBy('nik');
                });

            // Ambil semua karyawan yang sesuai filter
            $karyawan_query = Karyawan::query()
                ->select('karyawan.nik', 'karyawan.kode_dept', 'karyawan.kode_cabang');

            $karyawan_query->where(function($q) use ($periode_dari) {
                $q->where('karyawan.status_aktif_karyawan', 1)
                  ->orWhere('karyawan.tanggal_nonaktif', '>=', $periode_dari);
            });

            if (!empty($request->kode_cabang)) {
                $karyawan_query->where('karyawan.kode_cabang', $request->kode_cabang);
            }

            if (!empty($request->kode_dept)) {
                $karyawan_query->where('karyawan.kode_dept', $request->kode_dept);
            }

            if (!empty($request->nik)) {
                $karyawan_query->where('karyawan.nik', $request->nik);
            }

            if (!empty($request->jenis_upah)) {
                $karyawan_query->leftJoinSub($gaji_pokok, 'gaji_pokok', function ($join) {
                    $join->on('karyawan.nik', '=', 'gaji_pokok.nik');
                });
                $karyawan_query->where('gaji_pokok.jenis_upah', $request->jenis_upah);
            }

            $karyawan_list = $karyawan_query->get();

            $updated_count = 0;
            $inserted_alpa_count = 0;

            // Loop setiap karyawan
            foreach ($karyawan_list as $karyawan) {
                // Ambil presensi yang sudah ada untuk karyawan ini
                $presensi_karyawan = $presensi_list[$karyawan->nik] ?? collect();
                $presensi_by_tanggal = $presensi_karyawan->keyBy('tanggal');

                // Pre-calculate overtime context per karyawan
                $lemburKhusus = getLemburKhusus($karyawan->nik);
                $gp = $gaji_pokok_data[$karyawan->nik] ?? null;
                $gaji_pokok_val = $gp ? $gp->jumlah : 0;

                // Hitung total tunjangan untuk nominal lembur standar
                $total_tunjangan_val = 0;
                $td = $tunjangan_data[$karyawan->nik] ?? null;
                if ($td) {
                    foreach ($jenis_tunjangan as $jt) {
                        $field = 'jumlah_' . $jt->kode_jenis_tunjangan;
                        $total_tunjangan_val += $td->$field ?? 0;
                    }
                }

                // Loop setiap tanggal dalam periode
                $tanggal_loop = $periode_dari;
                while (strtotime($tanggal_loop) <= strtotime($periode_sampai)) {
                    // === Hitung data lembur untuk tanggal ini ===
                    $search_lembur = ['nik' => $karyawan->nik, 'tanggal' => $tanggal_loop];
                    $ceklemburData = ceklembur($datalembur, $search_lembur);
                    $lembur_aktual_harian = hitungLembur($ceklemburData);
                    $is_libur = isLiburKaryawan($karyawan->nik, $tanggal_loop);
                    $tipe_hari = $is_libur ? 2 : 1;

                    $jam_lembur_aktual = 0;
                    $jam_lembur_netto = 0;
                    $nominal_lembur = 0;
                    $is_khusus = false;

                    if ($lembur_aktual_harian > 0) {
                        $jam_lembur_aktual = $lembur_aktual_harian;

                        if ($lemburKhusus) {
                            // Lembur khusus: jam netto = jam aktual, nominal = upah_perjam * aktual
                            $jam_lembur_netto = $lembur_aktual_harian;
                            $nominal_lembur = ROUND($lemburKhusus->upah_perjam * $lembur_aktual_harian);
                            $is_khusus = true;
                        } else {
                            // Lembur standar: jam netto via hitungJamNetto, nominal via basis gapok+tunjangan
                            $jam_lembur_netto = hitungJamNetto($lembur_aktual_harian, $tipe_hari);
                            $upah_perjam_lembur = ($gaji_pokok_val + $total_tunjangan_val) / ($generalsetting->total_jam_bulan ?: 173);
                            $nominal_lembur = ROUND($upah_perjam_lembur * $jam_lembur_netto);
                        }
                    }

                    // Cek apakah sudah ada presensi untuk tanggal ini
                    if ($presensi_by_tanggal->has($tanggal_loop)) {
                        // Ada presensi, update denda + snapshot lembur
                        $presensi = $presensi_by_tanggal[$tanggal_loop];
                        $jam_masuk = $presensi->tanggal . ' ' . $presensi->jam_masuk;
                        $terlambat = hitungjamterlambat($presensi->jam_in, $jam_masuk);

                        $denda = 0;
                        if ($terlambat != null) {
                            if ($terlambat['desimal_terlambat'] < 1) {
                                $denda = hitungdenda($denda_list, $terlambat['menitterlambat']);
                            }
                        }

                        // Update denda, status_potongan, dan snapshot lembur
                        Presensi::where('id', $presensi->id)->update([
                            'denda' => $denda,
                            'status_potongan' => $generalsetting->status_potongan_jam,
                            'status_potongan_istirahat' => $generalsetting->potongan_istirahat,
                            'jam_lembur_aktual' => $jam_lembur_aktual > 0 ? $jam_lembur_aktual : null,
                            'jam_lembur_netto' => $jam_lembur_netto > 0 ? $jam_lembur_netto : null,
                            'nominal_lembur' => $nominal_lembur > 0 ? $nominal_lembur : null,
                            'is_lembur_khusus' => $is_khusus,
                        ]);
                        $updated_count++;
                    } else {
                        // Tidak ada presensi, cek apakah alpa
                        $search = [
                            'nik' => $karyawan->nik,
                            'tanggal' => $tanggal_loop,
                        ];
                        
                        $ceklibur = ceklibur($datalibur, $search);
                        $nama_hari = getHari($tanggal_loop);

                        // Jika bukan libur, cek jadwal kerja
                        if (empty($ceklibur)) {
                            // Cek jadwal dengan prioritas yang sama seperti di view
                            $mapJadwalByDate = $jadwal_bydate[$karyawan->nik] ?? [];
                            $mapJadwalGrupByDate = $jadwal_grup_bydate[$karyawan->nik] ?? [];
                            $mapJadwalByDay = $jadwal_byday[$karyawan->nik] ?? [];
                            
                            $jadwal_info = null;
                            $kode_jam_kerja = null;
                            
                            // 1) Cek jadwal by-date per karyawan
                            if (isset($mapJadwalByDate[$tanggal_loop])) {
                                $jadwal_info = $mapJadwalByDate[$tanggal_loop];
                                $kode_jam_kerja = $jadwal_info['kode_jam_kerja'];
                            }
                            // 2) Cek jadwal grup by-date
                            elseif (isset($mapJadwalGrupByDate[$tanggal_loop])) {
                                $jadwal_info = $mapJadwalGrupByDate[$tanggal_loop];
                                $kode_jam_kerja = $jadwal_info['kode_jam_kerja'];
                            }
                            // 3) Cek jadwal by-day per karyawan
                            elseif (isset($mapJadwalByDay[$nama_hari])) {
                                $jadwal_info = $mapJadwalByDay[$nama_hari];
                                $kode_jam_kerja = $jadwal_info['kode_jam_kerja'];
                            }
                            // 4) Cek jadwal by-day per departemen & cabang
                            else {
                                $keyDeptCabang = $karyawan->kode_dept . '|' . $karyawan->kode_cabang;
                                $mapDept = $jadwal_bydept[$keyDeptCabang] ?? [];
                                if (isset($mapDept[$nama_hari])) {
                                    $jadwal_info = $mapDept[$nama_hari];
                                    $kode_jam_kerja = $jadwal_info['kode_jam_kerja'];
                                }
                                // 5) Cek jadwal global
                                elseif (isset($jadwal_global[$nama_hari])) {
                                    $jadwal_info = $jadwal_global[$nama_hari];
                                    $kode_jam_kerja = $jadwal_info['kode_jam_kerja'];
                                }
                            }

                            // Jika ada jadwal tapi tidak ada presensi → Alpa
                            if ($kode_jam_kerja !== null) {
                                // Cek apakah sudah ada presensi dengan status lain (izin, sakit, cuti, alpa)
                                $cek_presensi_existing = Presensi::where('nik', $karyawan->nik)
                                    ->where('tanggal', $tanggal_loop)
                                    ->first();

                                if (!$cek_presensi_existing) {
                                    // Insert presensi dengan status alpa
                                    Presensi::create([
                                        'nik' => $karyawan->nik,
                                        'tanggal' => $tanggal_loop,
                                        'kode_jam_kerja' => $kode_jam_kerja,
                                        'status' => 'a', // Alpa
                                        'jam_in' => null,
                                        'jam_out' => null,
                                        'denda' => null,
                                        'status_potongan' => $generalsetting->status_potongan_jam,
                                        'jam_lembur_aktual' => $jam_lembur_aktual > 0 ? $jam_lembur_aktual : null,
                                        'jam_lembur_netto' => $jam_lembur_netto > 0 ? $jam_lembur_netto : null,
                                        'nominal_lembur' => $nominal_lembur > 0 ? $nominal_lembur : null,
                                        'is_lembur_khusus' => $is_khusus,
                                    ]);
                                    $inserted_alpa_count++;
                                }
                            }
                        } else {
                            // Hari libur tapi mungkin ada lembur, update presensi jika ada
                            $cek_presensi_existing = Presensi::where('nik', $karyawan->nik)
                                ->where('tanggal', $tanggal_loop)
                                ->first();
                            if ($cek_presensi_existing && $jam_lembur_aktual > 0) {
                                Presensi::where('id', $cek_presensi_existing->id)->update([
                                    'jam_lembur_aktual' => $jam_lembur_aktual,
                                    'jam_lembur_netto' => $jam_lembur_netto,
                                    'nominal_lembur' => $nominal_lembur,
                                    'is_lembur_khusus' => $is_khusus,
                                ]);
                            }
                        }
                    }

                    // Increment tanggal
                    $tanggal_loop = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_loop)));
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Laporan berhasil dikunci. Total {$updated_count} presensi telah diupdate dengan denda, {$inserted_alpa_count} presensi alpa telah dibuat.",
                'updated_count' => $updated_count,
                'inserted_alpa_count' => $inserted_alpa_count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunci laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function batalkanKunciLaporan(Request $request)
    {
        try {
            $generalsetting = Pengaturanumum::where('id', 1)->first();
            $periode_laporan_dari = $generalsetting->periode_laporan_dari;
            $periode_laporan_sampai = $generalsetting->periode_laporan_sampai;
            $periode_laporan_lintas_bulan = $generalsetting->periode_laporan_next_bulan;
            
            if ($request->periode_laporan == 1) {
                if ($periode_laporan_lintas_bulan == 1) {
                    if ($request->bulan == 1) {
                        $bulan_dari = 12;
                        $tahun_dari = $request->tahun - 1;
                    } else {
                        $bulan_dari = $request->bulan - 1;
                        $tahun_dari = $request->tahun;
                    }
                    $bulan_sampai = $request->bulan;
                    $tahun_sampai = $request->tahun;
                } elseif ($periode_laporan_lintas_bulan == 2) {
                    $bulan_dari = $request->bulan;
                    $tahun_dari = $request->tahun;
                    if ($request->bulan == 12) {
                        $bulan_sampai = 1;
                        $tahun_sampai = $request->tahun + 1;
                    } else {
                        $bulan_sampai = $request->bulan + 1;
                        $tahun_sampai = $request->tahun;
                    }
                } else {
                    $bulan_dari = $request->bulan;
                    $tahun_dari = $request->tahun;
                    $bulan_sampai = $request->bulan;
                    $tahun_sampai = $request->tahun;
                }

                $bulan_dari = str_pad($bulan_dari, 2, '0', STR_PAD_LEFT);
                $p_dari = str_pad($periode_laporan_dari, 2, '0', STR_PAD_LEFT); 
                $periode_dari = $tahun_dari . '-' . $bulan_dari . '-' . $p_dari;

                $bulan_sampai = str_pad($bulan_sampai, 2, '0', STR_PAD_LEFT);
                $p_sampai = str_pad($periode_laporan_sampai, 2, '0', STR_PAD_LEFT); 
                $periode_sampai = $tahun_sampai . '-' . $bulan_sampai . '-' . $p_sampai;
            } else {
                $bulan = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
                $periode_dari = $request->tahun . '-' . $bulan . '-01';
                $periode_sampai = date('Y-m-t', strtotime($periode_dari));
            }

            $gaji_pokok = Gajipokok::select(
                'nik',
                'jumlah',
                'jenis_upah'
            )
                ->whereIn('kode_gaji', function ($query) use ($periode_sampai) {
                    $query->select(DB::raw('MAX(kode_gaji)'))
                        ->from('karyawan_gaji_pokok')
                        ->where('tanggal_berlaku', '<=', $periode_sampai)
                        ->groupBy('nik');
                });

            // Query untuk mendapatkan ID presensi yang akan diupdate
            $presensi_query = Presensi::query()
                ->select('presensi.id')
                ->whereBetween('presensi.tanggal', [$periode_dari, $periode_sampai]);

            // Filter berdasarkan request - perlu join dengan karyawan jika ada filter
            if (!empty($request->kode_cabang) || !empty($request->kode_dept)) {
                $presensi_query->leftJoin('karyawan', 'presensi.nik', '=', 'karyawan.nik');
            }

            if (!empty($request->kode_cabang)) {
                $presensi_query->where('karyawan.kode_cabang', $request->kode_cabang);
            }

            if (!empty($request->kode_dept)) {
                $presensi_query->where('karyawan.kode_dept', $request->kode_dept);
            }

            if (!empty($request->nik)) {
                $presensi_query->where('presensi.nik', $request->nik);
            }

            if (!empty($request->jenis_upah)) {
                $presensi_query->leftJoinSub($gaji_pokok, 'gaji_pokok', function ($join) {
                    $join->on('presensi.nik', '=', 'gaji_pokok.nik');
                });
                $presensi_query->where('gaji_pokok.jenis_upah', $request->jenis_upah);
            }

            // Ambil ID presensi yang akan diupdate
            $presensi_ids = $presensi_query->pluck('presensi.id')->toArray();

            // Update denda menjadi null untuk membatalkan kunci
            $updated_count = 0;
            if (!empty($presensi_ids)) {
                $updated_count = Presensi::whereIn('id', $presensi_ids)->update([
                    'denda' => null,
                    'status_potongan' => null,
                    'jam_lembur_aktual' => null,
                    'jam_lembur_netto' => null,
                    'nominal_lembur' => null,
                    'is_lembur_khusus' => false,
                ]);
            }

            /**
             * Hapus presensi ALPA yang dibuat otomatis saat kunci laporan
             * Kriteria:
             * - status = 'a'
             * - tanggal dalam periode
             * - sesuai filter cabang/dept/nik
             */
            $alpa_query = Presensi::query()
                ->whereBetween('presensi.tanggal', [$periode_dari, $periode_sampai])
                ->where('presensi.status', 'a');

            if (!empty($request->kode_cabang) || !empty($request->kode_dept)) {
                $alpa_query->leftJoin('karyawan', 'presensi.nik', '=', 'karyawan.nik');
            }

            if (!empty($request->kode_cabang)) {
                $alpa_query->where('karyawan.kode_cabang', $request->kode_cabang);
            }

            if (!empty($request->kode_dept)) {
                $alpa_query->where('karyawan.kode_dept', $request->kode_dept);
            }

            if (!empty($request->nik)) {
                $alpa_query->where('presensi.nik', $request->nik);
            }

            if (!empty($request->jenis_upah)) {
                $alpa_query->leftJoinSub($gaji_pokok, 'gaji_pokok', function ($join) {
                    $join->on('presensi.nik', '=', 'gaji_pokok.nik');
                });
                $alpa_query->where('gaji_pokok.jenis_upah', $request->jenis_upah);
            }

            $alpa_ids = $alpa_query->pluck('presensi.id')->toArray();
            $deleted_alpa_count = 0;
            if (!empty($alpa_ids)) {
                $deleted_alpa_count = Presensi::whereIn('id', $alpa_ids)->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "Kunci laporan berhasil dibatalkan. Total {$updated_count} presensi telah diupdate, {$deleted_alpa_count} presensi alpa telah dihapus.",
                'updated_count' => $updated_count,
                'deleted_alpa_count' => $deleted_alpa_count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan kunci laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function jadwal()
    {
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        return view('laporan.jadwal', $data);
    }

    public function cetakjadwal(Request $request)
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $periode_dari = $request->dari;
        $periode_sampai = $request->sampai;

        // 1) Jadwal by-date per karyawan (presensi_jamkerja_bydate)
        $jadwal_bydate_raw = DB::table('presensi_jamkerja_bydate')
            ->join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi_jamkerja_bydate.nik',
                'presensi_jamkerja_bydate.tanggal',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            )
            ->whereBetween('presensi_jamkerja_bydate.tanggal', [$periode_dari, $periode_sampai])
            ->get();

        // 1.5) Jadwal approved from ajuan_jadwal
        $jadwal_ajuan_raw = DB::table('ajuan_jadwal')
            ->join('presensi_jamkerja', 'ajuan_jadwal.kode_jam_kerja_tujuan', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'ajuan_jadwal.nik',
                'ajuan_jadwal.tanggal',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            )
            ->where('ajuan_jadwal.status', 'a')
            ->whereBetween('ajuan_jadwal.tanggal', [$periode_dari, $periode_sampai])
            ->get();

        // Merge both into jadwal_bydate map
        $jadwal_bydate = $jadwal_bydate_raw->concat($jadwal_ajuan_raw)
            ->groupBy('nik')
            ->map(function ($rows) {
                $result = [];
                foreach ($rows as $row) {
                    $result[$row->tanggal] = [
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                        'color' => $row->color,
                    ];
                }
                return $result;
            });

        // 2) Jadwal grup by-date
        $jadwal_grup_bydate = DB::table('grup_detail')
            ->join('grup_jamkerja_bydate', 'grup_detail.kode_grup', '=', 'grup_jamkerja_bydate.kode_grup')
            ->join('presensi_jamkerja', 'grup_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'grup_detail.nik',
                'grup_jamkerja_bydate.tanggal',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            )
            ->whereBetween('grup_jamkerja_bydate.tanggal', [$periode_dari, $periode_sampai])
            ->get()
            ->groupBy('nik')
            ->map(function ($rows) {
                $result = [];
                foreach ($rows as $row) {
                    $result[$row->tanggal] = [
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                        'color' => $row->color,
                    ];
                }
                return $result;
            });

        // 3) Jadwal by-day per karyawan
        $jadwal_byday = DB::table('presensi_jamkerja_byday')
            ->join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi_jamkerja_byday.nik',
                'presensi_jamkerja_byday.hari',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            )
            ->get()
            ->groupBy('nik')
            ->map(function ($rows) {
                $result = [];
                foreach ($rows as $row) {
                    $result[$row->hari] = [
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                        'color' => $row->color,
                    ];
                }
                return $result;
            });

        // 4) Jadwal by-day per departemen & cabang
        $jadwal_bydept = DB::table('presensi_jamkerja_bydept_detail')
            ->join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
            ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi_jamkerja_bydept.kode_dept',
                'presensi_jamkerja_bydept.kode_cabang',
                'presensi_jamkerja_bydept_detail.hari',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            )
            ->get()
            ->groupBy(function ($row) {
                return $row->kode_dept . '|' . $row->kode_cabang;
            })
            ->map(function ($rows) {
                $result = [];
                foreach ($rows as $row) {
                    $result[$row->hari] = [
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                        'color' => $row->color,
                    ];
                }
                return $result;
            });

        // 5) Jadwal Global (Jika diaktifkan)
        $jadwal_global = [];
        if ($generalsetting->global_jamkerja_aktif) {
            $jadwal_global = DB::table('global_jamkerja')
                ->join('presensi_jamkerja', 'global_jamkerja.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->select(
                    'global_jamkerja.hari',
                    'presensi_jamkerja.nama_jam_kerja',
                    'presensi_jamkerja.jam_masuk',
                    'presensi_jamkerja.jam_pulang',
                    'presensi_jamkerja.color'
                )
                ->get()
                ->keyBy('hari')
                ->map(function ($row) {
                    return [
                        'nama_jam_kerja' => $row->nama_jam_kerja,
                        'jam_masuk' => $row->jam_masuk,
                        'jam_pulang' => $row->jam_pulang,
                        'color' => $row->color,
                    ];
                })
                ->toArray();
        }

        $q_karyawan = Karyawan::query();
        $q_karyawan->where(function($q) use ($periode_dari) {
            $q->where('karyawan.status_aktif_karyawan', 1)
              ->orWhere('karyawan.tanggal_nonaktif', '>=', $periode_dari);
        });
        $q_karyawan->select('karyawan.nik', 'karyawan.nik_show', 'nama_karyawan', 'nama_jabatan', 'karyawan.kode_dept', 'nama_dept', 'karyawan.kode_cabang');
        $q_karyawan->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $q_karyawan->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');

        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($request->kode_cabang)) {
                if (in_array($request->kode_cabang, $userCabangs)) {
                    $q_karyawan->where('karyawan.kode_cabang', $request->kode_cabang);
                } else {
                    $q_karyawan->whereRaw('1 = 0');
                }
            } else {
                $q_karyawan->whereIn('karyawan.kode_cabang', $userCabangs);
            }

            if (!empty($request->kode_dept)) {
                if (in_array($request->kode_dept, $userDepartemens)) {
                    $q_karyawan->where('karyawan.kode_dept', $request->kode_dept);
                } else {
                    $q_karyawan->whereRaw('1 = 0');
                }
            } else {
                $q_karyawan->whereIn('karyawan.kode_dept', $userDepartemens);
            }
        } else {
            if (!empty($request->kode_cabang)) {
                $q_karyawan->where('karyawan.kode_cabang', $request->kode_cabang);
            }
            if (!empty($request->kode_dept)) {
                $q_karyawan->where('karyawan.kode_dept', $request->kode_dept);
            }
        }
        if (!empty($request->nik)) {
            $q_karyawan->where('karyawan.nik', $request->nik);
        }

        $q_karyawan->orderBy('karyawan.nama_karyawan');
        $karyawan = $q_karyawan->get();

        $data['periode_dari'] = $periode_dari;
        $data['periode_sampai'] = $periode_sampai;
        $data['jmlhari'] = hitungJumlahHari($periode_dari, $periode_sampai) + 1;
        $data['datalibur'] = getdatalibur($periode_dari, $periode_sampai);
        $data['generalsetting'] = $generalsetting;
        $data['jadwal_bydate'] = $jadwal_bydate;
        $data['jadwal_grup_bydate'] = $jadwal_grup_bydate;
        $data['jadwal_byday'] = $jadwal_byday;
        $data['jadwal_bydept'] = $jadwal_bydept;
        $data['jadwal_global'] = $jadwal_global;
        $data['karyawan'] = $karyawan;
        if ($request->has('exportButton')) {
            return Excel::download(new JadwalExport($data), 'Laporan Jadwal Karyawan ' . $periode_dari . ' - ' . $periode_sampai . '.xlsx');
        }

        return view('laporan.jadwal_cetak', $data);
    }

    public function lemburdetail($nik, $dari, $sampai)
    {
        $karyawan = Karyawan::where('nik', $nik)
            ->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->select('karyawan.*', 'jabatan.nama_jabatan', 'departemen.nama_dept')
            ->first();

        $datalibur = getdatalibur($dari, $sampai);
        $datalembur = getlembur($dari, $sampai);
        $generalsetting = Pengaturanumum::where('id', 1)->first();

        $presensi = Presensi::where('nik', $nik)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->select('tanggal', 'jam_lembur_aktual', 'jam_lembur_netto', 'is_lembur_khusus')
            ->get()
            ->keyBy('tanggal');

        $data['karyawan'] = $karyawan;
        $data['dari'] = $dari;
        $data['sampai'] = $sampai;
        $data['datalibur'] = $datalibur;
        $data['datalembur'] = $datalembur;
        $data['presensi'] = $presensi;
        $data['generalsetting'] = $generalsetting;

        return view('laporan.lembur_detail_cetak', $data);
    }

    public function reimbursement()
    {
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        return view('laporan.reimbursement', $data);
    }

    public function cetakreimbursement(Request $request)
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $dari = $request->dari;
        $sampai = $request->sampai;

        $query = DB::table('reimbursement')
            ->join('karyawan', 'reimbursement.nik', '=', 'karyawan.nik')
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->select('reimbursement.*', 'karyawan.nama_karyawan', 'departemen.nama_dept', 'cabang.nama_cabang')
            ->whereBetween('reimbursement.tanggal_pengajuan', [$dari, $sampai]);

        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($request->kode_cabang)) {
                if (in_array($request->kode_cabang, $userCabangs)) {
                    $query->where('karyawan.kode_cabang', $request->kode_cabang);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereIn('karyawan.kode_cabang', $userCabangs);
            }

            if (!empty($request->kode_dept)) {
                if (in_array($request->kode_dept, $userDepartemens)) {
                    $query->where('karyawan.kode_dept', $request->kode_dept);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereIn('karyawan.kode_dept', $userDepartemens);
            }
        } else {
            if (!empty($request->kode_cabang)) {
                $query->where('karyawan.kode_cabang', $request->kode_cabang);
            }
            if (!empty($request->kode_dept)) {
                $query->where('karyawan.kode_dept', $request->kode_dept);
            }
        }

        if (!empty($request->nik)) {
            $query->where('reimbursement.nik', $request->nik);
        }

        if (!empty($request->status)) {
            $query->where('reimbursement.status', $request->status);
        }

        $reimbursement = $query->orderBy('reimbursement.tanggal_pengajuan', 'desc')->get();

        // Let's load the details for each reimbursement
        $reimbursement_ids = $reimbursement->pluck('id')->toArray();
        $details_by_id = [];
        $rekap = [];
        if (!empty($reimbursement_ids)) {
            $all_details = DB::table('reimbursement_detail')
                ->join('jenis_reimbursement', 'reimbursement_detail.kode_jenis_reimburse', '=', 'jenis_reimbursement.kode_jenis_reimburse')
                ->select('reimbursement_detail.*', 'jenis_reimbursement.nama_jenis')
                ->whereIn('reimbursement_id', $reimbursement_ids)
                ->get();
                
            foreach ($all_details as $det) {
                $details_by_id[$det->reimbursement_id][] = $det;
                
                $nama_jenis = $det->nama_jenis;
                if (!isset($rekap[$nama_jenis])) {
                    $rekap[$nama_jenis] = [
                        'nama_jenis' => $nama_jenis,
                        'total_nominal' => 0,
                        'total_disetujui' => 0
                    ];
                }
                $rekap[$nama_jenis]['total_nominal'] += $det->nominal;
                $rekap[$nama_jenis]['total_disetujui'] += $det->nominal_disetujui ?? 0;
            }
        }

        $data['reimbursement'] = $reimbursement;
        $data['details_by_id'] = $details_by_id;
        $data['rekap'] = $rekap;
        $data['dari'] = $dari;
        $data['sampai'] = $sampai;
        $data['generalsetting'] = $generalsetting;
        $data['namacabang'] = !empty($request->kode_cabang) ? Cabang::where('kode_cabang', $request->kode_cabang)->first()->nama_cabang : 'Semua Cabang';
        $data['namadept'] = !empty($request->kode_dept) ? Departemen::where('kode_dept', $request->kode_dept)->first()->nama_dept : 'Semua Departemen';

        if ($request->has('exportButton')) {
            return Excel::download(new ReimbursementExport($data), 'Laporan Reimbursement ' . $dari . ' - ' . $sampai . '.xlsx');
        }

        return view('laporan.reimbursement_cetak', $data);
    }

    public function pinjaman()
    {
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        return view('laporan.pinjaman', $data);
    }

    public function cetakpinjaman(Request $request)
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();

        if ($request->jenis_laporan == 'rekap') {
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            
            $start_date = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
            $end_date = date('Y-m-t', strtotime($start_date));
            
            // Get employees filtered
            $employees_query = DB::table('karyawan')
                ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
                ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang');

            /** @var \App\Models\User $user */
            $user = User::find(auth()->id());
            if (!$user->isSuperAdmin()) {
                $userCabangs = $user->getCabangCodes();
                $userDepartemens = $user->getDepartemenCodes();
                
                if (!empty($request->kode_cabang)) {
                    if (in_array($request->kode_cabang, $userCabangs)) {
                        $employees_query->where('karyawan.kode_cabang', $request->kode_cabang);
                    } else {
                        $employees_query->whereRaw('1 = 0');
                    }
                } else {
                    $employees_query->whereIn('karyawan.kode_cabang', $userCabangs);
                }

                if (!empty($request->kode_dept)) {
                    if (in_array($request->kode_dept, $userDepartemens)) {
                        $employees_query->where('karyawan.kode_dept', $request->kode_dept);
                    } else {
                        $employees_query->whereRaw('1 = 0');
                    }
                } else {
                    $employees_query->whereIn('karyawan.kode_dept', $userDepartemens);
                }
            } else {
                if (!empty($request->kode_cabang)) {
                    $employees_query->where('karyawan.kode_cabang', $request->kode_cabang);
                }
                if (!empty($request->kode_dept)) {
                    $employees_query->where('karyawan.kode_dept', $request->kode_dept);
                }
            }
            if (!empty($request->nik)) {
                $employees_query->where('karyawan.nik', $request->nik);
            }

            $karyawan = $employees_query->select(
                'karyawan.nik', 
                'karyawan.nama_karyawan', 
                'departemen.nama_dept', 
                'cabang.nama_cabang'
            )
            ->orderBy('karyawan.nama_karyawan')
            ->get();
            
            $rekap = [];
            foreach ($karyawan as $k) {
                // 1. Saldo Awal (semua pinjaman yang dibuat sebelum $start_date dikurangi semua pembayaran sebelum $start_date)
                $total_pinjaman_awal = DB::table('pinjaman')
                    ->where('nik', $k->nik)
                    ->where('status', '!=', 'B')
                    ->where('tanggal_pinjaman', '<', $start_date)
                    ->sum('jumlah_pinjaman');
                    
                $total_bayar_awal = DB::table('pembayaran_pinjaman')
                    ->join('pinjaman', 'pembayaran_pinjaman.pinjaman_id', '=', 'pinjaman.id')
                    ->where('pinjaman.nik', $k->nik)
                    ->where('pinjaman.status', '!=', 'B')
                    ->where('pembayaran_pinjaman.tanggal_bayar', '<', $start_date)
                    ->sum('pembayaran_pinjaman.jumlah_bayar');
                    
                $saldo_awal = $total_pinjaman_awal - $total_bayar_awal;
                
                // 2. Penambah (pinjaman baru pada bulan ini)
                $penambah = DB::table('pinjaman')
                    ->where('nik', $k->nik)
                    ->where('status', '!=', 'B')
                    ->whereBetween('tanggal_pinjaman', [$start_date, $end_date])
                    ->sum('jumlah_pinjaman');
                    
                // 3. Pembayaran Potong Gaji (jenis_pembayaran = 'C' pada bulan ini)
                $potong_gaji = DB::table('pembayaran_pinjaman')
                    ->join('pinjaman', 'pembayaran_pinjaman.pinjaman_id', '=', 'pinjaman.id')
                    ->where('pinjaman.nik', $k->nik)
                    ->where('pinjaman.status', '!=', 'B')
                    ->where('pembayaran_pinjaman.jenis_pembayaran', 'C')
                    ->whereBetween('pembayaran_pinjaman.tanggal_bayar', [$start_date, $end_date])
                    ->sum('pembayaran_pinjaman.jumlah_bayar');
                    
                // 4. Pembayaran Bayar Cash (jenis_pembayaran IN ('M', 'P') pada bulan ini)
                $bayar_cash = DB::table('pembayaran_pinjaman')
                    ->join('pinjaman', 'pembayaran_pinjaman.pinjaman_id', '=', 'pinjaman.id')
                    ->where('pinjaman.nik', $k->nik)
                    ->where('pinjaman.status', '!=', 'B')
                    ->whereIn('pembayaran_pinjaman.jenis_pembayaran', ['M', 'P'])
                    ->whereBetween('pembayaran_pinjaman.tanggal_bayar', [$start_date, $end_date])
                    ->sum('pembayaran_pinjaman.jumlah_bayar');
                    
                $saldo_akhir = $saldo_awal + $penambah - $potong_gaji - $bayar_cash;
                
                // Hanya tampilkan jika memiliki transaksi atau saldo
                if ($saldo_awal > 0 || $penambah > 0 || $potong_gaji > 0 || $bayar_cash > 0) {
                    $rekap[] = (object)[
                        'nik' => $k->nik,
                        'nama_karyawan' => $k->nama_karyawan,
                        'nama_dept' => $k->nama_dept,
                        'nama_cabang' => $k->nama_cabang,
                        'saldo_awal' => $saldo_awal,
                        'penambah' => $penambah,
                        'potong_gaji' => $potong_gaji,
                        'bayar_cash' => $bayar_cash,
                        'saldo_akhir' => $saldo_akhir
                    ];
                }
            }
            
            $data['rekap'] = collect($rekap);
            $data['bulan'] = $bulan;
            $data['tahun'] = $tahun;
            $data['generalsetting'] = $generalsetting;
            $data['namacabang'] = !empty($request->kode_cabang) ? Cabang::where('kode_cabang', $request->kode_cabang)->first()->nama_cabang : 'Semua Cabang';
            $data['namadept'] = !empty($request->kode_dept) ? Departemen::where('kode_dept', $request->kode_dept)->first()->nama_dept : 'Semua Departemen';
            
            if ($request->has('exportButton')) {
                return Excel::download(new \App\Exports\PinjamanExport($data, 'laporan.pinjaman_rekap_excel'), 'Laporan Rekap Pinjaman Bulanan ' . $bulan . '-' . $tahun . '.xlsx');
            }
            
            return view('laporan.pinjaman_rekap_cetak', $data);
        }

        $dari = $request->dari;
        $sampai = $request->sampai;

        $query = DB::table('pinjaman')
            ->join('karyawan', 'pinjaman.nik', '=', 'karyawan.nik')
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->select('pinjaman.*', 'karyawan.nama_karyawan', 'departemen.nama_dept', 'cabang.nama_cabang')
            ->whereBetween('pinjaman.tanggal_pinjaman', [$dari, $sampai]);

        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($request->kode_cabang)) {
                if (in_array($request->kode_cabang, $userCabangs)) {
                    $query->where('karyawan.kode_cabang', $request->kode_cabang);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereIn('karyawan.kode_cabang', $userCabangs);
            }

            if (!empty($request->kode_dept)) {
                if (in_array($request->kode_dept, $userDepartemens)) {
                    $query->where('karyawan.kode_dept', $request->kode_dept);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereIn('karyawan.kode_dept', $userDepartemens);
            }
        } else {
            if (!empty($request->kode_cabang)) {
                $query->where('karyawan.kode_cabang', $request->kode_cabang);
            }
            if (!empty($request->kode_dept)) {
                $query->where('karyawan.kode_dept', $request->kode_dept);
            }
        }

        if (!empty($request->nik)) {
            $query->where('pinjaman.nik', $request->nik);
        }

        if (!empty($request->status)) {
            $query->where('pinjaman.status', $request->status);
        }

        $pinjaman = $query->orderBy('pinjaman.tanggal_pinjaman', 'desc')->get();

        $data['pinjaman'] = $pinjaman;
        $data['dari'] = $dari;
        $data['sampai'] = $sampai;
        $data['generalsetting'] = $generalsetting;
        $data['namacabang'] = !empty($request->kode_cabang) ? Cabang::where('kode_cabang', $request->kode_cabang)->first()->nama_cabang : 'Semua Cabang';
        $data['namadept'] = !empty($request->kode_dept) ? Departemen::where('kode_dept', $request->kode_dept)->first()->nama_dept : 'Semua Departemen';

        if ($request->has('exportButton')) {
            return Excel::download(new \App\Exports\PinjamanExport($data), 'Laporan Pinjaman ' . $dari . ' - ' . $sampai . '.xlsx');
        }

        return view('laporan.pinjaman_cetak', $data);
    }
}
