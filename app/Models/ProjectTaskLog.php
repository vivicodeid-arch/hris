<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskLog extends Model
{
    use HasFactory;

    protected $table = 'project_task_logs';

    // Disable updated_at automatically since the table only has created_at
    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'nik',
        'aksi',
        'data_lama',
        'data_baru',
        'keterangan',
        'created_at',
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the task.
     */
    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    /**
     * Get the employee who performed the action.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
