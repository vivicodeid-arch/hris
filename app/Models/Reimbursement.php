<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model
{
    use HasFactory;

    protected $table = 'reimbursement';
    protected $guarded = ['id'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function details()
    {
        return $this->hasMany(ReimbursementDetail::class, 'reimbursement_id', 'id');
    }

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function getStatusLabelAttribute()
    {
        if ($this->status == 'P') {
            return '<span class="badge bg-warning">Pending</span>';
        } elseif ($this->status == 'A') {
            return '<span class="badge bg-success">Approved</span>';
        } elseif ($this->status == 'R') {
            return '<span class="badge bg-danger">Rejected</span>';
        } elseif ($this->status == 'D') {
            return '<span class="badge bg-primary">Dibayar</span>';
        } elseif ($this->status == 'B') {
            return '<span class="badge bg-secondary">Batal</span>';
        }
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
