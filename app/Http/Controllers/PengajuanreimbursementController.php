<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Reimbursement;
use App\Models\ReimbursementDetail;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class PengajuanreimbursementController extends Controller
{
    public function index(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        
        $query = Reimbursement::query();
        $query->where('reimbursement.nik', $userkaryawan->nik);
        
        $dari = $request->dari ?: date('Y-m-01');
        $sampai = $request->sampai ?: date('Y-m-t');
        
        $query->whereBetween('tanggal_pengajuan', [$dari, $sampai]);
        
        $query->orderBy('tanggal_pengajuan', 'desc');
        $reimbursement = $query->paginate(10);
        $reimbursement->appends($request->all());
        
        return view('pengajuanreimbursement.index', compact('reimbursement'));
    }

    public function create()
    {
        $user = User::find(auth()->user()->id);
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        
        $jenis_reimburse = DB::table('jenis_reimbursement')
            ->join('reimbursement_karyawan', 'jenis_reimbursement.kode_jenis_reimburse', '=', 'reimbursement_karyawan.kode_jenis_reimburse')
            ->where('reimbursement_karyawan.nik', $userkaryawan->nik)
            ->where('reimbursement_karyawan.status', 1)
            ->where('jenis_reimbursement.status', 1)
            ->select('jenis_reimbursement.kode_jenis_reimburse', 'jenis_reimbursement.nama_jenis', 'jenis_reimbursement.wajib_bukti', 
                     DB::raw('COALESCE(reimbursement_karyawan.batas_nominal_override, jenis_reimbursement.batas_nominal) as limit_nominal'))
            ->get();
            
        if ($jenis_reimburse->isEmpty()) {
            return Redirect::route('pengajuanreimbursement.index')->with(['warning' => 'Anda belum diberikan akses layanan klaim/reimbursement. Silakan hubungi HRD.']);
        }
        
        return view('pengajuanreimbursement.create', compact('karyawan', 'jenis_reimburse'));
    }

    public function store(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $nik = $userkaryawan->nik;

        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Generate No Reimbursement (same pattern as ReimbursementController)
            $tgl = date('ym', strtotime($request->tanggal));
            $last = Reimbursement::whereRaw('MONTH(tanggal_pengajuan) = ?', [date('m', strtotime($request->tanggal))])
                ->whereRaw('YEAR(tanggal_pengajuan) = ?', [date('Y', strtotime($request->tanggal))])
                ->orderBy('no_reimbursement', 'desc')
                ->first();
            $last_no = $last ? $last->no_reimbursement : '';
            $no_reimbursement = buatkode($last_no, "RM/" . $tgl . "/", 4);

            $grand_total = 0;
            foreach ($request->items as $item) {
                $grand_total += str_replace(['.', ','], ['', '.'], $item['item_jumlah']);
            }

            $reimbursement = Reimbursement::create([
                'no_reimbursement' => $no_reimbursement,
                'tanggal_pengajuan' => $request->tanggal,
                'nik' => $nik,
                'total_nominal' => $grand_total,
                'catatan' => $request->keterangan,
                'status' => 'P',
                'approval_step' => 1,
            ]);

            foreach ($request->items as $index => $item) {
                // Verify authorization
                $auth_check = DB::table('reimbursement_karyawan')
                    ->where('nik', $nik)
                    ->where('kode_jenis_reimburse', $item['item_kategori'])
                    ->where('status', 1)
                    ->first();
                if (!$auth_check) {
                    throw new \Exception("Akses reimbursement ditolak untuk jenis klaim ini.");
                }

                $nominal = str_replace(['.', ','], ['', '.'], $item['item_jumlah']);
                
                $filename = null;
                if ($request->hasFile("items.$index.item_foto")) {
                    $file = $request->file("items.$index.item_foto");
                    $filename = str_replace('/', '-', $no_reimbursement) . "_" . $index . "_" . time() . "." . $file->getClientOriginalExtension();
                    $file->storeAs('public/uploads/reimbursement', $filename);
                }

                ReimbursementDetail::create([
                    'reimbursement_id' => $reimbursement->id,
                    'tanggal_transaksi' => $request->tanggal,
                    'kode_jenis_reimburse' => $item['item_kategori'],
                    'nominal' => $nominal,
                    'keterangan' => $item['item_keterangan'] ?? '-',
                    'bukti_file' => $filename,
                ]);
            }

            DB::commit();
            return Redirect::route('pengajuanreimbursement.index')->with(['success' => 'Pengajuan Berhasil Dikirim']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $reimbursement = Reimbursement::with('details')
            ->join('karyawan', 'reimbursement.nik', '=', 'karyawan.nik')
            ->select('reimbursement.*', 'karyawan.nama_karyawan')
            ->where('reimbursement.id', $id)
            ->first();

        // Get Approval History
        $approvals = DB::table('approvals')
            ->join('users', 'approvals.user_id', '=', 'users.id')
            ->where('approvable_type', Reimbursement::class)
            ->where('approvable_id', $reimbursement->id)
            ->select('approvals.*', 'users.name as user_name')
            ->orderBy('approvals.level', 'asc')
            ->get();

        return view('pengajuanreimbursement.show', compact('reimbursement', 'approvals'));
    }

    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $reimbursement = Reimbursement::with('details')->where('id', $id)->first();
        
        if ($reimbursement->status != 'P') {
            return Redirect::back()->with(['warning' => 'Pengajuan yang sudah diproses tidak dapat diubah']);
        }

        $user = User::find(auth()->user()->id);
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        
        $jenis_reimburse = DB::table('jenis_reimbursement')
            ->join('reimbursement_karyawan', 'jenis_reimbursement.kode_jenis_reimburse', '=', 'reimbursement_karyawan.kode_jenis_reimburse')
            ->where('reimbursement_karyawan.nik', $userkaryawan->nik)
            ->where('reimbursement_karyawan.status', 1)
            ->where('jenis_reimbursement.status', 1)
            ->select('jenis_reimbursement.kode_jenis_reimburse', 'jenis_reimbursement.nama_jenis', 'jenis_reimbursement.wajib_bukti', 
                     DB::raw('COALESCE(reimbursement_karyawan.batas_nominal_override, jenis_reimbursement.batas_nominal) as limit_nominal'))
            ->get();
            
        if ($jenis_reimburse->isEmpty()) {
            return Redirect::route('pengajuanreimbursement.index')->with(['warning' => 'Anda tidak memiliki akses reimbursement.']);
        }
        
        return view('pengajuanreimbursement.edit', compact('reimbursement', 'karyawan', 'jenis_reimburse'));
    }

    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $reimbursement = Reimbursement::where('id', $id)->first();

        if ($reimbursement->status != 'P') {
            return Redirect::back()->with(['warning' => 'Pengajuan yang sudah diproses tidak dapat diubah']);
        }

        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Delete old details
            $old_details = ReimbursementDetail::where('reimbursement_id', $reimbursement->id)->get();
            foreach ($old_details as $detail) {
                if ($detail->bukti_file) {
                    Storage::delete('public/uploads/reimbursement/' . $detail->bukti_file);
                }
            }
            ReimbursementDetail::where('reimbursement_id', $reimbursement->id)->delete();

            $grand_total = 0;
            foreach ($request->items as $index => $item) {
                // Verify authorization
                $auth_check = DB::table('reimbursement_karyawan')
                    ->where('nik', $reimbursement->nik)
                    ->where('kode_jenis_reimburse', $item['item_kategori'])
                    ->where('status', 1)
                    ->first();
                if (!$auth_check) {
                    throw new \Exception("Akses reimbursement ditolak untuk jenis klaim ini.");
                }

                $nominal = str_replace(['.', ','], ['', '.'], $item['item_jumlah']);
                $grand_total += $nominal;
                
                $filename = null;

                // Keep old file if exists
                $old_id = $item['old_id'] ?? null;
                if ($old_id) {
                    $old_detail = $old_details->where('id', $old_id)->first();
                    if ($old_detail && $old_detail->bukti_file && !$request->hasFile("items.$index.item_foto")) {
                        $filename = $old_detail->bukti_file;
                    }
                }

                if ($request->hasFile("items.$index.item_foto")) {
                    $file = $request->file("items.$index.item_foto");
                    $filename = str_replace('/', '-', $reimbursement->no_reimbursement) . "_" . $index . "_" . time() . "." . $file->getClientOriginalExtension();
                    $file->storeAs('public/uploads/reimbursement', $filename);
                }

                ReimbursementDetail::create([
                    'reimbursement_id' => $reimbursement->id,
                    'tanggal_transaksi' => $request->tanggal,
                    'kode_jenis_reimburse' => $item['item_kategori'],
                    'nominal' => $nominal,
                    'keterangan' => $item['item_keterangan'] ?? '-',
                    'bukti_file' => $filename,
                ]);
            }

            $reimbursement->update([
                'tanggal_pengajuan' => $request->tanggal,
                'total_nominal' => $grand_total,
                'catatan' => $request->keterangan,
            ]);

            DB::commit();
            return Redirect::route('pengajuanreimbursement.index')->with(['success' => 'Pengajuan Berhasil Diupdate']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $reimbursement = Reimbursement::where('id', $id)->first();

        if ($reimbursement->status != 'P') {
            return Redirect::back()->with(['warning' => 'Pengajuan yang sudah diproses tidak dapat dihapus']);
        }

        DB::beginTransaction();
        try {
            $details = ReimbursementDetail::where('reimbursement_id', $reimbursement->id)->get();
            foreach ($details as $detail) {
                if ($detail->bukti_file) {
                    Storage::delete('public/uploads/reimbursement/' . $detail->bukti_file);
                }
            }
            ReimbursementDetail::where('reimbursement_id', $reimbursement->id)->delete();
            $reimbursement->delete();

            DB::commit();
            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => $e->getMessage()]);
        }
    }
}
