<?php

namespace App\Http\Controllers;

use App\Models\Pelatihan;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PelatihanController extends Controller
{
    public function index($nik)
    {
        $nik = Crypt::decrypt($nik);
        $pelatihan = Pelatihan::where('nik', $nik)->orderBy('tanggal_pelatihan', 'desc')->get();
        return view('datamaster.karyawan.pelatihan.getpelatihan', compact('pelatihan', 'nik'));
    }

    public function create($nik)
    {
        $nik = Crypt::decrypt($nik);
        return view('datamaster.karyawan.pelatihan.create', compact('nik'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'nama_pelatihan' => 'required',
            'penyelenggara' => 'required',
            'tanggal_pelatihan' => 'required|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            $foto = null;
            if ($request->hasFile('foto')) {
                $foto_name = $request->nik . "-" . time() . "." . $request->file('foto')->getClientOriginalExtension();
                $destination_path = "/public/pelatihan";
                $request->file('foto')->storeAs($destination_path, $foto_name);
                $foto = $foto_name;
            }

            Pelatihan::create([
                'nik' => $request->nik,
                'nama_pelatihan' => $request->nama_pelatihan,
                'penyelenggara' => $request->penyelenggara,
                'tanggal_pelatihan' => $request->tanggal_pelatihan,
                'foto' => $foto
            ]);

            return Redirect::back()->with(messageSuccess('Data Pelatihan Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $pelatihan = Pelatihan::find($id);
        try {
            if ($pelatihan->foto) {
                Storage::delete("/public/pelatihan/" . $pelatihan->foto);
            }
            $pelatihan->delete();
            return Redirect::back()->with(messageSuccess('Data Pelatihan Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }
}
