<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLayer;
use App\Models\Cabang;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Redirect;

class ApprovalLayerController extends Controller
{
    public function index(Request $request)
    {
        $feature = $request->query('feature');
        $query = ApprovalLayer::orderBy('feature')->orderBy('level');
        
        if ($feature) {
            $query->where('feature', $feature);
        }

        $approvalLayers = $query->get();
        $cabangs = Cabang::all();
        $departemens = Departemen::all();
        $jabatans = \App\Models\Jabatan::all();

        return view('konfigurasi.approvallayer.index', compact('approvalLayers', 'cabangs', 'departemens', 'jabatans', 'feature'));
    }

    public function create(Request $request)
    {
        $feature = $request->query('feature');
        $roles = Role::when(!auth()->user()->hasRole('master admin'), function($query) {
            return $query->where('name', '!=', 'master admin');
        })->get();
        $departemen = Departemen::all();
        $cabang = Cabang::all();
        $jabatan = \App\Models\Jabatan::all();
        return view('konfigurasi.approvallayer.create', compact('roles', 'departemen', 'cabang', 'jabatan', 'feature'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_names' => 'required|array|min:1',
            'role_names.*' => 'required|string',
        ]);

        try {
            $feature = $request->feature ?? 'IZIN';
            // Delete existing configurations for this combination and feature to prevent duplicates
            ApprovalLayer::where('kode_cabang', $request->kode_cabang)
                ->where('kode_dept', $request->kode_dept)
                ->where('kode_jabatan', $request->kode_jabatan)
                ->where('feature', $feature)
                ->delete();

            $level = 1;
            foreach ($request->role_names as $role) {
                ApprovalLayer::create([
                    'feature' => $feature,
                    'level' => $level,
                    'role_name' => $role,
                    'kode_dept' => $request->kode_dept,
                    'kode_jabatan' => $request->kode_jabatan,
                    'kode_cabang' => $request->kode_cabang,
                ]);
                $level++;
            }

            return Redirect::route('approvallayer.index', ['feature' => $feature])->with(['success' => 'Data Konfigurasi Berhasil Disimpan']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => $e->getMessage()]);
        }
    }

    public function editGroup(Request $request)
    {
        $kode_cabang = $request->query('cabang');
        $kode_dept = $request->query('dept');
        $kode_jabatan = $request->query('jabatan');
        $feature = $request->query('feature') ?? 'IZIN';

        $query = ApprovalLayer::where('feature', $feature);
        
        if ($kode_cabang !== 'ALL') $query->where('kode_cabang', $kode_cabang);
        else $query->whereNull('kode_cabang');

        if ($kode_dept !== 'ALL') $query->where('kode_dept', $kode_dept);
        else $query->whereNull('kode_dept');

        if ($kode_jabatan !== 'ALL') $query->where('kode_jabatan', $kode_jabatan);
        else $query->whereNull('kode_jabatan');

        $approvalLayers = $query->orderBy('level')->get();

        $roles = Role::when(!auth()->user()->hasRole('master admin'), function($query) {
            return $query->where('name', '!=', 'master admin');
        })->get();
        $departemen = Departemen::all();
        $cabang = Cabang::all();
        $jabatan = \App\Models\Jabatan::all();

        return view('konfigurasi.approvallayer.edit', compact(
            'approvalLayers', 'roles', 'departemen', 'cabang', 'jabatan',
            'kode_cabang', 'kode_dept', 'kode_jabatan', 'feature'
        ));
    }

    public function updateGroup(Request $request)
    {
        $request->validate([
            'role_names' => 'required|array|min:1',
            'role_names.*' => 'required|string',
        ]);

        try {
            $feature = $request->feature ?? 'IZIN';
            
            $old_cabang = $request->old_kode_cabang !== 'ALL' ? $request->old_kode_cabang : null;
            $old_dept = $request->old_kode_dept !== 'ALL' ? $request->old_kode_dept : null;
            $old_jabatan = $request->old_kode_jabatan !== 'ALL' ? $request->old_kode_jabatan : null;

            ApprovalLayer::where('feature', $feature)
                ->where('kode_cabang', $old_cabang)
                ->where('kode_dept', $old_dept)
                ->where('kode_jabatan', $old_jabatan)
                ->delete();

            // Also delete if the new target already exists to prevent duplication
            ApprovalLayer::where('feature', $feature)
                ->where('kode_cabang', $request->kode_cabang)
                ->where('kode_dept', $request->kode_dept)
                ->where('kode_jabatan', $request->kode_jabatan)
                ->delete();

            $level = 1;
            foreach ($request->role_names as $role) {
                ApprovalLayer::create([
                    'feature' => $feature,
                    'level' => $level,
                    'role_name' => $role,
                    'kode_dept' => $request->kode_dept,
                    'kode_jabatan' => $request->kode_jabatan,
                    'kode_cabang' => $request->kode_cabang,
                ]);
                $level++;
            }

            return Redirect::route('approvallayer.index', ['feature' => $feature])->with(['success' => 'Data Konfigurasi Berhasil Diupdate']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => $e->getMessage()]);
        }
    }

    public function destroyGroup(Request $request)
    {
        try {
            $feature = $request->query('feature') ?? 'IZIN';
            $kode_cabang = $request->query('cabang') !== 'ALL' ? $request->query('cabang') : null;
            $kode_dept = $request->query('dept') !== 'ALL' ? $request->query('dept') : null;
            $kode_jabatan = $request->query('jabatan') !== 'ALL' ? $request->query('jabatan') : null;

            ApprovalLayer::where('feature', $feature)
                ->where('kode_cabang', $kode_cabang)
                ->where('kode_dept', $kode_dept)
                ->where('kode_jabatan', $kode_jabatan)
                ->delete();

            return Redirect::back()->with(['success' => 'Data Konfigurasi Berhasil Dihapus']);
        } catch (\Exception $e) {
            return Redirect::back()->with(['warning' => $e->getMessage()]);
        }
    }
}
