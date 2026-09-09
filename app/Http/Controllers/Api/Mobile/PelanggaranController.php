<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\Userkaryawan;
use App\Models\Pengaturanumum;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PelanggaranController extends Controller
{
    /**
     * Get list of violations (SP) for the authenticated employee.
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

        $pelanggarans = Pelanggaran::select(
            'pelanggaran.*',
            'jabatan.nama_jabatan',
            'cabang.nama_cabang',
            'departemen.nama_dept'
        )
            ->join('karyawan', 'pelanggaran.nik', '=', 'karyawan.nik')
            ->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->where('pelanggaran.nik', $userKaryawan->nik)
            ->orderByDesc('pelanggaran.tanggal')
            ->get();

        $data = $pelanggarans->map(function ($p) {
            return [
                'no_sp' => $p->no_sp,
                'no_dokumen' => $p->no_dokumen,
                'tanggal' => $p->tanggal ? $p->tanggal->format('Y-m-d') : null,
                'dari' => $p->dari ? $p->dari->format('Y-m-d') : null,
                'sampai' => $p->sampai ? $p->sampai->format('Y-m-d') : null,
                'jenis_sp' => $p->jenis_sp,
                'keterangan' => $p->keterangan,
                'nama_jabatan' => $p->nama_jabatan,
                'nama_cabang' => $p->nama_cabang,
                'nama_dept' => $p->nama_dept,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get detail of a specific violation (SP).
     */
    public function show(Request $request, $no_sp)
    {
        $user = $request->user();
        $userKaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userKaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terdaftar sebagai karyawan'
            ], 403);
        }

        $pelanggaran = Pelanggaran::select(
            'pelanggaran.*',
            'karyawan.nama_karyawan',
            'karyawan.nik_show',
            'karyawan.alamat',
            'jabatan.nama_jabatan',
            'cabang.nama_cabang',
            'departemen.nama_dept'
        )
            ->join('karyawan', 'pelanggaran.nik', '=', 'karyawan.nik')
            ->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->where('pelanggaran.no_sp', $no_sp)
            ->first();

        if (!$pelanggaran) {
            return response()->json([
                'success' => false,
                'message' => 'Data pelanggaran tidak ditemukan'
            ], 404);
        }

        if ($pelanggaran->nik !== $userKaryawan->nik) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke data pelanggaran ini'
            ], 403);
        }

        $pengaturan = Pengaturanumum::first();

        return response()->json([
            'success' => true,
            'data' => [
                'no_sp' => $pelanggaran->no_sp,
                'no_dokumen' => $pelanggaran->no_dokumen,
                'tanggal' => $pelanggaran->tanggal ? $pelanggaran->tanggal->format('Y-m-d') : null,
                'dari' => $pelanggaran->dari ? $pelanggaran->dari->format('Y-m-d') : null,
                'sampai' => $pelanggaran->sampai ? $pelanggaran->sampai->format('Y-m-d') : null,
                'jenis_sp' => $pelanggaran->jenis_sp,
                'keterangan' => $pelanggaran->keterangan,
                'nama_karyawan' => $pelanggaran->nama_karyawan,
                'nik_show' => $pelanggaran->nik_show,
                'alamat' => $pelanggaran->alamat,
                'nama_jabatan' => $pelanggaran->nama_jabatan,
                'nama_cabang' => $pelanggaran->nama_cabang,
                'nama_dept' => $pelanggaran->nama_dept,
                'nama_perusahaan' => $pengaturan ? $pengaturan->nama_perusahaan : null,
            ]
        ]);
    }
}
