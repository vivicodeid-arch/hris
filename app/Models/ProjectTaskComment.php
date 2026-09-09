<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskComment extends Model
{
    use HasFactory;

    protected $table = 'project_task_comments';

    protected $fillable = [
        'task_id',
        'nik',
        'komentar',
    ];

    /**
     * Get the task.
     */
    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    /**
     * Get the employee who commented.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
