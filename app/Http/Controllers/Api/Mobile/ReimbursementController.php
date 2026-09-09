<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Reimbursement;
use App\Models\ReimbursementDetail;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReimbursementController extends Controller
{
    /**
     * Get list of reimbursement claims for the authenticated employee.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userKaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $query = Reimbursement::where('nik', $userKaryawan->nik);

        // Optional date filters
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_pengajuan', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_pengajuan', '<=', $request->sampai);
        }

        $claims = $query->orderBy('tanggal_pengajuan', 'desc')->get();

        $data = $claims->map(function ($c) {
            return [
                'id' => $c->id,
                'no_reimbursement' => $c->no_reimbursement,
                'tanggal_pengajuan' => $c->tanggal_pengajuan,
                'total_nominal' => (double) $c->total_nominal,
                'catatan' => $c->catatan,
                'status' => $c->status,
                'approval_step' => (int) $c->approval_step,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get allowed categories/types of reimbursement.
     */
    public function getCategories(Request $request)
    {
        $user = $request->user();
        $userKaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $categories = DB::table('jenis_reimbursement')
            ->join('reimbursement_karyawan', 'jenis_reimbursement.kode_jenis_reimburse', '=', 'reimbursement_karyawan.kode_jenis_reimburse')
            ->where('reimbursement_karyawan.nik', $userKaryawan->nik)
            ->where('reimbursement_karyawan.status', 1)
            ->where('jenis_reimbursement.status', 1)
            ->select(
                'jenis_reimbursement.kode_jenis_reimburse',
                'jenis_reimbursement.nama_jenis',
                'jenis_reimbursement.wajib_bukti',
                DB::raw('COALESCE(reimbursement_karyawan.batas_nominal_override, jenis_reimbursement.batas_nominal) as limit_nominal')
            )
            ->get();

        $data = $categories->map(function ($c) {
            return [
                'kode_jenis_reimburse' => $c->kode_jenis_reimburse,
                'nama_jenis' => $c->nama_jenis,
                'wajib_bukti' => (int) $c->wajib_bukti,
                'limit_nominal' => (double) $c->limit_nominal,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Submit a new reimbursement request.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $userKaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'keterangan' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_kategori' => 'required|string',
            'items.*.item_jumlah' => 'required|numeric|min:1',
            'items.*.item_keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate No Reimbursement (same pattern as ReimbursementController)
            $tgl = date('ym', strtotime($request->tanggal));
            $last = Reimbursement::whereRaw('MONTH(tanggal_pengajuan) = ?', [date('m', strtotime($request->tanggal))])
                ->whereRaw('YEAR(tanggal_pengajuan) = ?', [date('Y', strtotime($request->tanggal))])
                ->orderBy('no_reimbursement', 'desc')
                ->first();
            $last_no = $last ? $last->no_reimbursement : '';
            
            // Helper function buatkode check
            if (function_exists('buatkode')) {
                $no_reimbursement = buatkode($last_no, "RM/" . $tgl . "/", 4);
            } else {
                // Fallback number generator if helper doesn't load in API namespace
                $seq = 1;
                if ($last) {
                    $lastSeq = (int) substr($last->no_reimbursement, -4);
                    $seq = $lastSeq + 1;
                }
                $no_reimbursement = "RM/" . $tgl . "/" . sprintf('%04d', $seq);
            }

            $grand_total = 0;
            foreach ($request->items as $item) {
                $grand_total += (double) $item['item_jumlah'];
            }

            $reimbursement = Reimbursement::create([
                'no_reimbursement' => $no_reimbursement,
                'tanggal_pengajuan' => $request->tanggal,
                'nik' => $userKaryawan->nik,
                'total_nominal' => $grand_total,
                'catatan' => $request->keterangan,
                'status' => 'P',
                'approval_step' => 1,
            ]);

            foreach ($request->items as $index => $item) {
                // Verify authorization
                $auth_check = DB::table('reimbursement_karyawan')
                    ->where('nik', $userKaryawan->nik)
                    ->where('kode_jenis_reimburse', $item['item_kategori'])
                    ->where('status', 1)
                    ->first();
                if (!$auth_check) {
                    throw new \Exception("Akses reimbursement ditolak untuk jenis klaim ini.");
                }

                $nominal = (double) $item['item_jumlah'];
                
                $filename = null;
                if ($request->hasFile("items.$index.item_foto")) {
                    $file = $request->file("items.$index.item_foto");
                    $filename = str_replace('/', '-', $no_reimbursement) . "_" . $index . "_" . time() . "." . $file->getClientOriginalExtension();
                    $file->storeAs('public/uploads/reimbursement', $filename);
                } else {
                    // Check if proof is required
                    $jenis = DB::table('jenis_reimbursement')->where('kode_jenis_reimburse', $item['item_kategori'])->first();
                    if ($jenis && $jenis->wajib_bukti == 1) {
                        throw new \Exception("Kategori " . $jenis->nama_jenis . " wajib menyertakan foto bukti kwitansi/nota.");
                    }
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
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan reimbursement berhasil dikirim',
                'data' => [
                    'id' => $reimbursement->id,
                    'no_reimbursement' => $reimbursement->no_reimbursement
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show detail of a reimbursement request.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $userKaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $reimbursement = Reimbursement::with(['details.jenis_reimbursement'])
            ->where('id', $id)
            ->first();

        if (!$reimbursement) {
            return response()->json([
                'success' => false,
                'message' => 'Data reimbursement tidak ditemukan'
            ], 404);
        }

        if ($reimbursement->nik !== $userKaryawan->nik) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke data reimbursement ini'
            ], 403);
        }

        // Get Approval History logs
        $approvals = DB::table('approvals')
            ->join('users', 'approvals.user_id', '=', 'users.id')
            ->where('approvable_type', Reimbursement::class)
            ->where('approvable_id', $reimbursement->id)
            ->select('approvals.*', 'users.name as user_name')
            ->orderBy('approvals.level', 'asc')
            ->get();

        $formattedDetails = $reimbursement->details->map(function ($d) {
            $fileUrl = null;
            if ($d->bukti_file) {
                $fileUrl = asset('storage/uploads/reimbursement/' . $d->bukti_file);
            }
            return [
                'id' => $d->id,
                'tanggal_transaksi' => $d->tanggal_transaksi,
                'kode_jenis_reimburse' => $d->kode_jenis_reimburse,
                'nama_jenis' => $d->jenis_reimbursement ? $d->jenis_reimbursement->nama_jenis : null,
                'nominal' => (double) $d->nominal,
                'keterangan' => $d->keterangan,
                'bukti_file' => $fileUrl,
            ];
        });

        $formattedApprovals = $approvals->map(function ($a) {
            return [
                'id' => $a->id,
                'user_name' => $a->user_name,
                'status' => $a->status,
                'notes' => $a->notes,
                'level' => (int) $a->level,
                'created_at' => $a->created_at ? date('Y-m-d H:i:s', strtotime($a->created_at)) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reimbursement->id,
                'no_reimbursement' => $reimbursement->no_reimbursement,
                'tanggal_pengajuan' => $reimbursement->tanggal_pengajuan,
                'total_nominal' => (double) $reimbursement->total_nominal,
                'catatan' => $reimbursement->catatan,
                'status' => $reimbursement->status,
                'approval_step' => (int) $reimbursement->approval_step,
                'details' => $formattedDetails,
                'approvals' => $formattedApprovals,
            ]
        ]);
    }

    /**
     * Delete/Cancel a pending reimbursement claim.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $userKaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $reimbursement = Reimbursement::where('id', $id)->first();

        if (!$reimbursement) {
            return response()->json([
                'success' => false,
                'message' => 'Data reimbursement tidak ditemukan'
            ], 404);
        }

        if ($reimbursement->nik !== $userKaryawan->nik) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke data reimbursement ini'
            ], 403);
        }

        if ($reimbursement->status !== 'P') {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan yang sudah diproses tidak dapat dibatalkan/dihapus'
            ], 400);
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
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan reimbursement berhasil dibatalkan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
