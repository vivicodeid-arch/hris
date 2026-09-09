<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Get NIK of authenticated employee.
     */
    private function getNik($userId)
    {
        return DB::table('users_karyawan')->where('id_user', $userId)->value('nik');
    }

    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $nik = $this->getNik($user->id);

        if (empty($nik)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan data Karyawan.'
            ], 404);
        }

        // Get projects where employee is a member
        $projects = Project::whereHas('members', function($q) use ($nik) {
            $q->where('nik', $nik);
        })
        ->with(['category', 'members.karyawan'])
        ->orderBy('end_date', 'asc')
        ->get();

        $data = $projects->map(function ($project) use ($nik) {
            // Count tasks assigned to this employee in this project that are not completed
            $pendingTasksCount = ProjectTask::where('project_id', $project->id)
                ->where('status', '!=', 'completed')
                ->whereHas('members', function($q) use ($nik) {
                    $q->where('nik', $nik);
                })
                ->count();

            $leader = $project->members->where('role', 'leader')->first();
            $leaderName = $leader && $leader->karyawan ? ($leader->karyawan->nama_lengkap ?? $leader->karyawan->nama_karyawan) : 'Belum ditentukan';
            $categoryColor = $project->category && $project->category->warna ? $project->category->warna : '#64748b';

            return [
                'id' => $project->id,
                'kode_project' => $project->kode_project,
                'nama_project' => $project->nama_project,
                'deskripsi' => $project->deskripsi ?? '',
                'category' => $project->category ? $project->category->nama_kategori : 'Uncategorized',
                'category_color' => $categoryColor,
                'start_date' => $project->start_date ? $project->start_date->format('Y-m-d') : '',
                'end_date' => $project->end_date ? $project->end_date->format('Y-m-d') : '',
                'status' => $project->status ?? 'active',
                'prioritas' => $project->prioritas ?? 'medium',
                'progress' => (int)($project->progress ?? 0),
                'members_count' => $project->members->count(),
                'pending_tasks_count' => $pendingTasksCount,
                'leader_name' => $leaderName
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Display the specified project with tasks and members.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $nik = $this->getNik($user->id);

        if (empty($nik)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan data Karyawan.'
            ], 404);
        }

        $project = Project::with(['category', 'members.karyawan'])->find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project tidak ditemukan.'
            ], 404);
        }

        // Verify membership
        $isMember = DB::table('project_members')->where('project_id', $project->id)->where('nik', $nik)->exists();
        if (!$isMember) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke project ini.'
            ], 403);
        }

        // Get all tasks for this project
        $tasks = ProjectTask::where('project_id', $project->id)
            ->with(['members.karyawan'])
            ->orderBy('urutan', 'asc')
            ->orderBy('due_date', 'asc')
            ->get();

        $formattedTasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'parent_id' => $task->parent_id,
                'kode_task' => $task->kode_task,
                'judul' => $task->judul,
                'deskripsi' => $task->deskripsi ?? '',
                'status' => $task->status ?? 'todo',
                'prioritas' => $task->prioritas ?? 'medium',
                'progress' => (int)($task->progress ?? 0),
                'start_date' => $task->start_date ? $task->start_date->format('Y-m-d') : '',
                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : '',
                'members' => $task->members->map(function ($m) {
                    return [
                        'nik' => $m->nik,
                        'nama' => $m->karyawan ? $m->karyawan->nama_lengkap : 'Karyawan',
                        'foto' => $m->karyawan && $m->karyawan->foto ? asset('storage/uploads/karyawan/' . $m->karyawan->foto) : ''
                    ];
                })
            ];
        });

        $formattedMembers = $project->members->map(function ($member) {
            return [
                'nik' => $member->nik,
                'nama' => $member->karyawan ? $member->karyawan->nama_lengkap : 'Karyawan',
                'role' => $member->role ?? 'Member',
                'foto' => $member->karyawan && $member->karyawan->foto ? asset('storage/uploads/karyawan/' . $member->karyawan->foto) : ''
            ];
        });

        $leader = $project->members->where('role', 'leader')->first();
        $leaderName = $leader && $leader->karyawan ? ($leader->karyawan->nama_lengkap ?? $leader->karyawan->nama_karyawan) : 'Belum ditentukan';
        $categoryColor = $project->category && $project->category->warna ? $project->category->warna : '#64748b';

        return response()->json([
            'success' => true,
            'data' => [
                'project' => [
                    'id' => $project->id,
                    'kode_project' => $project->kode_project,
                    'nama_project' => $project->nama_project,
                    'deskripsi' => $project->deskripsi ?? '',
                    'category' => $project->category ? $project->category->nama_kategori : 'Uncategorized',
                    'category_color' => $categoryColor,
                    'start_date' => $project->start_date ? $project->start_date->format('Y-m-d') : '',
                    'end_date' => $project->end_date ? $project->end_date->format('Y-m-d') : '',
                    'status' => $project->status ?? 'active',
                    'prioritas' => $project->prioritas ?? 'medium',
                    'progress' => (int)($project->progress ?? 0),
                    'leader_name' => $leaderName,
                    'budget' => $project->budget ? (double)$project->budget : 0.0
                ],
                'tasks' => $formattedTasks,
                'members' => $formattedMembers
            ]
        ]);
    }

    /**
     * Update task status from mobile board.
     */
    public function updateTaskStatus(Request $request, $taskId)
    {
        $user = $request->user();
        $nik = $this->getNik($user->id);

        if (empty($nik)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak terhubung dengan data Karyawan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:todo,in_progress,review,completed,cancelled',
            'progress' => 'nullable|integer|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $task = ProjectTask::find($taskId);
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task tidak ditemukan.'
            ], 404);
        }

        // Verify that the user is a member of the task's project
        $isMember = DB::table('project_members')->where('project_id', $task->project_id)->where('nik', $nik)->exists();
        if (!$isMember) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke project ini.'
            ], 403);
        }

        // Verify that the user is assigned to this specific task
        $isAssigned = DB::table('project_task_members')->where('task_id', $task->id)->where('nik', $nik)->exists();
        if (!$isAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak ditugaskan pada task ini sehingga tidak dapat memperbarui progress.'
            ], 403);
        }

        $oldStatus = $task->status;
        $newStatus = $request->status;

        $updateData = ['status' => $newStatus];
        
        if ($newStatus === 'completed') {
            $updateData['progress'] = 100;
            $updateData['completed_at'] = now();
        } else {
            if ($request->has('progress')) {
                $updateData['progress'] = $request->progress;
            }
            if ($oldStatus === 'completed') {
                $updateData['completed_at'] = null;
            }
        }

        DB::beginTransaction();
        try {
            $task->update($updateData);

            // Recalculate project progress
            $project = Project::find($task->project_id);
            if ($project) {
                $project->calculateProgress();
            }

            // Log the change
            DB::table('project_task_logs')->insert([
                'task_id' => $task->id,
                'nik' => $nik,
                'aksi' => 'status_updated',
                'data_lama' => json_encode(['status' => $oldStatus]),
                'data_baru' => json_encode(['status' => $newStatus]),
                'keterangan' => "Mengubah status task dari '$oldStatus' menjadi '$newStatus'",
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status task berhasil diperbarui.',
                'data' => [
                    'task_id' => $task->id,
                    'status' => $task->status,
                    'progress' => $task->progress,
                    'project_progress' => $project ? $project->progress : 0
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status task: ' . $e->getMessage()
            ], 500);
        }
    }
}
