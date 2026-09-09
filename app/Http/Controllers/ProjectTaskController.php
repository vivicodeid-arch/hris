<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskMember;
use App\Models\ProjectTaskComment;
use App\Models\ProjectTaskAttachment;
use App\Models\ProjectTaskLog;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class ProjectTaskController extends Controller
{
    public function create(Request $request, $projectId)
    {
        try {
            $projectIdDec = Crypt::decrypt($projectId);
            $project = Project::with('members.karyawan')->findOrFail($projectIdDec);
            
            // Tasks that can be parents (only root tasks, parent_id is null)
            $parentTasks = ProjectTask::where('project_id', $project->id)
                ->whereNull('parent_id')
                ->orderBy('judul')
                ->get();

            if ($request->ajax()) {
                $parentId = $request->input('parent_id');
                return view('project.task.create_form', compact('project', 'parentTasks', 'projectId', 'parentId'));
            }

            return view('project.task.create', compact('project', 'parentTasks', 'projectId'));
        } catch (\Exception $e) {
            return Redirect::route('project.index')->with(messageError($e->getMessage()));
        }
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request, $projectId)
    {
        try {
            $projectIdDec = Crypt::decrypt($projectId);
            $project = Project::findOrFail($projectIdDec);
        } catch (\Exception $e) {
            return Redirect::route('project.index')->with(messageError('Project tidak ditemukan.'));
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'parent_id' => 'nullable|exists:project_tasks,id',
            'status' => 'required|in:todo,in_progress,review,completed,cancelled',
            'prioritas' => 'required|in:low,medium,high,critical',
            'progress' => 'required|integer|min:0|max:100',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'urutan' => 'nullable|integer',
            'members' => 'nullable|array',
            'members.*' => 'exists:karyawan,nik',
        ]);

        DB::beginTransaction();
        try {
            // Get current NIK of creator
            $user = Auth::user();
            $creatorNik = DB::table('users_karyawan')->where('id_user', $user->id)->value('nik');
            if (empty($creatorNik)) {
                // If it is admin without NIK link, use project creator or fallback
                $creatorNik = $project->created_by;
            }

            // Generate task code: TSK-YYMM-XXXX using buatkode helper
            $prefix = 'TSK-' . date('ym') . '-';
            $lastTask = ProjectTask::where('kode_task', 'like', $prefix . '%')
                ->orderBy('kode_task', 'desc')
                ->first();
            $lastCode = $lastTask ? $lastTask->kode_task : null;
            $kodeTask = buatkode($lastCode, $prefix, 4);

            $completedAt = null;
            $progress = $request->progress;
            if ($request->status === 'completed') {
                $completedAt = now();
                $progress = 100;
            }

            $task = ProjectTask::create([
                'project_id' => $project->id,
                'parent_id' => $request->parent_id,
                'kode_task' => $kodeTask,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'status' => $request->status,
                'prioritas' => $request->prioritas,
                'progress' => $progress,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'completed_at' => $completedAt,
                'urutan' => $request->urutan ?? 0,
                'created_by' => $creatorNik,
            ]);

            // Assign members
            if ($request->filled('members')) {
                foreach ($request->members as $memberNik) {
                    ProjectTaskMember::create([
                        'task_id' => $task->id,
                        'nik' => $memberNik,
                        'assigned_at' => now(),
                    ]);
                }
            }

            // Log activity
            ProjectTaskLog::create([
                'task_id' => $task->id,
                'nik' => $creatorNik,
                'aksi' => 'created',
                'data_baru' => $task->toArray(),
                'keterangan' => 'Task "' . $task->judul . '" berhasil dibuat.',
            ]);

            // Recalculate Project overall progress
            $project->calculateProgress();

            DB::commit();
            return Redirect::route('project.show', $projectId)->with(messageSuccess('Task berhasil dibuat.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withInput()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Display the task details.
     */
    public function show($id)
    {
        try {
            $idDec = Crypt::decrypt($id);
            $task = ProjectTask::with([
                'project',
                'parent',
                'creator',
                'subtasks.members.karyawan',
                'members.karyawan.jabatan',
                'comments.karyawan',
                'attachments.karyawan',
                'logs.karyawan'
            ])->findOrFail($idDec);

            // Get available members from the parent project to allow new assignments
            $project = Project::with('members.karyawan')->findOrFail($task->project_id);

            return view('project.task.show', compact('task', 'project'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Show the form for editing a task.
     */
    public function edit(Request $request, $id)
    {
        try {
            $idDec = Crypt::decrypt($id);
            $task = ProjectTask::with('members')->findOrFail($idDec);
            $project = Project::with('members.karyawan')->findOrFail($task->project_id);
            
            $parentTasks = ProjectTask::where('project_id', $task->project_id)
                ->whereNull('parent_id')
                ->where('id', '!=', $task->id) // cannot be own parent
                ->orderBy('judul')
                ->get();

            $assignedNiks = $task->members->pluck('nik')->toArray();

            if ($request->ajax()) {
                return view('project.task.edit_form', compact('task', 'project', 'parentTasks', 'assignedNiks'));
            }

            return view('project.task.edit', compact('task', 'project', 'parentTasks', 'assignedNiks'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Update the task.
     */
    public function update(Request $request, $id)
    {
        try {
            $idDec = Crypt::decrypt($id);
            $task = ProjectTask::findOrFail($idDec);
            $project = Project::findOrFail($task->project_id);
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Task tidak ditemukan.'));
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'parent_id' => 'nullable|exists:project_tasks,id',
            'status' => 'required|in:todo,in_progress,review,completed,cancelled',
            'prioritas' => 'required|in:low,medium,high,critical',
            'progress' => 'required|integer|min:0|max:100',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'urutan' => 'nullable|integer',
            'members' => 'nullable|array',
            'members.*' => 'exists:karyawan,nik',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $executorNik = DB::table('users_karyawan')->where('id_user', $user->id)->value('nik');

            $oldData = $task->toArray();

            $completedAt = $task->completed_at;
            $progress = $request->progress;
            if ($request->status === 'completed' && $task->status !== 'completed') {
                $completedAt = now();
                $progress = 100;
            } elseif ($request->status !== 'completed') {
                $completedAt = null;
            }

            $task->update([
                'parent_id' => $request->parent_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'status' => $request->status,
                'prioritas' => $request->prioritas,
                'progress' => $progress,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'completed_at' => $completedAt,
                'urutan' => $request->urutan ?? 0,
            ]);

            // Sync members
            ProjectTaskMember::where('task_id', $task->id)->delete();
            if ($request->filled('members')) {
                foreach ($request->members as $memberNik) {
                    ProjectTaskMember::create([
                        'task_id' => $task->id,
                        'nik' => $memberNik,
                        'assigned_at' => now(),
                    ]);
                }
            }

            // Log activity
            ProjectTaskLog::create([
                'task_id' => $task->id,
                'nik' => $executorNik,
                'aksi' => 'updated',
                'data_lama' => $oldData,
                'data_baru' => $task->toArray(),
                'keterangan' => 'Task "' . $task->judul . '" berhasil diperbarui.',
            ]);

            // Recalculate Project progress
            $project->calculateProgress();

            DB::commit();
            return Redirect::route('project.task.show', Crypt::encrypt($task->id))->with(messageSuccess('Task berhasil diperbarui.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->withInput()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Delete the task.
     */
    public function destroy($id)
    {
        try {
            $idDec = Crypt::decrypt($id);
            $task = ProjectTask::findOrFail($idDec);
            $project = Project::findOrFail($task->project_id);
            
            DB::beginTransaction();
            $task->delete(); // Cascades on DB schema side or manual clean if soft deletes (not using soft delete on tasks)
            
            // Recalculate Project progress
            $project->calculateProgress();

            DB::commit();
            return Redirect::route('project.show', Crypt::encrypt($project->id))->with(messageSuccess('Task berhasil dihapus.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Update progress of a task.
     */
    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'status' => 'nullable|string|in:todo,in_progress,review,completed,cancelled',
        ]);

        try {
            $idDec = Crypt::decrypt($id);
            $task = ProjectTask::findOrFail($idDec);
            $project = Project::findOrFail($task->project_id);

            $user = Auth::user();
            $executorNik = DB::table('users_karyawan')->where('id_user', $user->id)->value('nik');

            $oldProgress = $task->progress;
            $oldStatus = $task->status;

            $status = $request->status ?? $task->status;
            $completedAt = $task->completed_at;

            // Handle completed logic
            if ($status === 'completed') {
                $request->merge(['progress' => 100]);
                $completedAt = now();
            }

            if ($request->progress == 100) {
                $status = 'completed';
                $completedAt = now();
            } elseif ($task->status === 'completed' && $request->progress < 100 && !$request->has('status')) {
                $status = 'in_progress';
                $completedAt = null;
            }

            $task->update([
                'progress' => $request->progress,
                'status' => $status,
                'completed_at' => $completedAt,
            ]);

            // Log activity
            ProjectTaskLog::create([
                'task_id' => $task->id,
                'nik' => $executorNik,
                'aksi' => 'progress_updated',
                'data_lama' => ['progress' => $oldProgress, 'status' => $oldStatus],
                'data_baru' => ['progress' => $request->progress, 'status' => $status],
                'keterangan' => 'Progress diperbarui dari ' . $oldProgress . '% menjadi ' . $request->progress . '% (' . str_replace('_', ' ', $status) . ').',
            ]);

            $project->calculateProgress();

            return Redirect::back()->with(messageSuccess('Progress pengerjaan berhasil diperbarui.'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Update status of a task.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,completed,cancelled',
        ]);

        try {
            $idDec = Crypt::decrypt($id);
            $task = ProjectTask::findOrFail($idDec);
            $project = Project::findOrFail($task->project_id);

            $user = Auth::user();
            $executorNik = DB::table('users_karyawan')->where('id_user', $user->id)->value('nik');

            $oldStatus = $task->status;
            $progress = $task->progress;
            $completedAt = $task->completed_at;

            if ($request->status === 'completed') {
                $progress = 100;
                $completedAt = now();
            } elseif ($request->status !== 'completed' && $task->status === 'completed') {
                $completedAt = null;
            }

            $task->update([
                'status' => $request->status,
                'progress' => $progress,
                'completed_at' => $completedAt,
            ]);

            // Log activity
            ProjectTaskLog::create([
                'task_id' => $task->id,
                'nik' => $executorNik,
                'aksi' => 'status_changed',
                'data_lama' => ['status' => $oldStatus],
                'data_baru' => ['status' => $request->status, 'progress' => $progress],
                'keterangan' => 'Status pengerjaan diubah dari "' . strtoupper($oldStatus) . '" menjadi "' . strtoupper($request->status) . '".',
            ]);

            $project->calculateProgress();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status task berhasil diperbarui.',
                ]);
            }

            return Redirect::back()->with(messageSuccess('Status task berhasil diperbarui.'));
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            }
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Store a comment on the task.
     */
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'komentar' => 'required|string',
        ]);

        try {
            $idDec = Crypt::decrypt($id);
            $task = ProjectTask::findOrFail($idDec);

            $user = Auth::user();
            $executorNik = DB::table('users_karyawan')->where('id_user', $user->id)->value('nik');

            ProjectTaskComment::create([
                'task_id' => $task->id,
                'nik' => $executorNik,
                'komentar' => $request->komentar,
            ]);

            // Log activity
            ProjectTaskLog::create([
                'task_id' => $task->id,
                'nik' => $executorNik,
                'aksi' => 'comment_added',
                'keterangan' => 'Menambahkan komentar baru.',
            ]);

            return Redirect::back()->with(messageSuccess('Komentar berhasil ditambahkan.'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Upload an attachment.
     */
    public function storeAttachment(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        try {
            $idDec = Crypt::decrypt($id);
            $task = ProjectTask::findOrFail($idDec);

            $user = Auth::user();
            $executorNik = DB::table('users_karyawan')->where('id_user', $user->id)->value('nik');

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . preg_replace('/\s+/', '_', $originalName);
                
                // Store in public project-attachments folder
                $path = $file->storeAs('public/project-attachments/' . $task->project_id . '/' . $task->id, $fileName);
                $dbPath = 'project-attachments/' . $task->project_id . '/' . $task->id . '/' . $fileName;

                ProjectTaskAttachment::create([
                    'task_id' => $task->id,
                    'nik' => $executorNik,
                    'nama_file' => $originalName,
                    'path' => $dbPath,
                    'tipe_file' => $file->getMimeType(),
                    'ukuran' => $file->getSize(),
                ]);

                // Log activity
                ProjectTaskLog::create([
                    'task_id' => $task->id,
                    'nik' => $executorNik,
                    'aksi' => 'attachment_added',
                    'keterangan' => 'Mengunggah file lampiran: ' . $originalName,
                ]);

                return Redirect::back()->with(messageSuccess('File lampiran berhasil diunggah.'));
            }

            return Redirect::back()->with(messageError('File gagal diunggah.'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment($id)
    {
        try {
            $idDec = Crypt::decrypt($id);
            $attachment = ProjectTaskAttachment::findOrFail($idDec);
            $task = ProjectTask::findOrFail($attachment->task_id);

            $user = Auth::user();
            $executorNik = DB::table('users_karyawan')->where('id_user', $user->id)->value('nik');

            // Delete physical file
            Storage::delete('public/' . $attachment->path);
            
            // Log activity before deleting record
            ProjectTaskLog::create([
                'task_id' => $task->id,
                'nik' => $executorNik,
                'aksi' => 'attachment_removed',
                'keterangan' => 'Menghapus file lampiran: ' . $attachment->nama_file,
            ]);

            $attachment->delete();

            return Redirect::back()->with(messageSuccess('File lampiran berhasil dihapus.'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }
}
