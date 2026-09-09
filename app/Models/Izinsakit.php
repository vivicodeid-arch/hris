<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ApprovalLayer;

class Izinsakit extends Model
{
    use HasFactory;
    protected $table = 'presensi_izinsakit';
    protected $guarded = [];
    protected $primaryKey = 'kode_izin_sakit';
    public $incrementing = false;
    
    public function getNextApprovalLayer()
    {
        // Asumsi Feature Code untuk model ini adalah 'IZIN'
        $nextLevel = $this->approval_step;
        
        $kode_dept = $this->kode_dept ?? null;

        $layer = ApprovalLayer::where('feature', 'IZIN')
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
