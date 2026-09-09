<?php

namespace App\Http\Controllers;

use App\Models\Jenistunjangan;
use App\Models\Pph21FormulaKomponen;
use App\Models\Pph21ProgresifRate;
use App\Models\Pph21Setting;
use App\Models\Pph21SlipDetail;
use App\Models\Pph21TerRate;
use App\Models\Statuskawin;
use App\Services\Pph21Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class Pph21Controller extends Controller
{
    protected Pph21Service $pph21Service;

    public function __construct(Pph21Service $pph21Service)
    {
        $this->pph21Service = $pph21Service;
    }

    // =========================================================
    // HALAMAN UTAMA — Setting Global
    // =========================================================

    public function index()
    {
        $data['setting']    = Pph21Setting::getSetting();
        $data['statuskawin'] = Statuskawin::orderBy('nilai_ptkp')->get();
        return view('datamaster.pph21.index', $data);
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'metode'                  => 'required|in:TER,PROGRESIF',
            'metode_tanggungan'       => 'required|in:GROSS,GROSS_UP',
            'biaya_jabatan_persen'    => 'required|numeric|min:0|max:100',
            'biaya_jabatan_max_bulan' => 'required|integer|min:0',
        ]);

        try {
            $setting = Pph21Setting::getSetting();
            $setting->update([
                'status_aktif'            => $request->has('status_aktif') ? true : false,
                'metode'                  => $request->metode,
                'metode_tanggungan'       => $request->metode_tanggungan,
                'biaya_jabatan_persen'    => $request->biaya_jabatan_persen,
                'biaya_jabatan_max_bulan' => toNumber($request->biaya_jabatan_max_bulan),
            ]);
            return Redirect::back()->with(messageSuccess('Pengaturan PPh 21 berhasil disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Gagal menyimpan: ' . $e->getMessage()));
        }
    }

    // =========================================================
    // KOMPONEN FORMULA
    // =========================================================

    public function formula()
    {
        $data['komponens']       = Pph21FormulaKomponen::orderBy('urutan')->get();
        $data['jenistunjangan']  = Jenistunjangan::orderBy('kode_jenis_tunjangan')->get();
        return view('datamaster.pph21.formula', $data);
    }

    public function storeFormula(Request $request)
    {
        $request->validate([
            'nama_komponen' => 'required|string|max:100',
            'tipe'          => 'required|in:penambah,pengurang',
            'sumber'        => 'required|in:gaji_pokok,tunjangan,bpjs_kesehatan,bpjs_tenagakerja,lembur',
        ]);

        try {
            $maxUrutan = Pph21FormulaKomponen::max('urutan') ?? 0;
            Pph21FormulaKomponen::create([
                'nama_komponen' => $request->nama_komponen,
                'tipe'          => $request->tipe,
                'sumber'        => $request->sumber,
                'kode_sumber'   => $request->kode_sumber ?: null,
                'status_aktif'  => true,
                'urutan'        => $maxUrutan + 1,
            ]);
            return Redirect::back()->with(messageSuccess('Komponen berhasil ditambahkan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function toggleFormula($id)
    {
        try {
            $komponen = Pph21FormulaKomponen::findOrFail($id);
            $komponen->update(['status_aktif' => !$komponen->status_aktif]);
            return Redirect::back()->with(messageSuccess('Status komponen berhasil diubah'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function destroyFormula($id)
    {
        try {
            Pph21FormulaKomponen::findOrFail($id)->delete();
            return Redirect::back()->with(messageSuccess('Komponen berhasil dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function reorderFormula(Request $request)
    {
        try {
            foreach ($request->urutan as $id => $urutan) {
                Pph21FormulaKomponen::where('id', $id)->update(['urutan' => $urutan]);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // =========================================================
    // TABEL TER
    // =========================================================

    public function terRates()
    {
        $data['terA'] = Pph21TerRate::where('kategori', 'A')->orderBy('penghasilan_dari')->get();
        $data['terB'] = Pph21TerRate::where('kategori', 'B')->orderBy('penghasilan_dari')->get();
        $data['terC'] = Pph21TerRate::where('kategori', 'C')->orderBy('penghasilan_dari')->get();
        return view('datamaster.pph21.ter', $data);
    }

    public function updateTerRate(Request $request, $id)
    {
        $request->validate([
            'tarif_persen' => 'required|numeric|min:0|max:100',
        ]);

        try {
            Pph21TerRate::findOrFail($id)->update([
                'tarif_persen' => $request->tarif_persen,
                'status_aktif' => $request->has('status_aktif'),
            ]);
            return Redirect::back()->with(messageSuccess('Tarif TER berhasil diperbarui'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    // =========================================================
    // TABEL PROGRESIF
    // =========================================================

    public function progresifRates()
    {
        $data['rates'] = Pph21ProgresifRate::orderBy('pkp_dari')->get();
        return view('datamaster.pph21.progresif_rates', $data);
    }

    public function updateProgresifRate(Request $request, $id)
    {
        $request->validate([
            'tarif_persen' => 'required|numeric|min:0|max:100',
            'pkp_dari'     => 'required|integer|min:0',
            'pkp_sampai'   => 'nullable|integer|min:0',
        ]);

        try {
            Pph21ProgresifRate::findOrFail($id)->update([
                'pkp_dari'     => $request->pkp_dari,
                'pkp_sampai'   => $request->pkp_sampai ?: null,
                'tarif_persen' => $request->tarif_persen,
                'status_aktif' => $request->has('status_aktif'),
            ]);
            return Redirect::back()->with(messageSuccess('Tarif Progresif berhasil diperbarui'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    // =========================================================
    // SIMULASI / KALKULATOR
    // =========================================================

    public function simulasi()
    {
        $data['setting']        = Pph21Setting::getSetting();
        $data['statuskawin']    = Statuskawin::orderBy('kode_status_kawin')->get();
        $data['jenistunjangan'] = Jenistunjangan::orderBy('kode_jenis_tunjangan')->get();
        $data['komponens']      = Pph21FormulaKomponen::orderBy('urutan')->get();
        return view('datamaster.pph21.simulasi', $data);
    }

    public function hitungSimulasi(Request $request)
    {
        $request->validate([
            'gaji_pokok'       => 'required',
            'kode_status_kawin'=> 'required|exists:status_kawin,kode_status_kawin',
            'bulan'            => 'required|integer|min:1|max:12',
        ]);

        $nilaiKomponen = [
            'gaji_pokok'       => toNumber($request->gaji_pokok),
            'bpjs_kesehatan'   => toNumber($request->bpjs_kesehatan ?? 0),
            'bpjs_tenagakerja' => toNumber($request->bpjs_tenagakerja ?? 0),
            'lembur'           => toNumber($request->lembur ?? 0),
            'tunjangan'        => [],
        ];

        // Proses tunjangan dari form
        if ($request->has('tunjangan')) {
            foreach ($request->tunjangan as $kode => $nilai) {
                $nilaiKomponen['tunjangan'][$kode] = toNumber($nilai);
            }
        }

        $hasil = $this->pph21Service->hitung(
            $nilaiKomponen,
            $request->kode_status_kawin,
            (int)$request->bulan
        );

        $statusKawin = Statuskawin::where('kode_status_kawin', $request->kode_status_kawin)->first();
        $hasil['nama_status_kawin'] = $statusKawin ? $statusKawin->status_kawin : '-';
        $hasil['kode_status_kawin'] = $request->kode_status_kawin;
        $hasil['nilai_ptkp']        = $this->pph21Service->getNilaiPtkp($request->kode_status_kawin);

        return response()->json([
            'success' => true,
            'data'    => $hasil,
        ]);
    }

    // =========================================================
    // DATA SNAPSHOT SLIP
    // =========================================================

    public function generateSlip(Request $request)
    {
        // Generate snapshot PPh 21 untuk semua karyawan dalam satu slip gaji
        // Dipanggil dari SlipgajiController atau LaporanController
        $request->validate([
            'kode_slip_gaji' => 'required|exists:slip_gaji,kode_slip_gaji',
        ]);

        try {
            // Hapus snapshot lama
            $this->pph21Service->hapusSnapshot($request->kode_slip_gaji);
            return Redirect::back()->with(messageSuccess('Data PPh 21 berhasil di-generate ulang'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }
}
