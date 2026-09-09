<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectMember;
use App\Models\Karyawan;
use App\Models\Cabang;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::with(['category', 'creator', 'departemen', 'cabang', 'members.karyawan']);

        // Check if user is karyawan (restrict to projects where they are a member)
        $user = Auth::user();
        if ($user->hasRole('karyawan')) {
            $nik = DB::table('users_karyawan')->where('id_user', $user->id)->value('nik');
            $query->whereHas('members', function($q) use ($nik) {
                $q->where('nik', $nik);
            });
        }

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_project', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_project', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('kode_dept')) {
            $query->where('kode_dept', $request->kode_dept);
        }

        if ($request->filled('kode_cabang')) {
            $query->where('kode_cabang', $request->kode_cabang);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->orderBy('end_date', 'asc')->paginate(10);
        $categories = ProjectCategory::orderBy('nama_kategori')->get();
        $departements = Departemen::orderBy('nama_dept')->get();
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        return view('project.index', compact('projects', 'categories', 'departements', 'cabangs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $categories = ProjectCategory::orderBy('nama_kategori')->get();
        $departements = Departemen::orderBy('nama_dept')->get();
        $cabangs = Cabang::orderBy('nama_cabang')->get();
        
        // Fetch active employees for selection
        $karyawan = Karyawan::where('status_aktif_karyawan', 1)->orderBy('nama_karyawan')->get();

        if ($request->ajax()) {
            return view('project.form', compact('categories', 'departements', 'cabangs', 'karyawan'));
        }

        return view('project.create', compact('categories', 'departements', 'cabangs', 'karyawan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_project' => 'required|string|max:255',
            'category_id' => 'required|exists:project_categories,id',
            'kode_dept' => 'nullable|exists:departemen,kode_dept',
            'kode_cabang' => 'nullable|exists:cabang,kode_cabang',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:planning,in_progress,completed,on_hold,cancelled',
            'prioritas' => 'required|in:low,medium,high,critical',
            'budget' => 'nullable',
            'catatan' => 'nullable|string',
            'leader_nik' => 'required|exists:karyawan,nik',
            'members' => 'nullable|array',
            'members.*' => 'exists:karyawan,nik',
        ]);

        DB::beginTransaction();
        try {
            // Get NIK of current user
            $user = Auth::user();
            $creatorNik = DB::table('users_karyawan')->where('id_user', $user->id)->value('nik');
            if (empty($creatorNik)) {
                // If it is admin without NIK link, fallback to leader or default
                $creatorNik = $request->leader_nik;
            }

            // Generate project code: PRJ-YYMM-XXXX using buatkode helper
            $prefix = 'PRJ-' . date('ym') . '-';
            $lastProject = Project::where('kode_project', 'like', $prefix . '%')
                ->orderBy('kode_project', 'desc')
                ->first();
            $lastCode = $lastProject ? $lastProject->kode_project : null;
            $kodeProject = buatkode($lastCode, $prefix, 4);

            $project = Project::create([
                'kode_project' => $kodeProject,
                'nama_project' => $request->nama_project,
                'deskripsi' => $request->deskripsi,
                'category_id' => $request->category_id,
                'kode_dept' => $request->kode_dept,
                'kode_cabang' => $request->kode_cabang,
                'created_by' => $creatorNik,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'prioritas' => $request->prioritas,
                'budget' => $request->budget ? toNumber($request->budget) : null,
                'catatan' => $request->catatan,
                'progress' => 0,
            ]);

            // Add leader as member
            ProjectMember::create([
                'project_id' => $project->id,
                'nik' => $request->leader_nik,
                'role' => 'leader',
                'joined_at' => now(),
            ]);

            // Add other members
            if ($request->filled('members')) {
                foreach ($request->members as $memberNik) {
                    if ($memberNik !== $request->leader_nik) {
                        ProjectMember::create([
                            'project_id' => $project->id,
                            'nik' => $memberNik,
                            'role' => 'member',
                            'joined_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return Redirect::route('project.index')->with(messageSuccess('Project berhasil dibuat.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withInput()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $project = Project::with([
                'category',
                'creator',
                'departemen',
                'cabang',
                'members.karyawan.jabatan',
                'tasks' => function($q) {
                    $q->whereNull('parent_id')->orderBy('urutan')->with(['members.karyawan', 'subtasks']);
                }
            ])->findOrFail($id);

            return view('project.show', compact('project'));
        } catch (\Exception $e) {
            return Redirect::route('project.index')->with(messageError($e->getMessage()));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
            $project = Project::with('members')->findOrFail($id);
            $categories = ProjectCategory::orderBy('nama_kategori')->get();
            $departements = Departemen::orderBy('nama_dept')->get();
            $cabangs = Cabang::orderBy('nama_cabang')->get();
            $karyawan = Karyawan::where('status_aktif_karyawan', 1)->orderBy('nama_karyawan')->get();

            // Find current leader
            $leader = $project->members->where('role', 'leader')->first();
            $leaderNik = $leader ? $leader->nik : '';

            // Find other members
            $memberNiks = $project->members->where('role', 'member')->pluck('nik')->toArray();

            if ($request->ajax()) {
                return view('project.edit_form', compact('project', 'categories', 'departements', 'cabangs', 'karyawan', 'leaderNik', 'memberNiks'));
            }

            return view('project.edit', compact('project', 'categories', 'departements', 'cabangs', 'karyawan', 'leaderNik', 'memberNiks'));
        } catch (\Exception $e) {
            return Redirect::route('project.index')->with(messageError($e->getMessage()));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
            $project = Project::findOrFail($id);
        } catch (\Exception $e) {
            return Redirect::route('project.index')->with(messageError('Project tidak ditemukan.'));
        }

        $request->validate([
            'nama_project' => 'required|string|max:255',
            'category_id' => 'required|exists:project_categories,id',
            'kode_dept' => 'nullable|exists:departemen,kode_dept',
            'kode_cabang' => 'nullable|exists:cabang,kode_cabang',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:planning,in_progress,completed,on_hold,cancelled',
            'prioritas' => 'required|in:low,medium,high,critical',
            'budget' => 'nullable',
            'catatan' => 'nullable|string',
            'leader_nik' => 'required|exists:karyawan,nik',
            'members' => 'nullable|array',
            'members.*' => 'exists:karyawan,nik',
        ]);

        DB::beginTransaction();
        try {
            $completedAt = null;
            if ($request->status === 'completed' && $project->status !== 'completed') {
                $completedAt = now();
            } elseif ($request->status === 'completed') {
                $completedAt = $project->completed_at;
            }

            $project->update([
                'nama_project' => $request->nama_project,
                'deskripsi' => $request->deskripsi,
                'category_id' => $request->category_id,
                'kode_dept' => $request->kode_dept,
                'kode_cabang' => $request->kode_cabang,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'prioritas' => $request->prioritas,
                'budget' => $request->budget ? toNumber($request->budget) : null,
                'catatan' => $request->catatan,
                'completed_at' => $completedAt,
            ]);

            // Sync members: Delete all existing first, then add back.
            ProjectMember::where('project_id', $project->id)->delete();

            // Add leader
            ProjectMember::create([
                'project_id' => $project->id,
                'nik' => $request->leader_nik,
                'role' => 'leader',
                'joined_at' => now(),
            ]);

            // Add other members
            if ($request->filled('members')) {
                foreach ($request->members as $memberNik) {
                    if ($memberNik !== $request->leader_nik) {
                        ProjectMember::create([
                            'project_id' => $project->id,
                            'nik' => $memberNik,
                            'role' => 'member',
                            'joined_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return Redirect::route('project.show', Crypt::encrypt($project->id))->with(messageSuccess('Project berhasil diupdate.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withInput()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $project = Project::findOrFail($id);
            $project->delete(); // Soft delete

            return Redirect::route('project.index')->with(messageSuccess('Project berhasil dihapus.'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Add single member to project
     */
    public function addMember(Request $request, $id)
    {
        $request->validate([
            'nik' => 'required|exists:karyawan,nik',
            'role' => 'required|in:leader,member',
        ]);

        try {
            $id = Crypt::decrypt($id);
            
            // Check if member already exists
            $exists = ProjectMember::where('project_id', $id)->where('nik', $request->nik)->exists();
            if ($exists) {
                return Redirect::back()->with(messageError('Karyawan sudah tergabung dalam project ini.'));
            }

            // If new leader is assigned, demote old leader to member
            if ($request->role === 'leader') {
                ProjectMember::where('project_id', $id)->where('role', 'leader')->update(['role' => 'member']);
            }

            ProjectMember::create([
                'project_id' => $id,
                'nik' => $request->nik,
                'role' => $request->role,
                'joined_at' => now(),
            ]);

            return Redirect::back()->with(messageSuccess('Anggota baru berhasil ditambahkan.'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Remove single member from project
     */
    public function removeMember($id, $nik)
    {
        try {
            $id = Crypt::decrypt($id);
            
            // Check if we are deleting the leader
            $member = ProjectMember::where('project_id', $id)->where('nik', $nik)->first();
            if ($member && $member->role === 'leader') {
                return Redirect::back()->with(messageError('Pemimpin project (leader) tidak dapat dihapus. Silakan ganti leader terlebih dahulu.'));
            }

            ProjectMember::where('project_id', $id)->where('nik', $nik)->delete();
            return Redirect::back()->with(messageSuccess('Anggota berhasil dihapus dari project.'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }
}
