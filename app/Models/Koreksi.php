<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Koreksi extends Model
{
    use HasFactory;
    protected $table = 'presensi_koreksi';
    protected $guarded = [];
    protected $primaryKey = 'kode_koreksi';
    public $incrementing = false;

    public function getNextApprovalLayer()
    {
        // Feature Code untuk model ini adalah 'KOREKSI'
        $nextLevel = $this->approval_step;
        
        $kode_dept = $this->kode_dept ?? null;

        $layer = ApprovalLayer::where('feature', 'KOREKSI')
            ->where('level', $nextLevel)
            ->where(function ($q) use ($kode_dept) {
                $q->where('kode_dept', $kode_dept)
                  ->orWhereNull('kode_dept');
            })
            ->first();

        return $layer;
    }

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    protected static function booted()
    {
        static::created(function ($model) {
            \App\Services\ApprovalNotificationService::sendNewRequestNotification($model, $model->approval_step ?? 1);
        });

        static::updated(function ($model) {
            if ($model->isDirty('approval_step') && $model->approval_step > $model->getOriginal('approval_step')) {
                \App\Services\ApprovalNotificationService::sendNewRequestNotification($model, $model->approval_step);
            }
        });
    }
}
