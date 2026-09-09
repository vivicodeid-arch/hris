<?php

namespace App\Providers;

use App\Models\Izinabsen;
use App\Models\Izincuti;
use App\Models\Izindinas;
use App\Models\Izinsakit;
use App\Models\Lembur;
use App\Models\Pengaturanumum;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class Globalprovider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Guard $auth): void
    {
        // Share general_setting globally with try-catch to prevent errors during dump-autoload
        try {
            $settings = Pengaturanumum::first();
            View::share('general_setting', $settings);

            // Modern theme variables (shared globally for modern layout)
            $scheme = $settings->mobile_theme_scheme ?? 'green';
            $themeColors = [
                'green' => ['primary' => '#32745e', 'primary_light' => '#58907D', 'bg_body' => '#f0fdf9'],
                'blue' => ['primary' => '#0d47a1', 'primary_light' => '#1976d2', 'bg_body' => '#eff6ff'],
                'red' => ['primary' => '#b71c1c', 'primary_light' => '#d32f2f', 'bg_body' => '#fef2f2'],
                'purple' => ['primary' => '#4a148c', 'primary_light' => '#7b1fa2', 'bg_body' => '#faf5ff'],
                'orange' => ['primary' => '#e65100', 'primary_light' => '#f57c00', 'bg_body' => '#fff8f1'],
                'rose' => ['primary' => '#ce8291', 'primary_light' => '#ef95a6', 'bg_body' => '#fff5f7'],
            ];
            $t = $themeColors[$scheme] ?? $themeColors['green'];
            $isDark = false;
            View::share('t', $t);
            View::share('isDark', $isDark);
        } catch (\Exception $e) {
            View::share('general_setting', null);
            View::share('t', ['primary' => '#32745e', 'primary_light' => '#58907D', 'bg_body' => '#f0fdf9']);
            View::share('isDark', false);
        }


        view()->composer('*', function ($view) use ($auth) {
            static $composed = false;
            if ($composed)
                return;
            $composed = true;
            if ($auth->check()) {
                /** @var \App\Models\User $user */
                $user = $auth->user();
                $isSuperAdmin = $user->isSuperAdmin();
                $userCabangs = $isSuperAdmin ? [] : $user->getCabangCodes();
                $userDepartemens = $isSuperAdmin ? [] : $user->getDepartemenCodes();

                $applyFilter = function ($query) use ($isSuperAdmin, $userCabangs, $userDepartemens) {
                    if (!$isSuperAdmin) {
                        $query->join('karyawan', $query->getModel()->getTable() . '.nik', '=', 'karyawan.nik');
                        if (!empty($userCabangs)) {
                            $query->whereIn('karyawan.kode_cabang', $userCabangs);
                        }
                        if (!empty($userDepartemens)) {
                            $query->whereIn('karyawan.kode_dept', $userDepartemens);
                        }
                    }
                };

                $izinLevels = [];
                $reimbursementLevels = [];
                $koreksiLevels = [];

                if (!$isSuperAdmin) {
                    $targetUser = $user;
                    if ($user->hasRole('karyawan')) {
                        $userkaryawan = \App\Models\Userkaryawan::where('id_user', $user->id)->first();
                        if ($userkaryawan && $userkaryawan->approval_admin_id) {
                            $admin = \App\Models\User::find($userkaryawan->approval_admin_id);
                            if ($admin) {
                                $targetUser = $admin;
                            }
                        }
                    }
                    $userRole = $targetUser->getRoleNames()->first();
                    $layers = \App\Models\ApprovalLayer::where('role_name', $userRole)->get();
                    
                    foreach ($layers as $l) {
                        if ($l->feature === 'IZIN') {
                            $izinLevels[] = $l->level;
                        } elseif ($l->feature === 'REIMBURSEMENT') {
                            $reimbursementLevels[] = $l->level;
                        } elseif ($l->feature === 'KOREKSI') {
                            $koreksiLevels[] = $l->level;
                        }
                    }
                }

                // Queries for Counts
                $q_izinabsen = Izinabsen::where('presensi_izinabsen.status', 0);
                $applyFilter($q_izinabsen);
                if (!$isSuperAdmin) {
                    if (!empty($izinLevels)) {
                        $q_izinabsen->whereIn('presensi_izinabsen.approval_step', $izinLevels);
                    } else {
                        $q_izinabsen->whereRaw('1 = 0');
                    }
                }
                $notifikasi_izinabsen = $q_izinabsen->count();

                $q_izinsakit = Izinsakit::where('presensi_izinsakit.status', 0);
                $applyFilter($q_izinsakit);
                if (!$isSuperAdmin) {
                    if (!empty($izinLevels)) {
                        $q_izinsakit->whereIn('presensi_izinsakit.approval_step', $izinLevels);
                    } else {
                        $q_izinsakit->whereRaw('1 = 0');
                    }
                }
                $notifikasi_izinsakit = $q_izinsakit->count();

                $q_izincuti = Izincuti::where('presensi_izincuti.status', 0);
                $applyFilter($q_izincuti);
                if (!$isSuperAdmin) {
                    if (!empty($izinLevels)) {
                        $q_izincuti->whereIn('presensi_izincuti.approval_step', $izinLevels);
                    } else {
                        $q_izincuti->whereRaw('1 = 0');
                    }
                }
                $notifikasi_izincuti = $q_izincuti->count();

                $q_lembur = Lembur::where('lembur.status', 0);
                $applyFilter($q_lembur);
                $notifikasi_lembur = $q_lembur->count();

                $q_izindinas = Izindinas::where('presensi_izindinas.status', 0);
                $applyFilter($q_izindinas);
                if (!$isSuperAdmin) {
                    if (!empty($izinLevels)) {
                        $q_izindinas->whereIn('presensi_izindinas.approval_step', $izinLevels);
                    } else {
                        $q_izindinas->whereRaw('1 = 0');
                    }
                }
                $notifikasi_izin_dinas = $q_izindinas->count();

                $q_ajuanjadwal = \App\Models\AjuanJadwal::where('status', 'p');
                $applyFilter($q_ajuanjadwal);
                $notifikasi_ajuan_jadwal = $q_ajuanjadwal->count();

                $q_koreksi = \App\Models\Koreksi::where('presensi_koreksi.status', '0');
                $applyFilter($q_koreksi);
                if (!$isSuperAdmin) {
                    if (!empty($koreksiLevels)) {
                        $q_koreksi->whereIn('presensi_koreksi.approval_step', $koreksiLevels);
                    } else {
                        $q_koreksi->whereRaw('1 = 0');
                    }
                }
                $notifikasi_koreksi = $q_koreksi->count();

                $q_reimbursement = \App\Models\Reimbursement::where('reimbursement.status', 'P');
                $applyFilter($q_reimbursement);
                if (!$isSuperAdmin) {
                    if (!empty($reimbursementLevels)) {
                        $q_reimbursement->whereIn('reimbursement.approval_step', $reimbursementLevels);
                    } else {
                        $q_reimbursement->whereRaw('1 = 0');
                    }
                }
                $notifikasi_reimbursement = $q_reimbursement->count();

                // Queries for Data List (already joining karyawan in original code, but we need to handle it carefully)
                // Actually, original code joined karyawan below. My applyFilter also joins. 
                // To avoid double join, I should construct these queries fresh or be careful.
                // The original code for data_izin used joins for selecting names.

                $data_izinabsen = Izinabsen::select('presensi_izinabsen.nik', 'nama_karyawan', DB::raw('"i" as status'), 'presensi_izinabsen.created_at')
                    ->where('presensi_izinabsen.status', 0)
                    ->join('karyawan', 'presensi_izinabsen.nik', '=', 'karyawan.nik');

                if (!$isSuperAdmin) {
                    if (!empty($userCabangs))
                        $data_izinabsen->whereIn('karyawan.kode_cabang', $userCabangs);
                    if (!empty($userDepartemens))
                        $data_izinabsen->whereIn('karyawan.kode_dept', $userDepartemens);
                    if (!empty($izinLevels)) {
                        $data_izinabsen->whereIn('presensi_izinabsen.approval_step', $izinLevels);
                    } else {
                        $data_izinabsen->whereRaw('1 = 0');
                    }
                }

                $data_izinsakit = Izinsakit::select('presensi_izinsakit.nik', 'nama_karyawan', DB::raw('"s" as status'), 'presensi_izinsakit.created_at')
                    ->where('presensi_izinsakit.status', 0)
                    ->join('karyawan', 'presensi_izinsakit.nik', '=', 'karyawan.nik');
                if (!$isSuperAdmin) {
                    if (!empty($userCabangs))
                        $data_izinsakit->whereIn('karyawan.kode_cabang', $userCabangs);
                    if (!empty($userDepartemens))
                        $data_izinsakit->whereIn('karyawan.kode_dept', $userDepartemens);
                    if (!empty($izinLevels)) {
                        $data_izinsakit->whereIn('presensi_izinsakit.approval_step', $izinLevels);
                    } else {
                        $data_izinsakit->whereRaw('1 = 0');
                    }
                }

                $data_izincuti = Izincuti::select('presensi_izincuti.nik', 'nama_karyawan', DB::raw('"c" as status'), 'presensi_izincuti.created_at')
                    ->where('presensi_izincuti.status', 0)
                    ->join('karyawan', 'presensi_izincuti.nik', '=', 'karyawan.nik');
                if (!$isSuperAdmin) {
                    if (!empty($userCabangs))
                        $data_izincuti->whereIn('karyawan.kode_cabang', $userCabangs);
                    if (!empty($userDepartemens))
                        $data_izincuti->whereIn('karyawan.kode_dept', $userDepartemens);
                    if (!empty($izinLevels)) {
                        $data_izincuti->whereIn('presensi_izincuti.approval_step', $izinLevels);
                    } else {
                        $data_izincuti->whereRaw('1 = 0');
                    }
                }

                $data_izin_dinas = Izindinas::select('presensi_izindinas.nik', 'nama_karyawan', DB::raw('"d" as status'), 'presensi_izindinas.created_at')
                    ->where('presensi_izindinas.status', 0)
                    ->join('karyawan', 'presensi_izindinas.nik', '=', 'karyawan.nik');
                if (!$isSuperAdmin) {
                    if (!empty($userCabangs))
                        $data_izin_dinas->whereIn('karyawan.kode_cabang', $userCabangs);
                    if (!empty($userDepartemens))
                        $data_izin_dinas->whereIn('karyawan.kode_dept', $userDepartemens);
                    if (!empty($izinLevels)) {
                        $data_izin_dinas->whereIn('presensi_izindinas.approval_step', $izinLevels);
                    } else {
                        $data_izin_dinas->whereRaw('1 = 0');
                    }
                }

                $data_izin = $data_izinabsen->unionAll($data_izinsakit)->unionAll($data_izincuti)->unionAll($data_izin_dinas)->get();

                $q_reimbursement_list = \App\Models\Reimbursement::where('reimbursement.status', 'P')
                    ->join('karyawan', 'reimbursement.nik', '=', 'karyawan.nik');
                if (!$isSuperAdmin) {
                    if (!empty($userCabangs))
                        $q_reimbursement_list->whereIn('karyawan.kode_cabang', $userCabangs);
                    if (!empty($userDepartemens))
                        $q_reimbursement_list->whereIn('karyawan.kode_dept', $userDepartemens);
                    if (!empty($reimbursementLevels)) {
                        $q_reimbursement_list->whereIn('reimbursement.approval_step', $reimbursementLevels);
                    } else {
                        $q_reimbursement_list->whereRaw('1 = 0');
                    }
                }
                $data_reimbursement_pending = $q_reimbursement_list->select('reimbursement.*', 'karyawan.nama_karyawan')->get();

                $notifikasi_ajuan_absen = $notifikasi_izinabsen + $notifikasi_izincuti + $notifikasi_izinsakit + $notifikasi_izin_dinas + $notifikasi_ajuan_jadwal + $notifikasi_koreksi;
                $shareddata = [
                     'notifikasi_izinabsen' => $notifikasi_izinabsen,
                     'notifikasi_izinsakit' => $notifikasi_izinsakit,
                     'notifikasi_izincuti' => $notifikasi_izincuti,
                     'notifikasi_lembur' => $notifikasi_lembur,
                     'notifikasi_izin_dinas' => $notifikasi_izin_dinas,
                     'notifikasi_ajuan_absen' => $notifikasi_ajuan_absen,
                     'notifikasi_ajuan_jadwal' => $notifikasi_ajuan_jadwal,
                     'notifikasi_koreksi' => $notifikasi_koreksi,
                     'notifikasi_reimbursement' => $notifikasi_reimbursement,
                     'data_izin' => $data_izin,
                     'data_reimbursement_pending' => $data_reimbursement_pending,
                ];
                View::share($shareddata);
            }
        });
    }
}
