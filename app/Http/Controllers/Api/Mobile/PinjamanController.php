<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pinjaman;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;

class PinjamanController extends Controller
{
    /**
     * Get loan summary and history for the authenticated employee.
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

        $pinjamans = Pinjaman::with([
            'rencana_cicilan' => function($q) {
                $q->orderBy('tahun', 'asc')->orderBy('bulan', 'asc');
            },
            'pembayaran_pinjaman' => function($q) {
                $q->orderBy('tanggal_bayar', 'desc');
            }
        ])
            ->where('nik', $userKaryawan->nik)
            ->where('status', '!=', 'B') // Exclude Cancelled
            ->orderBy('id', 'desc')
            ->get();

        $totalPinjaman = $pinjamans->sum('jumlah_pinjaman');
        $totalDibayar = $pinjamans->sum('total_dibayar');
        $sisaPinjaman = $pinjamans->sum('sisa_pinjaman');

        $data = $pinjamans->map(function ($p) {
            return [
                'id' => $p->id,
                'no_pinjaman' => $p->no_pinjaman,
                'tanggal_pinjaman' => $p->tanggal_pinjaman,
                'jumlah_pinjaman' => (double) $p->jumlah_pinjaman,
                'sisa_pinjaman' => (double) $p->sisa_pinjaman,
                'total_dibayar' => (double) $p->total_dibayar,
                'jumlah_cicilan' => (int) $p->jumlah_cicilan,
                'status' => $p->status,
                'rencana_cicilan' => $p->rencana_cicilan->map(function ($rc) {
                    return [
                        'id' => $rc->id,
                        'cicilan_ke' => (int) $rc->cicilan_ke,
                        'bulan' => (int) $rc->bulan,
                        'tahun' => (int) $rc->tahun,
                        'jumlah_cicilan' => (double) $rc->jumlah_cicilan,
                        'status' => $rc->status,
                    ];
                }),
                'pembayaran_pinjaman' => $p->pembayaran_pinjaman->map(function ($pp) {
                    return [
                        'id' => $pp->id,
                        'tanggal_bayar' => $pp->tanggal_bayar,
                        'no_bukti' => $pp->no_bukti,
                        'jumlah_bayar' => (double) $pp->jumlah_bayar,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'sisa_saldo_pinjaman' => (double) $sisaPinjaman,
                'total_pinjaman' => (double) $totalPinjaman,
                'total_dibayar' => (double) $totalDibayar,
                'pinjaman' => $data,
            ]
        ]);
    }
}
