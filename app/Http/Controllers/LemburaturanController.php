<?php

namespace App\Http\Controllers;

use App\Models\LemburAturan;
use App\Models\LemburKaryawanKhusus;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class LemburaturanController extends Controller
{
    public function index(Request $request)
    {
        $data['aturan_kerja'] = LemburAturan::where('tipe_hari', '1')->orderBy('jam_dari')->get();
        $data['aturan_libur'] = LemburAturan::where('tipe_hari', '2')->orderBy('jam_dari')->get();

        $queryKhusus = LemburKaryawanKhusus::query();
        $queryKhusus->join('karyawan', 'lembur_karyawan_khusus.nik', '=', 'karyawan.nik');
        $queryKhusus->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $queryKhusus->orderBy('karyawan.nama_karyawan');

        if (!empty($request->nama_karyawan_search)) {
            $queryKhusus->where('karyawan.nama_karyawan', 'like', '%' . $request->nama_karyawan_search . '%');
        }

        $data['lembur_khusus'] = $queryKhusus->select('lembur_karyawan_khusus.*', 'karyawan.nama_karyawan', 'jabatan.nama_jabatan')->get();

        return view('konfigurasi.lembur_aturan.index', $data);
    }

    public function create(Request $request)
    {
        $data['tipe_hari'] = $request->tipe_hari;
        return view('konfigurasi.lembur_aturan.create', $data);
    }

    public function createKhusus()
    {
        return view('konfigurasi.lembur_aturan.create_khusus');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipe_hari' => 'required',
            'jam_dari' => 'required|numeric',
            'jam_sampai' => 'nullable|numeric',
            'faktor' => 'required|numeric',
        ]);

        try {
            LemburAturan::create([
                'tipe_hari' => $request->tipe_hari,
                'jam_dari' => $request->jam_dari,
                'jam_sampai' => $request->jam_sampai,
                'faktor' => $request->faktor,
            ]);
            return Redirect::back()->with(messageSuccess('Aturan Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function storeKhusus(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'upah_perjam' => 'required',
        ]);

        try {
            // Cek jika sudah ada
            $exists = LemburKaryawanKhusus::where('nik', $request->nik)->exists();
            if ($exists) {
                return Redirect::back()->with(messageError('Karyawan tersebut sudah memiliki aturan lembur khusus!'));
            }

            LemburKaryawanKhusus::create([
                'nik' => $request->nik,
                'upah_perjam' => toNumber($request->upah_perjam),
                'keterangan' => $request->keterangan,
                'status' => 1
            ]);
            return Redirect::back()->with(messageSuccess('Lembur Khusus Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data['aturan'] = LemburAturan::find($id);
        return view('konfigurasi.lembur_aturan.edit', $data);
    }

    public function editKhusus(Request $request)
    {
        $id = $request->id;
        $data['khusus'] = LemburKaryawanKhusus::join('karyawan', 'lembur_karyawan_khusus.nik', '=', 'karyawan.nik')
            ->where('lembur_karyawan_khusus.id', $id)
            ->select('lembur_karyawan_khusus.*', 'karyawan.nama_karyawan')
            ->first();
        return view('konfigurasi.lembur_aturan.edit_khusus', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipe_hari' => 'required',
            'jam_dari' => 'required|numeric',
            'jam_sampai' => 'nullable|numeric',
            'faktor' => 'required|numeric',
        ]);

        try {
            LemburAturan::where('id', $id)->update([
                'tipe_hari' => $request->tipe_hari,
                'jam_dari' => $request->jam_dari,
                'jam_sampai' => $request->jam_sampai,
                'faktor' => $request->faktor,
            ]);
            return Redirect::back()->with(messageSuccess('Aturan Berhasil Diupdate'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function updateKhusus(Request $request, $id)
    {
        $request->validate([
            'upah_perjam' => 'required',
        ]);

        try {
            LemburKaryawanKhusus::where('id', $id)->update([
                'upah_perjam' => toNumber($request->upah_perjam),
                'keterangan' => $request->keterangan,
                'status' => $request->status
            ]);
            return Redirect::back()->with(messageSuccess('Lembur Khusus Berhasil Diupdate'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function destroy($id)
    {
        try {
            LemburAturan::where('id', $id)->delete();
            return Redirect::back()->with(messageSuccess('Aturan Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function destroyKhusus($id)
    {
        try {
            LemburKaryawanKhusus::where('id', $id)->delete();
            return Redirect::back()->with(messageSuccess('Lembur Khusus Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }
}
