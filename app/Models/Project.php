<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'kode_project',
        'nama_project',
        'deskripsi',
        'category_id',
        'kode_dept',
        'kode_cabang',
        'created_by',
        'start_date',
        'end_date',
        'status',
        'prioritas',
        'progress',
        'budget',
        'catatan',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'datetime',
        'progress' => 'integer',
        'budget' => 'decimal:2',
    ];

    /**
     * Get the category of the project.
     */
    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    /**
     * Get the members assigned to the project.
     */
    public function members()
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    /**
     * Get the tasks of the project.
     */
    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'project_id');
    }

    /**
     * Get the creator of the project.
     */
    public function creator()
    {
        return $this->belongsTo(Karyawan::class, 'created_by', 'nik');
    }

    /**
     * Get the department of the project.
     */
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'kode_dept', 'kode_dept');
    }

    /**
     * Get the branch of the project.
     */
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'kode_cabang', 'kode_cabang');
    }

    /**
     * Calculate and update the overall progress based on task progress.
     */
    public function calculateProgress(): int
    {
        // Only count parent tasks (exclude sub-tasks to avoid double counting if sub-tasks represent parent's progress)
        // Or if we want all tasks, we can do that. Let's average parent tasks first. If parent tasks are empty, average all tasks.
        $query = $this->tasks()->whereNull('parent_id');
        $totalTasks = $query->count();
        
        if ($totalTasks === 0) {
            // Fallback to all tasks if no parent tasks are explicitly marked
            $totalTasks = $this->tasks()->count();
            $query = $this->tasks();
        }

        if ($totalTasks === 0) {
            $newProgress = 0;
        } else {
            $sumProgress = $query->sum('progress');
            $newProgress = (int) round($sumProgress / $totalTasks);
        }

        $this->update(['progress' => $newProgress]);
        return $newProgress;
    }
}
