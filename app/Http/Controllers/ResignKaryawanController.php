<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\ResignKaryawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class ResignKaryawanController extends Controller
{
    /**
     * Get karyawan query scoped to user's access (active only)
     */
    private function getKaryawanQuery(User $user, bool $activeOnly = true)
    {
        $query = Karyawan::query();
        if ($activeOnly) {
            $query->where('status_aktif_karyawan', 1);
        }
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
        $user  = User::find(auth()->id());
        $query = ResignKaryawan::with(['karyawan', 'kategoriResign']);

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

        $resign = $query->orderBy('tanggal_resign', 'desc')->paginate(10);
        $resign->appends($request->all());

        if (!$user->isSuperAdmin()) {
            $cabangCodes = $user->getCabangCodes();
            $deptCodes   = $user->getDepartemenCodes();

            if (!empty($cabangCodes)) {
                $cabang = \App\Models\Cabang::whereIn('kode_cabang', $cabangCodes)->orderBy('nama_cabang')->get();
            } else {
                $cabang = \App\Models\Cabang::whereRaw('1 = 0')->get();
            }

            if (!empty($deptCodes)) {
                $departemen = \App\Models\Departemen::whereIn('kode_dept', $deptCodes)->orderBy('nama_dept')->get();
            } else {
                $departemen = \App\Models\Departemen::whereRaw('1 = 0')->get();
            }
        } else {
            $cabang     = \App\Models\Cabang::orderBy('nama_cabang')->get();
            $departemen = \App\Models\Departemen::orderBy('nama_dept')->get();
        }

        return view('resign.index', compact('resign', 'cabang', 'departemen'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user     = User::find(auth()->id());
        $karyawan = $this->getKaryawanQuery($user, true)->orderBy('nama_karyawan')->get();
        $kategori = \App\Models\KategoriResign::orderBy('kode_kategori')->get();
        return view('resign.create', compact('karyawan', 'kategori'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());

        $request->validate([
            'nik'          => 'required|exists:karyawan,nik',
            'tanggal_resign'=> 'required|date',
            'kode_kategori'=> 'required|string|exists:kategori_resign,kode_kategori',
            'alasan'       => 'nullable|string',
            'dokumen'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $karyawan = Karyawan::where('nik', $request->nik)->first();
        if (!$karyawan) {
            return Redirect::back()->with(['warning' => 'Data Karyawan Tidak Ditemukan']);
        }

        // Validate access scope
        if (!$this->canAccessKaryawan($user, $karyawan)) {
            abort(403, 'Anda tidak memiliki akses ke karyawan ini.');
        }

        $filename = null;
        if ($request->hasFile('dokumen')) {
            $file = $request->file('dokumen');
            $filename = 'resign_' . $request->nik . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'public/uploads/resign';
            if (!Storage::exists($destinationPath)) {
                Storage::makeDirectory($destinationPath, 0775, true);
                $path = Storage::path($destinationPath);
                chmod($path, 0775);
            }
            $file->storeAs($destinationPath, $filename);
        }

        DB::beginTransaction();
        try {
            // Catat history resign
            ResignKaryawan::create([
                'nik' => $request->nik,
                'tanggal_resign' => $request->tanggal_resign,
                'kode_kategori' => $request->kode_kategori,
                'alasan' => $request->alasan,
                'dokumen' => $filename,
                'user_id' => auth()->id()
            ]);

            // Non-aktifkan karyawan
            $karyawan->update([
                'status_aktif_karyawan' => 0,
                'tanggal_nonaktif' => $request->tanggal_resign,
            ]);

            DB::commit();
            return Redirect::route('resign.index')->with(['success' => 'Karyawan Berhasil Di-resign-kan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => 'Gagal Memproses Data: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = User::find(auth()->id());

        DB::beginTransaction();
        try {
            $resign   = ResignKaryawan::findOrFail($id);
            $karyawan = Karyawan::where('nik', $resign->nik)->first();

            // Validate access scope
            if ($karyawan && !$this->canAccessKaryawan($user, $karyawan)) {
                abort(403, 'Anda tidak memiliki akses ke data resign karyawan ini.');
            }

            // Kembalikan karyawan menjadi aktif
            if ($karyawan) {
                $karyawan->update([
                    'status_aktif_karyawan' => 1,
                    'tanggal_nonaktif' => null,
                ]);
            }

            // Delete file if exists
            if ($resign->dokumen && Storage::exists('public/uploads/resign/' . $resign->dokumen)) {
                Storage::delete('public/uploads/resign/' . $resign->dokumen);
            }

            $resign->delete();
            
            DB::commit();
            return Redirect::back()->with(['success' => 'Data Resign Berhasil Dibatalkan, Karyawan Kembali Aktif']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => 'Gagal Membatalkan Data: ' . $e->getMessage()]);
        }
    }
}
