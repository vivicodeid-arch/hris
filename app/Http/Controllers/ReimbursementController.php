<?php

namespace App\Http\Controllers;

use App\Models\Reimbursement;
use App\Models\ReimbursementDetail;
use App\Models\JenisReimbursement;
use App\Models\Karyawan;
use App\Models\Userkaryawan;
use App\Models\Approval;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class ReimbursementController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $role = $user->getRoleNames()->first();

        $query = Reimbursement::query();
        $query->join('karyawan', 'reimbursement.nik', '=', 'karyawan.nik');
        $query->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $query->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang');
        $query->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');

        if ($role == 'karyawan') {
            $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
            $query->where('reimbursement.nik', $userkaryawan->nik);
        } else {
            // Admin/HRD Filter
            if (!$user->isSuperAdmin()) {
                $userCabangs = $user->getCabangCodes();
                $userDepartemens = $user->getDepartemenCodes();
                if (!empty($userCabangs)) $query->whereIn('karyawan.kode_cabang', $userCabangs);
                if (!empty($userDepartemens)) $query->whereIn('karyawan.kode_dept', $userDepartemens);
            }
        }

        if (!empty($request->nama_karyawan)) {
            $query->where('karyawan.nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
        }

        if (!empty($request->kode_cabang)) {
            $query->where('karyawan.kode_cabang', $request->kode_cabang);
        }

        if (!empty($request->kode_dept)) {
            $query->where('karyawan.kode_dept', $request->kode_dept);
        }

        if ($request->dari && $request->sampai) {
            $query->whereBetween('tanggal_pengajuan', [$request->dari, $request->sampai]);
        }
        if ($request->nik) {
            $query->where('reimbursement.nik', $request->nik);
        }
        if ($request->status) {
            $query->where('reimbursement.status', $request->status);
        }

        $data['reimbursement'] = $query->select('reimbursement.*', 'karyawan.nama_karyawan', 'karyawan.nik_show', 'departemen.nama_dept', 'cabang.nama_cabang', 'jabatan.nama_jabatan')
            ->orderBy('tanggal_pengajuan', 'desc')
            ->orderBy('reimbursement.created_at', 'desc')
            ->paginate(15);
        $data['reimbursement']->appends($request->all());

        if ($role != 'karyawan') {
            $data['cabang'] = $user->getCabang();
            $data['departemen'] = $user->getDepartemen();
        }

        return view('reimbursement.index', $data);
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $role = $user->getRoleNames()->first();

        if ($role == 'karyawan') {
            $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
            $data['nik'] = $userkaryawan->nik;
            $data['karyawan_selected'] = $userkaryawan->karyawan;
            
            // Get enrolled reimbursement types with their respective limits
            $data['jenis_reimburse'] = DB::table('jenis_reimbursement')
                ->join('reimbursement_karyawan', 'jenis_reimbursement.kode_jenis_reimburse', '=', 'reimbursement_karyawan.kode_jenis_reimburse')
                ->where('reimbursement_karyawan.nik', $userkaryawan->nik)
                ->where('jenis_reimbursement.status', 1)
                ->where('reimbursement_karyawan.status', 1)
                ->where(function($q) {
                    $q->whereNull('reimbursement_karyawan.tanggal_selesai')
                      ->orWhere('reimbursement_karyawan.tanggal_selesai', '>=', date('Y-m-d'));
                })
                ->select(
                    'jenis_reimbursement.kode_jenis_reimburse', 
                    'jenis_reimbursement.nama_jenis',
                    'jenis_reimbursement.wajib_bukti',
                    DB::raw('COALESCE(reimbursement_karyawan.batas_nominal_override, jenis_reimbursement.batas_nominal) as limit_nominal')
                )
                ->get();
        } else {
            $data['nik'] = null;
            $queryKaryawan = Karyawan::where('status_aktif_karyawan', 1);
            if (!$user->isSuperAdmin()) {
                $queryKaryawan->whereIn('kode_cabang', $user->getCabangCodes())
                              ->whereIn('kode_dept', $user->getDepartemenCodes());
            }
            $data['karyawan'] = $queryKaryawan->orderBy('nama_karyawan')->get();
            $data['jenis_reimburse'] = JenisReimbursement::where('status', 1)
                ->select('*', 'batas_nominal as limit_nominal', 'wajib_bukti')
                ->get();
        }

        return view('reimbursement.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_pengajuan' => 'required|date',
            'kode_jenis_reimburse.*' => 'required',
            'nominal.*' => 'required',
            'file.*' => 'nullable|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = auth()->user();
        $role = $user->getRoleNames()->first();
        
        if ($role == 'karyawan') {
            $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
            $nik = $userkaryawan->nik;
        } else {
            $nik = $request->nik;
            if (!$user->isSuperAdmin()) {
                $targetKaryawan = Karyawan::where('nik', $nik)->firstOrFail();
                if (!in_array($targetKaryawan->kode_cabang, $user->getCabangCodes()) || !in_array($targetKaryawan->kode_dept, $user->getDepartemenCodes())) {
                    abort(403, 'Akses ditolak.');
                }
            }
        }

        DB::beginTransaction();
        try {
            // Generate No Pengajuan
            $last = Reimbursement::whereRaw('MONTH(tanggal_pengajuan) = ?', [date('m', strtotime($request->tanggal_pengajuan))])
                ->whereRaw('YEAR(tanggal_pengajuan) = ?', [date('Y', strtotime($request->tanggal_pengajuan))])
                ->orderBy('no_reimbursement', 'desc')
                ->first();
            $last_no = $last ? $last->no_reimbursement : '';
            $no_reimbursement = buatkode($last_no, "RM/"  . date('ym', strtotime($request->tanggal_pengajuan)) . "/", 4);

            $total = 0;
            foreach ($request->nominal as $n) {
                $total += str_replace(['.', ','], ['', '.'], $n);
            }

            $reimbursement = Reimbursement::create([
                'no_reimbursement' => $no_reimbursement,
                'tanggal_pengajuan' => $request->tanggal_pengajuan,
                'nik' => $nik,
                'total_nominal' => $total,
                'catatan' => $request->catatan_global,
                'status' => 'P', // Pending
                'approval_step' => 1
            ]);

            foreach ($request->kode_jenis_reimburse as $key => $kode_jenis) {
                $nominal = str_replace(['.', ','], ['', '.'], $request->nominal[$key]);
                
                // VALIDASI PLAFON
                $enrollment = DB::table('reimbursement_karyawan')
                    ->join('jenis_reimbursement', 'reimbursement_karyawan.kode_jenis_reimburse', '=', 'jenis_reimbursement.kode_jenis_reimburse')
                    ->where('reimbursement_karyawan.nik', $nik)
                    ->where('reimbursement_karyawan.kode_jenis_reimburse', $kode_jenis)
                    ->select('reimbursement_karyawan.batas_nominal_override', 'jenis_reimbursement.batas_nominal', 'jenis_reimbursement.nama_jenis')
                    ->first();

                if ($enrollment) {
                    $limit = $enrollment->batas_nominal_override ?? $enrollment->batas_nominal;
                    if ($limit > 0 && $nominal > $limit) {
                        throw new \Exception("Nominal klaim untuk " . $enrollment->nama_jenis . " (Row " . ($key+1) . ") melebihi plafon maksimal Rp " . number_format($limit, 0, ',', '.'));
                    }
                } else {
                    // Jika tidak terdaftar (untuk jaga-jaga jika bypass lewat form html)
                    throw new \Exception("Karyawan tidak terdaftar untuk jenis reimbursement (Row " . ($key+1) . ")");
                }

                $filename = null;
                if ($request->hasFile('file.' . $key)) {
                    $file = $request->file('file.' . $key);
                    $filename = str_replace('/', '-', $no_reimbursement) . "_" . $key . "_" . time() . "." . $file->getClientOriginalExtension();
                    $file->storeAs('public/uploads/reimbursement', $filename);
                }

                ReimbursementDetail::create([
                    'reimbursement_id' => $reimbursement->id,
                    'tanggal_transaksi' => $request->tgl_item[$key] ?? $request->tanggal_pengajuan,
                    'kode_jenis_reimburse' => $kode_jenis,
                    'nominal' => $nominal,
                    'keterangan' => $request->keterangan[$key] ?? '-',
                    'bukti_file' => $filename
                ]);
            }

            DB::commit();
            return redirect()->route('reimbursement.index')->with(messageSuccess('Pengajuan Reimbursement Berhasil Disimpan'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(messageError($e->getMessage()))->withInput();
        }
    }

    public function show($id, ApprovalService $approvalService)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $id = Crypt::decrypt($id);
        $reimbursement = Reimbursement::where('reimbursement.id', $id)
            ->join('karyawan', 'reimbursement.nik', '=', 'karyawan.nik')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->select('reimbursement.*', 'karyawan.nama_karyawan', 'karyawan.nik_show', 'karyawan.kode_cabang', 'karyawan.kode_dept', 'karyawan.kode_jabatan', 'departemen.nama_dept', 'cabang.nama_cabang')
            ->firstOrFail();

        $role = $user->getRoleNames()->first();
        if ($role == 'karyawan') {
            $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
            if ($reimbursement->nik != $userkaryawan->nik) {
                abort(403, 'Akses ditolak.');
            }
        } else {
            if (!$user->isSuperAdmin()) {
                if (!in_array($reimbursement->kode_cabang, $user->getCabangCodes()) || !in_array($reimbursement->kode_dept, $user->getDepartemenCodes())) {
                    abort(403, 'Akses ditolak.');
                }
            }
        }
        
        $data['reimbursement'] = $reimbursement;

        $data['details'] = ReimbursementDetail::where('reimbursement_id', $id)
            ->join('jenis_reimbursement', 'reimbursement_detail.kode_jenis_reimburse', '=', 'jenis_reimbursement.kode_jenis_reimburse')
            ->select('reimbursement_detail.*', 'jenis_reimbursement.nama_jenis')
            ->get();

        $data['approvals'] = Approval::where('approvable_type', Reimbursement::class)
            ->where('approvable_id', $id)
            ->join('users', 'approvals.user_id', '=', 'users.id')
            ->select('approvals.*', 'users.name as user_name')
            ->orderBy('approvals.level', 'asc')
            ->get();
            
        // Approval Logic
        $userRole = $user->getRoleNames()->first();
        $can_approve = false;
        if ($reimbursement->status == 'P') {
            if ($user->isSuperAdmin()) {
                $can_approve = true;
            } else {
                $can_approve = $approvalService->canApprove(
                    'REIMBURSEMENT', 
                    $reimbursement->approval_step, 
                    $userRole, 
                    $reimbursement->kode_dept, 
                    $reimbursement->kode_jabatan, 
                    $user, 
                    $reimbursement->kode_cabang
                );
            }
        }

        $data['can_approve'] = $can_approve;
        $data['is_super_admin'] = $user->isSuperAdmin();

        return view('reimbursement.show', $data);
    }

    public function print($id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $id = Crypt::decrypt($id);
        $reimbursement = Reimbursement::where('reimbursement.id', $id)
            ->join('karyawan', 'reimbursement.nik', '=', 'karyawan.nik')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->select('reimbursement.*', 'karyawan.nama_karyawan', 'karyawan.nik_show', 'karyawan.kode_cabang', 'karyawan.kode_dept', 'karyawan.kode_jabatan', 'departemen.nama_dept', 'cabang.nama_cabang', 'jabatan.nama_jabatan')
            ->firstOrFail();

        $role = $user->getRoleNames()->first();
        if ($role == 'karyawan') {
            $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
            if ($reimbursement->nik != $userkaryawan->nik) {
                abort(403, 'Akses ditolak.');
            }
        } else {
            if (!$user->isSuperAdmin()) {
                if (!in_array($reimbursement->kode_cabang, $user->getCabangCodes()) || !in_array($reimbursement->kode_dept, $user->getDepartemenCodes())) {
                    abort(403, 'Akses ditolak.');
                }
            }
        }
        
        $data['reimbursement'] = $reimbursement;

        $data['details'] = ReimbursementDetail::where('reimbursement_id', $id)
            ->join('jenis_reimbursement', 'reimbursement_detail.kode_jenis_reimburse', '=', 'jenis_reimbursement.kode_jenis_reimburse')
            ->select('reimbursement_detail.*', 'jenis_reimbursement.nama_jenis')
            ->get();

        $data['approvals'] = Approval::where('approvable_type', Reimbursement::class)
            ->where('approvable_id', $id)
            ->join('users', 'approvals.user_id', '=', 'users.id')
            ->select('approvals.*', 'users.name as user_name')
            ->orderBy('approvals.level', 'asc')
            ->get();

        $data['generalsetting'] = \App\Models\Pengaturanumum::where('id', 1)->first();

        return view('reimbursement.print', $data);
    }


    public function storeapprove(Request $request, $no_reimbursement)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $no_reimbursement = Crypt::decrypt($no_reimbursement);
        
        $reimbursement = Reimbursement::where('no_reimbursement', $no_reimbursement)
            ->join('karyawan', 'reimbursement.nik', '=', 'karyawan.nik')
            ->select('reimbursement.*', 'karyawan.kode_dept', 'karyawan.kode_cabang', 'karyawan.kode_jabatan')
            ->first();

        if (!$reimbursement) {
            return redirect()->back()->with(messageError('Data pengajuan tidak ditemukan.'));
        }

        // Cek akses jika bukan super admin
        if (!$user->isSuperAdmin()) {
            $accessUser = $user->getApprovalAdmin() ?? $user;
            $userCabangs = $accessUser->getCabangCodes();
            $userDepartemens = $accessUser->getDepartemenCodes();
            
            if (!in_array($reimbursement->kode_cabang, $userCabangs) || !in_array($reimbursement->kode_dept, $userDepartemens)) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
            }
        }

        $approvalService = app(ApprovalService::class);
        $userRole = $user->getRoleNames()->first();
        $currentStep = $reimbursement->approval_step;
        $approvalUserId = $approvalService->getApprovalUserId($user);
        $approvalAdmin = $approvalUserId != $user->id ? User::find($approvalUserId) : $user;

        // Check Authorization
        if (!$approvalService->canApprove('REIMBURSEMENT', $currentStep, $userRole, $reimbursement->kode_dept, $reimbursement->kode_jabatan, $user, $reimbursement->kode_cabang)) {
             if (!$user->isSuperAdmin()) {
                 return redirect()->back()->with(messageError('Anda tidak memiliki wewenang untuk approval tahap ke-' . $currentStep));
             }
        }

        DB::beginTransaction();
        try {
            if (isset($request->approve)) {
                // 1. Record Approval
                Approval::create([
                    'approvable_type' => Reimbursement::class,
                    'approvable_id' => $reimbursement->id,
                    'user_id' => $approvalUserId,
                    'level' => $currentStep,
                    'status' => 'approved',
                    'keterangan' => $request->keterangan ?? ('Approved by ' . $approvalAdmin->name),
                ]);

                // 2. Check for Next Level rule
                $nextLevel = $currentStep + 1;
                $nextRule = $approvalService->getLayer('REIMBURSEMENT', $nextLevel, $reimbursement->kode_dept, $reimbursement->kode_jabatan, $reimbursement->kode_cabang);
                
                if ($nextRule && !$user->isSuperAdmin()) {
                    // Move to next step
                    $reimbursement->update(['approval_step' => $nextLevel]);
                    DB::commit();
                    return redirect()->back()->with(messageSuccess('Berhasil disetujui (Tahap ' . $currentStep . '). Menunggu tahap selanjutnya.'));
                }

                // FINAL APPROVAL
                $reimbursement->update(['status' => 'A']); // A = Approved
            } else {
                // REJECTION
                Approval::create([
                    'approvable_type' => Reimbursement::class,
                    'approvable_id' => $reimbursement->id,
                    'user_id' => $approvalUserId,
                    'level' => $currentStep,
                    'status' => 'rejected',
                    'keterangan' => $request->keterangan ?? ('Rejected by ' . $approvalAdmin->name),
                ]);

                $reimbursement->update(['status' => 'R']); // R = Rejected
            }

            DB::commit();
            return redirect()->back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(messageError($e->getMessage()));
        }
    }

    public function cancelapprove($no_reimbursement)
    {
        $no_reimbursement = Crypt::decrypt($no_reimbursement);
        $reimbursement = Reimbursement::where('no_reimbursement', $no_reimbursement)
            ->join('karyawan', 'reimbursement.nik', '=', 'karyawan.nik')
            ->select('reimbursement.*', 'karyawan.kode_dept', 'karyawan.kode_cabang')
            ->first();

        if (!$reimbursement) {
            return redirect()->back()->with(messageError('Data tidak ditemukan.'));
        }

        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            if (!in_array($reimbursement->kode_cabang, $user->getCabangCodes()) || !in_array($reimbursement->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Akses ditolak.');
            }
        }

        DB::beginTransaction();
        try {
            // Find the last approval record for this reimbursement
            $lastApproval = Approval::where('approvable_type', Reimbursement::class)
                ->where('approvable_id', $reimbursement->id)
                ->orderBy('level', 'desc')
                ->first();

            if ($lastApproval) {
                // Revert step to this level
                $revertStep = $lastApproval->level;
                $lastApproval->delete();

                $reimbursement->update([
                    'status' => 'P',
                    'approval_step' => $revertStep
                ]);

                DB::commit();
                return redirect()->back()->with(messageSuccess('Approval berhasil dibatalkan. Kembali ke tahap ' . $revertStep));
            }

            return redirect()->back()->with(messageError('Tidak ada riwayat approval untuk dibatalkan.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(messageError($e->getMessage()));
        }
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $reimbursement = Reimbursement::findOrFail($id);
        
        $user = auth()->user();
        $role = $user->getRoleNames()->first();
        if ($role == 'karyawan') {
            $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
            if ($reimbursement->nik != $userkaryawan->nik) {
                abort(403, 'Akses ditolak.');
            }
        } else {
            if (!$user->isSuperAdmin()) {
                $karyawan = Karyawan::where('nik', $reimbursement->nik)->first();
                if (!$karyawan || !in_array($karyawan->kode_cabang, $user->getCabangCodes()) || !in_array($karyawan->kode_dept, $user->getDepartemenCodes())) {
                    abort(403, 'Akses ditolak.');
                }
            }
        }

        if ($reimbursement->status != 'P') {
            return redirect()->back()->with(messageError('Hanya pengajuan berstatus Pending yang dapat dihapus.'));
        }

        DB::beginTransaction();
        try {
            $details = ReimbursementDetail::where('reimbursement_id', $id)->get();
            foreach ($details as $detail) {
                if ($detail->bukti_file) {
                    \Illuminate\Support\Facades\Storage::delete('public/uploads/reimbursement/' . $detail->bukti_file);
                }
            }
            $reimbursement->delete();
            DB::commit();
            return redirect()->back()->with(messageSuccess('Pengajuan Berhasil Dihapus'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(messageError($e->getMessage()));
        }
    }
}
