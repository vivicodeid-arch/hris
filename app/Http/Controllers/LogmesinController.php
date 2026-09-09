<?php

namespace App\Http\Controllers;

use App\Models\LogMesinPresensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogmesinController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('log_mesin_presensis')
            ->select(
                'log_mesin_presensis.*',
                'karyawan.nama_karyawan',
                'mesin_fingerprints.nama_mesin'
            )
            ->leftJoin('karyawan', 'log_mesin_presensis.pin', '=', 'karyawan.pin')
            ->leftJoin('mesin_fingerprints', 'log_mesin_presensis.id_mesin', '=', 'mesin_fingerprints.id');

        if ($request->filled('nama_karyawan')) {
            $query->where(function ($q) use ($request) {
                $q->where('karyawan.nama_karyawan', 'like', '%' . $request->nama_karyawan . '%')
                    ->orWhere('log_mesin_presensis.pin', 'like', '%' . $request->nama_karyawan . '%');
            });
        }

        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('jam_absen', [$request->tanggal_awal . ' 00:00:00', $request->tanggal_akhir . ' 23:59:59']);
        }

        $logs = $query->orderBy('jam_absen', 'desc')->paginate(20);
        $logs->appends($request->all());

        return view('utilities.logmesin.index', compact('logs'));
    }
}
