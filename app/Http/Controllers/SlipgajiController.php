<?php

namespace App\Http\Controllers;

use App\Models\Slipgaji;
use App\Models\SlipgajiHarian;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;

class SlipgajiController extends Controller
{
    public function index(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->first();

        $data['start_year'] = config('global.start_year');
        if ($user->hasRole('karyawan')) {
            $data['slipgaji'] = Slipgaji::orderBy('tahun')
                ->orderBy('bulan')
                ->where('status', '1')
                ->get();
            return view('payroll.slipgaji.index_mobile', $data);
        } else {
            $data['slipgaji'] = Slipgaji::orderBy('tahun')->orderBy('bulan')->get();

            $queryHarian = SlipgajiHarian::withCount('detail');
            if (!empty($request->tahun_harian)) {
                $queryHarian->whereYear('tanggal_slip', $request->tahun_harian);
            }
            $data['slipgaji_harian'] = $queryHarian->orderBy('tanggal_slip', 'desc')->get();

            $data['cabang'] = $user->getCabang();
            $data['departemen'] = $user->getDepartemen();
            return view('payroll.slipgaji.index', $data);
        }
    }

    public function create()
    {
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        return view('payroll.slipgaji.create', $data);
    }

    public function store(Request $request)
    {

        try {
            $slipgaji = Slipgaji::create([
                'kode_slip_gaji' => 'GJ' . $request->bulan . $request->tahun,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'status' => $request->status
            ]);

            if ($request->status == '1') {
                $this->sendPublishNotification($slipgaji);
            }

            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function edit($kode_slip_gaji)
    {
        $kode_slip_gaji = Crypt::decrypt($kode_slip_gaji);
        $data['slipgaji'] = Slipgaji::where('kode_slip_gaji', $kode_slip_gaji)->first();
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        return view('payroll.slipgaji.edit', $data);
    }

    public function update(Request $request, $kode_slip_gaji)
    {
        $kode_slip_gaji = Crypt::decrypt($kode_slip_gaji);
        try {
            $slipgaji = Slipgaji::where('kode_slip_gaji', $kode_slip_gaji)->first();
            $oldStatus = $slipgaji ? $slipgaji->status : null;

            Slipgaji::where('kode_slip_gaji', $kode_slip_gaji)->update([
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'status' => $request->status
            ]);

            if ($oldStatus != '1' && $request->status == '1' && $slipgaji) {
                $this->sendPublishNotification($slipgaji);
            }

            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function destroy($kode_slip_gaji)
    {
        $kode_slip_gaji = Crypt::decrypt($kode_slip_gaji);
        try {
            Slipgaji::where('kode_slip_gaji', $kode_slip_gaji)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    private function sendPublishNotification($slipgaji)
    {
        try {
            $users = User::role('karyawan')->get();
            if ($users->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\SlipgajiNotification($slipgaji));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim notifikasi slip gaji: ' . $e->getMessage());
        }
    }
}
