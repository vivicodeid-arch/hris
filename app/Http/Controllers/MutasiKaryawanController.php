<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\MutasiKaryawan;
use App\Models\Statuskaryawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class MutasiKaryawanController extends Controller
{
    /**
     * Get karyawan query scoped to user access
     */
    private function getKaryawanQuery(User $user)
    {
        $query = Karyawan::query();
        if (!$user->isSuperAdmin()) {
            $cabangCodes = $user->getCabangCodes();
            $deptCodes   = $user->getDepartemenCodes();
            if (!empty($cabangCodes)) {
                $query->whereIn('kode_cabang', $cabangCodes);
            } else {
                $query->whereRaw('1 = 0');
            }
            if (!empty($deptCodes)) {
                $query->whereIn('kode_dept', $deptCodes);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        return $query;
    }

    /**
     * Check whether user can access a given karyawan
     */
    private function canAccessKaryawan(User $user, Karyawan $karyawan): bool
    {
        if ($user->isSuperAdmin()) return true;
        $cabangCodes = $user->getCabangCodes();
        $deptCodes   = $user->getDepartemenCodes();
        return in_array($karyawan->kode_cabang, $cabangCodes)
            && in_array($karyawan->kode_dept, $deptCodes);
    }
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());

        $query = MutasiKaryawan::with(['karyawan', 'cabangLama', 'cabangBaru', 'deptLama', 'deptBaru', 'jabatanLama', 'jabatanBaru']);

        // Scope records to user's access
        if (!$user->isSuperAdmin()) {
            $cabangCodes = $user->getCabangCodes();
            $deptCodes   = $user->getDepartemenCodes();
            $query->whereHas('karyawan', function ($q) use ($cabangCodes, $deptCodes) {
                if (!empty($cabangCodes)) {
                    $q->whereIn('kode_cabang', $cabangCodes);
                } else {
                    $q->whereRaw('1 = 0');
                }
                if (!empty($deptCodes)) {
                    $q->whereIn('kode_dept', $deptCodes);
                } else {
                    $q->whereRaw('1 = 0');
                }
            });
        }

        if (!empty($request->nama_karyawan)) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
            });
        }

        if ($request->filled('kode_cabang')) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('kode_cabang', $request->kode_cabang);
            });
        }

        if ($request->filled('kode_dept')) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('kode_dept', $request->kode_dept);
            });
        }

        $query->orderBy('tanggal_mutasi', 'desc');
        $mutasi = $query->paginate(10);
        $mutasi->appends($request->all());

        if (!$user->isSuperAdmin()) {
            $cabangCodes = $user->getCabangCodes();
            $deptCodes   = $user->getDepartemenCodes();

            if (!empty($cabangCodes)) {
                $cabang = Cabang::whereIn('kode_cabang', $cabangCodes)->orderBy('nama_cabang')->get();
            } else {
                $cabang = Cabang::whereRaw('1 = 0')->get();
            }

            if (!empty($deptCodes)) {
                $departemen = Departemen::whereIn('kode_dept', $deptCodes)->orderBy('nama_dept')->get();
            } else {
                $departemen = Departemen::whereRaw('1 = 0')->get();
            }
        } else {
            $cabang          = Cabang::orderBy('nama_cabang')->get();
            $departemen      = Departemen::orderBy('nama_dept')->get();
        }
        $karyawan        = $this->getKaryawanQuery($user)->orderBy('nama_karyawan')->get();
        $jabatan         = Jabatan::orderBy('nama_jabatan')->get();
        $status_karyawan = Statuskaryawan::orderBy('kode_status_karyawan')->get();

        return view('mutasi.index', compact('mutasi', 'karyawan', 'cabang', 'departemen', 'jabatan', 'status_karyawan'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user            = User::find(auth()->id());
        $karyawan        = $this->getKaryawanQuery($user)->orderBy('nama_karyawan')->get();
        $cabang          = Cabang::orderBy('nama_cabang')->get();
        $departemen      = Departemen::orderBy('nama_dept')->get();
        $jabatan         = Jabatan::orderBy('nama_jabatan')->get();
        $status_karyawan = Statuskaryawan::orderBy('kode_status_karyawan')->get();

        return view('mutasi.create', compact('karyawan', 'cabang', 'departemen', 'jabatan', 'status_karyawan'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());

        $request->validate([
            'nik'              => 'required',
            'tanggal_mutasi'   => 'required|date',
            'jenis_mutasi'     => 'required',
            'kode_cabang_baru' => 'required',
            'kode_dept_baru'   => 'required',
            'kode_jabatan_baru'=> 'required',
            'doc_sk'           => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $karyawan = Karyawan::find($request->nik);
        if (!$karyawan) {
            return Redirect::back()->with(['warning' => 'Data Karyawan Tidak Ditemukan']);
        }

        // Validate access scope
        if (!$this->canAccessKaryawan($user, $karyawan)) {
            abort(403, 'Anda tidak memiliki akses ke karyawan ini.');
        }

        DB::beginTransaction();
        try {
            $doc_sk = null;
            if ($request->hasFile('doc_sk')) {
                $doc_sk = $request->file('doc_sk')->store('mutasi_sk', 'public');
            }

            MutasiKaryawan::create([
                'nik' => $request->nik,
                'tanggal_mutasi' => $request->tanggal_mutasi,
                'jenis_mutasi' => $request->jenis_mutasi,
                'kode_cabang_lama' => $karyawan->kode_cabang,
                'kode_cabang_baru' => $request->kode_cabang_baru,
                'kode_dept_lama' => $karyawan->kode_dept,
                'kode_dept_baru' => $request->kode_dept_baru,
                'kode_jabatan_lama' => $karyawan->kode_jabatan,
                'kode_jabatan_baru' => $request->kode_jabatan_baru,
                'status_karyawan_lama' => $karyawan->status_karyawan,
                'status_karyawan_baru' => $request->status_karyawan_baru ?? $karyawan->status_karyawan,
                'keterangan' => $request->keterangan,
                'doc_sk' => $doc_sk,
                'user_id' => auth()->id()
            ]);

            // Update Data Karyawan
            $karyawan->update([
                'kode_cabang' => $request->kode_cabang_baru,
                'kode_dept' => $request->kode_dept_baru,
                'kode_jabatan' => $request->kode_jabatan_baru,
                'status_karyawan' => $request->status_karyawan_baru ?? $karyawan->status_karyawan
            ]);

            DB::commit();
            return Redirect::route('mutasi.index')->with(['success' => 'Data Mutasi Berhasil Disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => 'Gagal Menyimpan Data: ' . $e->getMessage()]);
        }
    }
    
    public function getKaryawan($nik)
    {
        /** @var \App\Models\User $user */
        $user     = User::find(auth()->id());
        $karyawan = Karyawan::with(['cabang', 'departemen', 'jabatan'])->where('nik', $nik)->first();

        if ($karyawan && !$this->canAccessKaryawan($user, $karyawan)) {
            return response()->json(null, 403);
        }

        return response()->json($karyawan);
    }
    
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());

        DB::beginTransaction();
        try {
            $mutasi   = MutasiKaryawan::findOrFail($id);
            $karyawan = Karyawan::where('nik', $mutasi->nik)->first();

            // Validate access scope
            if ($karyawan && !$this->canAccessKaryawan($user, $karyawan)) {
                abort(403, 'Anda tidak memiliki akses ke data mutasi karyawan ini.');
            }

            // Kembalikan data karyawan ke data lama
            if ($karyawan) {
                $karyawan->update([
                    'kode_cabang' => $mutasi->kode_cabang_lama,
                    'kode_dept' => $mutasi->kode_dept_lama,
                    'kode_jabatan' => $mutasi->kode_jabatan_lama,
                    'status_karyawan' => $mutasi->status_karyawan_lama
                ]);
            }

            if ($mutasi->doc_sk) {
                Storage::disk('public')->delete('uploads/mutasi/' . $mutasi->doc_sk); // Pastikan path sesuai
            }
            
            $mutasi->delete();
            
            DB::commit();
            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus dan Data Karyawan Dikembalikan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => 'Gagal Menghapus Data: ' . $e->getMessage()]);
        }
    }
}
