<?php

namespace App\Http\Controllers;

use App\Charts\JeniskelaminkaryawanChart;
use App\Charts\PendidikankaryawanChart;
use App\Charts\StatusKaryawanChart;
use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Denda;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\Pengumuman;
use App\Models\User;
use App\Models\Userkaryawan;
use App\Models\Pengaturanumum;
use App\Http\Controllers\KaryawanApprovalController;
use App\Jobs\SendWaMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class DashboardController extends Controller
{
    public function index(StatusKaryawanChart $chart, JeniskelaminkaryawanChart $jkchart, PendidikankaryawanChart $pddchart, Request $request)
    {
        $agent = new Agent();
        $user = User::where('id', auth()->user()->id)->first();

        // Gunakan Carbon dengan timezone aplikasi (dari config/app.php)
        // BUKAN date() yang menggunakan timezone PHP default
        $hari_ini = Carbon::now(config('app.timezone'))->format('Y-m-d');
        if ($user->hasRole('karyawan')) {
            $userkaryawan = Userkaryawan::where('id_user', auth()->user()->id)->first();
            $data['karyawan'] = Karyawan::where('nik', $userkaryawan->nik)
                ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
                ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
                ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
                ->first();

            $data['presensi'] = Presensi::where('presensi.nik', $userkaryawan->nik)->where('presensi.tanggal', $hari_ini)->first();
            $data['datapresensi'] = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('presensi.nik', $userkaryawan->nik)
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
            $data['rekappresensi'] = Presensi::select(
                DB::raw("SUM(IF(status='h',1,0)) as hadir"),
                DB::raw("SUM(IF(status='i',1,0)) as izin"),
                DB::raw("SUM(IF(status='s',1,0)) as sakit"),
                DB::raw("SUM(IF(status='a',1,0)) as alpa"),
                DB::raw("SUM(IF(status='c',1,0)) as cuti")
            )
                ->groupBy('presensi.nik')
                ->whereRaw('MONTH(presensi.tanggal) = MONTH(?)', [$hari_ini])
                ->whereRaw('YEAR(presensi.tanggal) = YEAR(?)', [$hari_ini])
                ->where('presensi.nik', $userkaryawan->nik)
                ->first();

            $data['lembur'] = Lembur::where('nik', $userkaryawan->nik)
                ->whereIn('status', [0, 1])
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            $data['notiflembur'] = Lembur::where('nik', $userkaryawan->nik)
                ->whereIn('status', [0, 1])
                ->where(function($query) {
                    $query->whereNull('lembur_in')
                        ->orWhereNull('lembur_out');
                })
                ->count();

            // Cek apakah hari ini adalah ulang tahun karyawan
            $isBirthday = false;
            $umur = null;
            if ($data['karyawan'] && $data['karyawan']->tanggal_lahir) {
                $tanggalLahir = Carbon::parse($data['karyawan']->tanggal_lahir);
                $today = Carbon::now();
                if ($tanggalLahir->month == $today->month && $tanggalLahir->day == $today->day) {
                    $isBirthday = true;
                    $umur = $tanggalLahir->age;
                }
            }
            $data['is_birthday'] = $isBirthday;
            $data['umur'] = $umur;

            // Cek Notifikasi Kontrak Berakhir (H-30)
            $kontrak = DB::table('kontrak')
                ->where('nik', $userkaryawan->nik)
                ->where('status_kontrak', '1')
                ->where('jenis_kontrak', '!=', 'T')
                ->orderBy('sampai', 'desc')
                ->first();

            $notif_kontrak = null;
            if ($kontrak) {
                $tgl_akhir = Carbon::parse($kontrak->sampai);
                $today = Carbon::now(config('app.timezone'));
                $sisa_hari = $today->diffInDays($tgl_akhir, false); // false agar negatif jika lewat

                // Jika sisa hari <= 30 hari dan belum lewat (atau lewat hari ini)
                // Kita anggap sisa_hari < 0 berarti sudah expired
                if ($sisa_hari >= 0 && $sisa_hari <= 30) {
                     $notif_kontrak = [
                        'sisa_hari' => $sisa_hari,
                        'tanggal_akhir' => $tgl_akhir->translatedFormat('d F Y')
                    ];
                }
            }
            $data['notif_kontrak'] = $notif_kontrak;

            // Cek Notifikasi SP Aktif
            $notif_sp = DB::table('pelanggaran')
                ->where('nik', $userkaryawan->nik)
                ->where('dari', '<=', $today->toDateString())
                ->where('sampai', '>=', $today->toDateString())
                ->first();
            
            $data['notif_sp'] = $notif_sp;

            // Cek Pengumuman Aktif (Ambil yang terakhir dibuat)
            $data['pengumuman'] = Pengumuman::orderBy('created_at', 'desc')->first();
            $data['namasettings'] = Pengaturanumum::first();
            $data['denda_list'] = Denda::orderBy('dari')->get()->toArray();
            $data['pendingApprovalCount'] = KaryawanApprovalController::getPendingCount(auth()->user()->id);
            $data['bulan_skrg'] = Carbon::parse($hari_ini)->translatedFormat('F');
            $data['tahun_skrg'] = Carbon::parse($hari_ini)->year;

            return view('dashboard.karyawan', $data);
        } else {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            //Dashboard Admin
            $sk = new Karyawan();

            // Resolve target branches and departments based on user privileges and selected filters
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            $selectedCabang = $request->kode_cabang;
            $selectedDept = $request->kode_dept;

            if (!$user->isSuperAdmin()) {
                if (empty($userCabangs) || empty($userDepartemens)) {
                    $targetCabangs = ['INVALID'];
                    $targetDepartemens = ['INVALID'];
                } else {
                    if (!empty($selectedCabang)) {
                        $targetCabangs = in_array($selectedCabang, $userCabangs) ? [$selectedCabang] : ['INVALID'];
                    } else {
                        $targetCabangs = $userCabangs;
                    }

                    if (!empty($selectedDept)) {
                        $targetDepartemens = in_array($selectedDept, $userDepartemens) ? [$selectedDept] : ['INVALID'];
                    } else {
                        $targetDepartemens = $userDepartemens;
                    }
                }
            } else {
                $targetCabangs = !empty($selectedCabang) ? [$selectedCabang] : [];
                $targetDepartemens = !empty($selectedDept) ? [$selectedDept] : [];
            }

            // 1. Rekap Status Karyawan
            $filterRequest = new Request();
            if (!empty($targetCabangs)) {
                $filterRequest->merge(['kode_cabang' => count($targetCabangs) == 1 ? $targetCabangs[0] : $targetCabangs]);
            }
            if (!empty($targetDepartemens)) {
                $filterRequest->merge(['kode_dept' => count($targetDepartemens) == 1 ? $targetDepartemens[0] : $targetDepartemens]);
            }
            $data['status_karyawan'] = $sk->getRekapstatuskaryawan($filterRequest);

            // 2. Charts
            $chartRequest = new Request();
            if (!empty($targetCabangs)) {
                $chartRequest->merge(['user_cabangs' => $targetCabangs]);
            }
            if (!empty($targetDepartemens)) {
                $chartRequest->merge(['user_departemens' => $targetDepartemens]);
            }
            $data['chart'] = $chart->build($chartRequest);
            $data['jkchart'] = $jkchart->build($chartRequest);
            $data['pddchart'] = $pddchart->build($chartRequest);

            // 3. Rekap Presensi
            $queryPresensi = Presensi::query();
            $queryPresensi->join('karyawan', 'presensi.nik', '=', 'karyawan.nik');
            $queryPresensi->select(
                DB::raw("SUM(IF(status='h',1,0)) as hadir"),
                DB::raw("SUM(IF(status='i',1,0)) as izin"),
                DB::raw("SUM(IF(status='s',1,0)) as sakit"),
                DB::raw("SUM(IF(status='a',1,0)) as alpa"),
                DB::raw("SUM(IF(status='c',1,0)) as cuti")
            );

            if (!empty($targetCabangs)) {
                $queryPresensi->whereIn('karyawan.kode_cabang', $targetCabangs);
            }
            if (!empty($targetDepartemens)) {
                $queryPresensi->whereIn('karyawan.kode_dept', $targetDepartemens);
            }

            if (!empty($request->tanggal)) {
                $queryPresensi->where('tanggal', $request->tanggal);
            } else {
                $queryPresensi->where('tanggal', Carbon::now(config('app.timezone'))->format('Y-m-d'));
            }
            $data['rekappresensi'] = $queryPresensi->first();

            $data['departemen'] = $user->getDepartemen();
            $data['cabang'] = $user->getCabang();
            
            // 4. Birthday Karyawan
            $today = Carbon::now(config('app.timezone'));
            $data['birthday'] = Karyawan::where('status_aktif_karyawan', 1)
                ->whereMonth('tanggal_lahir', $today->month)
                ->whereDay('tanggal_lahir', $today->day)
                ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
                ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
                ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
                ->select(
                    'karyawan.*',
                    'jabatan.nama_jabatan',
                    'departemen.nama_dept',
                    'cabang.nama_cabang',
                    'karyawan.status_karyawan'
                )
                ->when(!empty($targetCabangs), function ($query) use ($targetCabangs) {
                    $query->whereIn('karyawan.kode_cabang', $targetCabangs);
                })
                ->when(!empty($targetDepartemens), function ($query) use ($targetDepartemens) {
                    $query->whereIn('karyawan.kode_dept', $targetDepartemens);
                })
                ->orderBy('tanggal_lahir', 'asc')
                ->get();

            // 5. Rekap Kontrak
            $data['kontrak_lewat'] = $sk->getRekapkontrak(0, !empty($targetCabangs) ? $targetCabangs : null, !empty($targetDepartemens) ? $targetDepartemens : null);
            $data['kontrak_bulanini'] = $sk->getRekapkontrak(1, !empty($targetCabangs) ? $targetCabangs : null, !empty($targetDepartemens) ? $targetDepartemens : null);
            $data['kontrak_bulandepan'] = $sk->getRekapkontrak(2, !empty($targetCabangs) ? $targetCabangs : null, !empty($targetDepartemens) ? $targetDepartemens : null);
            $data['kontrak_duabulan'] = $sk->getRekapkontrak(3, !empty($targetCabangs) ? $targetCabangs : null, !empty($targetDepartemens) ? $targetDepartemens : null);
            // Storage Usage Info
            if ($user->hasRole('master admin')) {
                try {
                    $disk_path = base_path();
                    $total_space = @disk_total_space($disk_path);
                    $free_space = @disk_free_space($disk_path);

                    if ($total_space !== false && $free_space !== false) {
                        $used_space = $total_space - $free_space;
                        $percentage = ($total_space > 0) ? round(($used_space / $total_space) * 100, 2) : 0;

                        $data['storage_info'] = [
                            'total' => round($total_space / (1024 * 1024 * 1024), 2) . ' GB',
                            'used' => round($used_space / (1024 * 1024 * 1024), 2) . ' GB',
                            'free' => round($free_space / (1024 * 1024 * 1024), 2) . ' GB',
                            'percentage' => $percentage
                        ];
                    } else {
                        $data['storage_info'] = null;
                    }
                } catch (\Exception $e) {
                    $data['storage_info'] = null;
                }
            }

            // Expiration warning alert (7 days or less)
            $data['expired_alert'] = null;
            $setting = Pengaturanumum::first();
            if ($setting && $setting->expired) {
                $today = Carbon::today();
                $expiredDate = Carbon::parse($setting->expired);
                $diffInDays = $today->diffInDays($expiredDate, false);

                if ($diffInDays <= 7) {
                    $data['expired_alert'] = [
                        'days_left' => $diffInDays,
                        'date' => $expiredDate->translatedFormat('d F Y'),
                        'is_expired' => $diffInDays < 0
                    ];
                }
            }

            return view('dashboard.dashboard', $data);
        }
    }

    public function kirimUcapanBirthday(Request $request)
    {
        try {
            // Ambil karyawan yang ulang tahun hari ini (menggunakan timezone aplikasi)
            $today = Carbon::now(config('app.timezone'));
            $birthday = Karyawan::where('status_aktif_karyawan', 1)
                ->whereMonth('tanggal_lahir', $today->month)
                ->whereDay('tanggal_lahir', $today->day)
                ->when($request->kode_cabang, function ($query) use ($request) {
                    $query->where('kode_cabang', $request->kode_cabang);
                })
                ->when($request->kode_dept, function ($query) use ($request) {
                    $query->where('kode_dept', $request->kode_dept);
                })
                ->whereNotNull('no_hp')
                ->where('no_hp', '!=', '')
                ->get();

            if ($birthday->count() == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada karyawan yang ulang tahun hari ini atau tidak ada nomor HP yang tersedia.'
                ], 400);
            }

            $count = 0;
            foreach ($birthday as $karyawan) {
                // Hitung umur
                $umur = Carbon::parse($karyawan->tanggal_lahir)->age;

                // Format pesan ucapan ulang tahun
                $message = "🎉 *Selamat Ulang Tahun!* 🎂\n\n";
                $message .= "Halo *{$karyawan->nama_karyawan}*,\n\n";
                $message .= "Di hari yang istimewa ini, kami ingin mengucapkan:\n\n";
                $message .= "🎂 *Selamat Ulang Tahun yang ke-{$umur}!* 🎂\n\n";
                $message .= "Semoga di hari ulang tahunmu ini:\n";
                $message .= "✨ Panjang umur\n";
                $message .= "✨ Sehat selalu\n";
                $message .= "✨ Bahagia selalu\n";
                $message .= "✨ Sukses dalam karir\n";
                $message .= "✨ Diberkahi rezeki yang berlimpah\n\n";
                $message .= "Terima kasih atas dedikasi dan kontribusinya selama ini. Semoga hubungan kerja kita terus berjalan dengan baik!\n\n";
                $message .= "*Salam Hangat,*\nTim HR";

                // Format nomor HP (hapus 0 di depan jika ada, pastikan format 62xxx)
                $phoneNumber = $karyawan->no_hp;
                $phoneNumber = preg_replace('/^0+/', '', $phoneNumber);
                if (!str_starts_with($phoneNumber, '62')) {
                    $phoneNumber = '62' . $phoneNumber;
                }

                // Dispatch job untuk mengirim WhatsApp
                SendWaMessage::dispatch($phoneNumber, $message, true);
                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "Ucapan ulang tahun sedang dikirim ke {$count} karyawan."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getKaryawanPresensi(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $userCabangs = $user->getCabangCodes();
        $userDepartemens = $user->getDepartemenCodes();
        
        $selectedCabang = $request->kode_cabang;
        $selectedDept = $request->kode_dept;

        if (!$user->isSuperAdmin()) {
            if (empty($userCabangs) || empty($userDepartemens)) {
                $targetCabangs = ['INVALID'];
                $targetDepartemens = ['INVALID'];
            } else {
                if (!empty($selectedCabang)) {
                    $targetCabangs = in_array($selectedCabang, $userCabangs) ? [$selectedCabang] : ['INVALID'];
                } else {
                    $targetCabangs = $userCabangs;
                }

                if (!empty($selectedDept)) {
                    $targetDepartemens = in_array($selectedDept, $userDepartemens) ? [$selectedDept] : ['INVALID'];
                } else {
                    $targetDepartemens = $userDepartemens;
                }
            }
        } else {
            $targetCabangs = !empty($selectedCabang) ? [$selectedCabang] : [];
            $targetDepartemens = !empty($selectedDept) ? [$selectedDept] : [];
        }

        $tanggal = $request->tanggal ?? Carbon::now(config('app.timezone'))->format('Y-m-d');
        $status = $request->status;

        $query = Presensi::query();
        $query->join('karyawan', 'presensi.nik', '=', 'karyawan.nik');
        $query->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $query->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $query->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang');

        if ($status == 'h') {
            $query->leftJoin('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja');
        } elseif ($status == 'i') {
            $query->leftJoin('presensi_izinabsen_approve', 'presensi.id', '=', 'presensi_izinabsen_approve.id_presensi');
            $query->leftJoin('presensi_izinabsen', 'presensi_izinabsen_approve.kode_izin', '=', 'presensi_izinabsen.kode_izin');
        } elseif ($status == 's') {
            $query->leftJoin('presensi_izinsakit_approve', 'presensi.id', '=', 'presensi_izinsakit_approve.id_presensi');
            $query->leftJoin('presensi_izinsakit', 'presensi_izinsakit_approve.kode_izin_sakit', '=', 'presensi_izinsakit.kode_izin_sakit');
        } elseif ($status == 'c') {
            $query->leftJoin('presensi_izincuti_approve', 'presensi.id', '=', 'presensi_izincuti_approve.id_presensi');
            $query->leftJoin('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti');
        }

        $query->where('presensi.tanggal', $tanggal);
        $query->where('presensi.status', $status);

        if (!empty($targetCabangs)) {
            $query->whereIn('karyawan.kode_cabang', $targetCabangs);
        }
        if (!empty($targetDepartemens)) {
            $query->whereIn('karyawan.kode_dept', $targetDepartemens);
        }

        $query->select(
            'karyawan.nik',
            'karyawan.nama_karyawan',
            'karyawan.foto',
            'jabatan.nama_jabatan',
            'departemen.nama_dept',
            'cabang.nama_cabang',
            'presensi.jam_in',
            'presensi.jam_out',
            'presensi.status'
        );

        if ($status == 'h') {
            $query->addSelect('presensi_jamkerja.nama_jam_kerja', 'presensi_jamkerja.jam_masuk', 'presensi_jamkerja.jam_pulang');
        } elseif ($status == 'i') {
            $query->addSelect('presensi_izinabsen.keterangan as keterangan');
        } elseif ($status == 's') {
            $query->addSelect('presensi_izinsakit.keterangan as keterangan');
        } elseif ($status == 'c') {
            $query->addSelect('presensi_izincuti.keterangan as keterangan');
        }

        $karyawanList = $query->orderBy('karyawan.nama_karyawan', 'asc')->get();

        $title = 'List Karyawan - ';
        if ($status == 'h') {
            $title .= 'Hadir';
        } elseif ($status == 'i') {
            $title .= 'Izin';
        } elseif ($status == 's') {
            $title .= 'Sakit';
        } elseif ($status == 'c') {
            $title .= 'Cuti';
        }

        $html = view('dashboard.karyawan_list_modal', compact('karyawanList', 'status'))->render();

        return response()->json([
            'success' => true,
            'title' => $title,
            'html' => $html
        ]);
    }
}
