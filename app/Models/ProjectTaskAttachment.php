<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskAttachment extends Model
{
    use HasFactory;

    protected $table = 'project_task_attachments';

    protected $fillable = [
        'task_id',
        'nik',
        'nama_file',
        'path',
        'tipe_file',
        'ukuran',
    ];

    protected $casts = [
        'ukuran' => 'integer',
    ];

    /**
     * Get the task.
     */
    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    /**
     * Get the employee who uploaded the attachment.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
