<?php

namespace App\Http\Controllers;

use App\Models\Approveizinsakit;
use App\Models\Approvekoreksi;
use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Detailsetjamkerjabydept;
use App\Models\Izinabsen;
use App\Models\Izincuti;
use App\Models\Izinsakit;
use App\Models\Karyawan;
use App\Models\Koreksi;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\User;
use App\Models\Userkaryawan;
use App\Services\ApprovalService;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class KoreksiController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $qkoreksi = Koreksi::query();
        $qkoreksi->join('karyawan', 'presensi_koreksi.nik', '=', 'karyawan.nik');
        $qkoreksi->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $qkoreksi->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $qkoreksi->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang');
        $qkoreksi->leftJoin('presensi_jamkerja', 'presensi_koreksi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja');

        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($userCabangs)) {
                $qkoreksi->whereIn('karyawan.kode_cabang', $userCabangs);
            } else {
                $qkoreksi->whereRaw('1 = 0');
            }
            
            if (!empty($userDepartemens)) {
                $qkoreksi->whereIn('karyawan.kode_dept', $userDepartemens);
            } else {
                $qkoreksi->whereRaw('1 = 0');
            }
        }

        $qkoreksi->select('presensi_koreksi.*', 'karyawan.nama_karyawan', 'karyawan.nik_show', 'karyawan.foto', 'jabatan.nama_jabatan', 'departemen.nama_dept', 'cabang.nama_cabang', 'karyawan.kode_dept', 'presensi_jamkerja.nama_jam_kerja');
        
        if (!empty($request->dari) && !empty($request->sampai)) {
            $qkoreksi->whereBetween('presensi_koreksi.tanggal', [$request->dari, $request->sampai]);
        }
        if (!empty($request->nama_karyawan)) {
            $qkoreksi->where('karyawan.nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
        }
        if (!empty($request->status) || $request->status === '0') {
            $qkoreksi->where('presensi_koreksi.status', $request->status);
        }

        $qkoreksi->orderBy('presensi_koreksi.status');
        $qkoreksi->orderBy('presensi_koreksi.tanggal', 'desc');
        $koreksi = $qkoreksi->paginate(15);
        $koreksi->appends($request->all());

        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        $data['koreksi'] = $koreksi;
        return view('koreksi.index', $data);
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $qkaryawan = Karyawan::query();
        $qkaryawan->select('karyawan.nik', 'karyawan.nama_karyawan');
        
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($userCabangs)) {
                $qkaryawan->whereIn('kode_cabang', $userCabangs);
            } else {
                $qkaryawan->whereRaw('1 = 0');
            }
            
            if (!empty($userDepartemens)) {
                $qkaryawan->whereIn('kode_dept', $userDepartemens);
            } else {
                $qkaryawan->whereRaw('1 = 0');
            }
        }
        
        $karyawan = $qkaryawan->get();
        $jamkerja = \App\Models\Jamkerja::all();
        $data['karyawan'] = $karyawan;
        $data['jamkerja'] = $jamkerja;

        if ($user->hasRole('karyawan')) {
            return view('koreksi.create-mobile', $data);
        }

        return view('koreksi.create', $data);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $role = $user->getRoleNames()->first();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $nik = $user->hasRole('karyawan') ? $userkaryawan->nik : $request->nik;

        $request->validate([
            'tanggal' => 'required|date',
            'kode_jam_kerja' => 'required',
            'jam_in' => 'nullable',
            'jam_out' => 'nullable',
            'keterangan' => 'required',
        ]);

        if (!$request->jam_in && !$request->jam_out) {
            return Redirect::back()->with(messageError('Jam Masuk atau Jam Pulang harus diisi salah satu!'));
        }

        DB::beginTransaction();
        try {
            // Cek apakah sudah ada pengajuan koreksi untuk tanggal tersebut yang masih pending
            $cek_koreksi = Koreksi::where('nik', $nik)
                ->where('tanggal', $request->tanggal)
                ->where('status', '0')
                ->first();

            if ($cek_koreksi) {
                return Redirect::back()->with(messageError('Anda sudah memiliki pengajuan koreksi yang sedang diproses untuk tanggal tersebut!'));
            }

            $lastkoreksi = Koreksi::select('kode_koreksi')
                ->whereRaw('YEAR(tanggal)="' . date('Y', strtotime($request->tanggal)) . '"')
                ->whereRaw('MONTH(tanggal)="' . date('m', strtotime($request->tanggal)) . '"')
                ->orderBy("kode_koreksi", "desc")
                ->first();
            
            $last_kode_koreksi = $lastkoreksi != null ? $lastkoreksi->kode_koreksi : '';
            $kode_koreksi = buatkode($last_kode_koreksi, "KP" . date('ym', strtotime($request->tanggal)), 4);

            Koreksi::create([
                'kode_koreksi' => $kode_koreksi,
                'nik' => $nik,
                'tanggal' => $request->tanggal,
                'kode_jam_kerja' => $request->kode_jam_kerja,
                'jam_in' => $request->jam_in,
                'jam_out' => $request->jam_out,
                'keterangan' => $request->keterangan,
                'status' => '0',
                'approval_step' => 1,
                'id_user' => $user->id,
            ]);

            DB::commit();
            return Redirect::back()->with(messageSuccess('Pengajuan koreksi berhasil disimpan.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function show($kode_koreksi)
    {
        $kode_koreksi = Crypt::decrypt($kode_koreksi);
        $koreksi = Koreksi::where('kode_koreksi', $kode_koreksi)
            ->join('karyawan', 'presensi_koreksi.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('presensi_jamkerja', 'presensi_koreksi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select('presensi_koreksi.*', 'karyawan.nama_karyawan', 'jabatan.nama_jabatan', 'departemen.nama_dept', 'cabang.nama_cabang', 'presensi_jamkerja.nama_jam_kerja')
            ->first();

        $data['koreksi'] = $koreksi;
        return view('koreksi.show', $data);
    }

    public function approve($kode_koreksi)
    {
        $kode_koreksi = Crypt::decrypt($kode_koreksi);
        $koreksi = Koreksi::where('kode_koreksi', $kode_koreksi)
            ->join('karyawan', 'presensi_koreksi.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('presensi_jamkerja', 'presensi_koreksi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select('presensi_koreksi.*', 'karyawan.nama_karyawan', 'jabatan.nama_jabatan', 'departemen.nama_dept', 'cabang.nama_cabang', 'presensi_jamkerja.nama_jam_kerja')
            ->first();

        $data['koreksi'] = $koreksi;
        return view('koreksi.approve', $data);
    }

    public function storeapprove(Request $request, $kode_koreksi)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $kode_koreksi = Crypt::decrypt($kode_koreksi);
        $koreksi = Koreksi::where('kode_koreksi', $kode_koreksi)
            ->join('karyawan', 'presensi_koreksi.nik', '=', 'karyawan.nik')
            ->select('presensi_koreksi.*', 'karyawan.kode_dept', 'karyawan.kode_cabang', 'karyawan.kode_jabatan')
            ->first();
        
        if (!$user->isSuperAdmin()) {
            $accessUser = $user->getApprovalAdmin() ?? $user;
            $userCabangs = $accessUser->getCabangCodes();
            $userDepartemens = $accessUser->getDepartemenCodes();
            
            if (!in_array($koreksi->kode_cabang, $userCabangs) || !in_array($koreksi->kode_dept, $userDepartemens)) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
            }
        }

        $approvalService = app(ApprovalService::class);
        $userRole = $user->getRoleNames()->first();
        $currentStep = $koreksi->approval_step;
        $approvalUserId = $approvalService->getApprovalUserId($user);
        $approvalAdmin = $approvalUserId != $user->id ? User::find($approvalUserId) : $user;

        if (!$approvalService->canApprove('KOREKSI', $currentStep, $userRole, $koreksi->kode_dept, $koreksi->kode_jabatan, $user, $koreksi->kode_cabang)) {
             if (!$user->isSuperAdmin()) {
                 return Redirect::back()->with(messageError('Anda tidak memiliki wewenang untuk approval tahap ke-' . $currentStep));
             }
        }
        
        DB::beginTransaction();
        try {
            if (isset($request->approve)) {
                Approval::create([
                    'approvable_type' => Koreksi::class,
                    'approvable_id' => $kode_koreksi,
                    'user_id' => $approvalUserId,
                    'level' => $currentStep,
                    'status' => 'approved',
                    'keterangan' => 'Approved by ' . $approvalAdmin->name,
                ]);

                $nextLevel = $currentStep + 1;
                $nextRule = $approvalService->getLayer('KOREKSI', $nextLevel, $koreksi->kode_dept, $koreksi->kode_jabatan, $koreksi->kode_cabang);
                
                if ($nextRule && !$user->hasRole('super admin')) {
                    Koreksi::where('kode_koreksi', $kode_koreksi)->update(['approval_step' => $nextLevel]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Berhasil disetujui (Tahap ' . $currentStep . '). Menunggu approval tahap selanjutnya.'));
                }

                // FINAL APPROVAL
                Koreksi::where('kode_koreksi', $kode_koreksi)->update(['status' => '1']);

                // Update or Create Presensi Record
                $tanggal = $koreksi->tanggal;
                $namahari = getnamaHari(date('D', strtotime($tanggal)));

                if ($koreksi->kode_jam_kerja) {
                    $jamkerja = \App\Models\Jamkerja::where('kode_jam_kerja', $koreksi->kode_jam_kerja)->first();
                } else {
                    $jamkerja = Setjamkerjabydate::join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                        ->where('nik', $koreksi->nik)
                        ->where('tanggal', $tanggal)
                        ->first();
                    if ($jamkerja == null) {
                        $jamkerja = Setjamkerjabyday::join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                            ->where('nik', $koreksi->nik)->where('hari', $namahari)
                            ->first();
                    }
                    if ($jamkerja == null) {
                        $jamkerja = Detailsetjamkerjabydept::join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
                            ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                            ->where('kode_dept', $koreksi->kode_dept)
                            ->where('kode_cabang', $koreksi->kode_cabang)
                            ->where('hari', $namahari)->first();
                    }
                }

                if ($jamkerja == null) {
                    throw new \Exception('Jam Kerja Belum Di Set!');
                }

                $presensi = Presensi::where('nik', $koreksi->nik)->where('tanggal', $tanggal)->first();
                
                $dataPresensi = [
                    'nik' => $koreksi->nik,
                    'tanggal' => $tanggal,
                    'kode_jam_kerja' => $jamkerja->kode_jam_kerja,
                    'status' => 'h',
                ];

                if ($koreksi->jam_in) $dataPresensi['jam_in'] = $tanggal . ' ' . $koreksi->jam_in;
                if ($koreksi->jam_out) $dataPresensi['jam_out'] = $tanggal . ' ' . $koreksi->jam_out;

                if ($presensi) {
                    $presensi->update($dataPresensi);
                } else {
                    $presensi = Presensi::create($dataPresensi);
                }

                Approvekoreksi::updateOrCreate(
                    ['kode_koreksi' => $kode_koreksi],
                    ['id_presensi' => $presensi->id]
                );

            } else {
                 // REJECTION
                 Approval::create([
                    'approvable_type' => Koreksi::class,
                    'approvable_id' => $kode_koreksi,
                    'user_id' => $approvalUserId,
                    'level' => $currentStep,
                    'status' => 'rejected',
                    'keterangan' => 'Rejected by ' . $approvalAdmin->name,
                ]);

                Koreksi::where('kode_koreksi', $kode_koreksi)->update(['status' => '2']);
            }
            
            DB::commit();
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function destroy($kode_koreksi)
    {
        $kode_koreksi = Crypt::decrypt($kode_koreksi);
        try {
            Koreksi::where('kode_koreksi', $kode_koreksi)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }
}
