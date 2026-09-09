<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\KpiIndicator;
use App\Models\KpiIndicatorDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class KpiIndicatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = KpiIndicator::query();
        $query->with(['jabatan', 'departemen', 'details']);
        $query->leftJoin('jabatan', 'kpi_indicators.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $query->leftJoin('departemen', 'kpi_indicators.kode_dept', '=', 'departemen.kode_dept');
        $query->orderByRaw('ISNULL(kpi_indicators.kode_jabatan) DESC'); // Global first? Or last? Let's put Global first.
        $query->orderBy('jabatan.nama_jabatan');
        $query->orderBy('departemen.nama_dept');
        
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $query->whereIn('kpi_indicators.kode_dept', $user->getDepartemenCodes());
        }

        if (!empty($request->nama_jabatan)) {
            $query->where('jabatan.nama_jabatan', 'like', '%' . $request->nama_jabatan . '%');
        }
        
        $kpi_indicators = $query->select('kpi_indicators.*')->paginate(10);
        
        // Also need list of Jabatan for the modal (to create new)
        $jabatan_list = Jabatan::orderBy('nama_jabatan')->get();
        // And list of Departemen
        $departemen_list = $user->getDepartemen();

        return view('kpi.indicators.index', compact('kpi_indicators', 'jabatan_list', 'departemen_list'));
    }

    public function create(Request $request)
    {
        $kode_jabatan = $request->get('kode_jabatan');
        $kode_dept = $request->get('kode_dept');
        $jabatan = null;
        $departemen = null;
        
        $user = auth()->user();
        
        if ($request->get('scope') == 'global') {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Akses ditolak.');
            }
            // Check if Global KPI already exists
            $existing = KpiIndicator::whereNull('kode_jabatan')->whereNull('kode_dept')->first();
            if ($existing) {
                 return redirect()->route('kpi.indicators.edit', $existing->id)->with(['info' => 'Konfigurasi KPI Global sudah ada']);
            }
            return view('kpi.indicators.create', [
                'jabatan' => null, 
                'departemen' => null,
                'is_global' => true
            ]);
        }
        
        if ($kode_jabatan && $kode_dept) {
            if (!$user->isSuperAdmin() && !in_array($kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Akses ditolak.');
            }
            $jabatan = Jabatan::where('kode_jabatan', $kode_jabatan)->first();
            $departemen = \App\Models\Departemen::where('kode_dept', $kode_dept)->first();

            if (!$jabatan || !$departemen) {
                return redirect()->route('kpi.indicators.index')->with(['warning' => 'Jabatan atau Departemen tidak ditemukan']);
            }
            // Check if already has KPI
            $existing = KpiIndicator::where('kode_jabatan', $kode_jabatan)->where('kode_dept', $kode_dept)->first();
            if ($existing) {
                return redirect()->route('kpi.indicators.edit', $existing->id)->with(['info' => 'Konfigurasi KPI untuk Jabatan dan Departemen ini sudah ada']);
            }
        }
        
        return view('kpi.indicators.create', compact('jabatan', 'departemen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_jabatan' => 'nullable',
            'kode_dept' => 'nullable',
            'indicators_data' => 'required|json',
        ]);

        $user = auth()->user();
        if (empty($request->kode_dept)) {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Akses ditolak.');
            }
        } else {
            if (!$user->isSuperAdmin() && !in_array($request->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Akses ditolak.');
            }
        }

        // Check uniqueness manually for composite key
        $exists = KpiIndicator::where('kode_jabatan', $request->kode_jabatan)
                              ->where('kode_dept', $request->kode_dept)
                              ->exists();
        
        if ($exists) {
            return Redirect::back()->with(['warning' => 'Konfigurasi KPI untuk Jabatan dan Departemen ini sudah ada']);
        }

        DB::beginTransaction();
        try {
            // Parse indicators data from JSON
            $indicators = json_decode($request->indicators_data, true);
            
            if (empty($indicators)) {
                return Redirect::back()->with(['warning' => 'Minimal harus ada 1 indikator']);
            }

            // Validate total bobot
            $totalBobot = array_sum(array_column($indicators, 'bobot'));
            if ($totalBobot != 100) {
                return Redirect::back()->with(['warning' => 'Total bobot harus 100%']);
            }

            // Create Header
            $kpi_indicator = KpiIndicator::create([
                'kode_jabatan' => $request->kode_jabatan,
                'kode_dept' => $request->kode_dept
            ]);

            // Create Details from JSON data
            foreach ($indicators as $indicator) {
                KpiIndicatorDetail::create([
                    'kpi_indicator_id' => $kpi_indicator->id,
                    'nama_indikator' => $indicator['nama_indikator'],
                    'deskripsi' => $indicator['deskripsi'] ?? null,
                    'satuan' => $indicator['satuan'],
                    'jenis_target' => $indicator['jenis_target'],
                    'bobot' => $indicator['bobot'],
                    'target' => $indicator['target'],
                    'mode' => $indicator['mode'] ?? 'manual',
                    'metric_source' => $indicator['metric_source'] ?? null
                ]);
            }

            DB::commit();
            return Redirect::route('kpi.indicators.index')->with(['success' => 'Paket KPI Berhasil Disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $kpi_indicator = KpiIndicator::with('details', 'jabatan')->findOrFail($id);
        $user = auth()->user();
        if (empty($kpi_indicator->kode_dept)) {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Akses ditolak.');
            }
        } else {
            if (!$user->isSuperAdmin() && !in_array($kpi_indicator->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Akses ditolak.');
            }
        }
        return view('kpi.indicators.edit', compact('kpi_indicator'));
    }

    public function update(Request $request, string $id)
    {
        $kpi_indicator = KpiIndicator::findOrFail($id);
        $user = auth()->user();
        if (empty($kpi_indicator->kode_dept)) {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Akses ditolak.');
            }
        } else {
            if (!$user->isSuperAdmin() && !in_array($kpi_indicator->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Akses ditolak.');
            }
        }

        $request->validate([
            'indicators_data' => 'required|json',
        ]);

        DB::beginTransaction();
        try {
            // Parse indicators data from JSON
            $indicators = json_decode($request->indicators_data, true);
            
            if (empty($indicators)) {
                return Redirect::back()->with(['warning' => 'Minimal harus ada 1 indikator']);
            }

            // Validate total bobot
            $totalBobot = array_sum(array_column($indicators, 'bobot'));
            if ($totalBobot != 100) {
                return Redirect::back()->with(['warning' => 'Total bobot harus 100%']);
            }

            // Replace Details - delete old and create new
            KpiIndicatorDetail::where('kpi_indicator_id', $id)->delete();

            foreach ($indicators as $indicator) {
                KpiIndicatorDetail::create([
                    'kpi_indicator_id' => $kpi_indicator->id,
                    'nama_indikator' => $indicator['nama_indikator'],
                    'deskripsi' => $indicator['deskripsi'] ?? null,
                    'satuan' => $indicator['satuan'],
                    'jenis_target' => $indicator['jenis_target'],
                    'bobot' => $indicator['bobot'],
                    'target' => $indicator['target'],
                    'mode' => $indicator['mode'] ?? 'manual',
                    'metric_source' => $indicator['metric_source'] ?? null
                ]);
            }

            DB::commit();
            return Redirect::back()->with(['success' => 'Paket KPI Jabatan Berhasil Diupdate']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => $e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        $kpi_indicator = KpiIndicator::findOrFail($id);
        $user = auth()->user();
        if (empty($kpi_indicator->kode_dept)) {
            if (!$user->isSuperAdmin()) {
                abort(403, 'Akses ditolak.');
            }
        } else {
            if (!$user->isSuperAdmin() && !in_array($kpi_indicator->kode_dept, $user->getDepartemenCodes())) {
                abort(403, 'Akses ditolak.');
            }
        }

        try {
            $kpi_indicator->delete();
            return Redirect::back()->with(['success' => 'Paket KPI Jabatan Berhasil Dihapus']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => $e->getMessage()]);
        }
    }
}
