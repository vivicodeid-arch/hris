<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    use HasFactory;

    protected $table = 'project_tasks';

    protected $fillable = [
        'project_id',
        'parent_id',
        'kode_task',
        'judul',
        'deskripsi',
        'status',
        'prioritas',
        'progress',
        'start_date',
        'due_date',
        'completed_at',
        'urutan',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'progress' => 'integer',
        'urutan' => 'integer',
    ];

    /**
     * Get the project that owns the task.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the parent task if this is a sub-task.
     */
    public function parent()
    {
        return $this->belongsTo(ProjectTask::class, 'parent_id');
    }

    /**
     * Get the sub-tasks for this task.
     */
    public function subtasks()
    {
        return $this->hasMany(ProjectTask::class, 'parent_id')->orderBy('urutan');
    }

    /**
     * Get the members assigned to this task.
     */
    public function members()
    {
        return $this->hasMany(ProjectTaskMember::class, 'task_id');
    }

    /**
     * Get the comments on this task.
     */
    public function comments()
    {
        return $this->hasMany(ProjectTaskComment::class, 'task_id');
    }

    /**
     * Get the attachments of this task.
     */
    public function attachments()
    {
        return $this->hasMany(ProjectTaskAttachment::class, 'task_id');
    }

    /**
     * Get the logs for this task.
     */
    public function logs()
    {
        return $this->hasMany(ProjectTaskLog::class, 'task_id');
    }

    /**
     * Get the creator of the task.
     */
    public function creator()
    {
        return $this->belongsTo(Karyawan::class, 'created_by', 'nik');
    }
}
