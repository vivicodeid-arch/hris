<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $table = 'approvals';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'user_id',
        'level',
        'status',
        'keterangan',
    ];

    /**
     * Get the parent approvable model (Izinabsen, Cuti, etc).
     */
    public function approvable()
    {
        return $this->morphTo();
    }

    protected static function booted()
    {
        static::created(function ($approval) {
            if ($approval->status === 'approved') {
                try {
                    $requestObj = $approval->approvable;
                    if ($requestObj && isset($requestObj->nik)) {
                        // 1. Notify the applicant
                        $userKaryawan = \App\Models\Userkaryawan::where('nik', $requestObj->nik)->first();
                        if ($userKaryawan && $userKaryawan->user) {
                            $userKaryawan->user->notify(new \App\Notifications\ApprovalStatusNotification($approval));
                        }

                        // 2. Notify the next level approvers/delegates
                        $nextLevel = $approval->level + 1;
                        $karyawan = \App\Models\Karyawan::find($requestObj->nik);
                        if ($karyawan) {
                            $approvalService = app(\App\Services\ApprovalService::class);
                            $feature = null;
                            $class = get_class($requestObj);
                            if (in_array($class, [\App\Models\Izinabsen::class, \App\Models\Izinsakit::class, \App\Models\Izincuti::class, \App\Models\Izindinas::class])) {
                                $feature = 'IZIN';
                            } elseif ($class === \App\Models\Reimbursement::class) {
                                $feature = 'REIMBURSEMENT';
                            } elseif ($class === \App\Models\Koreksi::class) {
                                $feature = 'KOREKSI';
                            }

                            if ($feature) {
                                $nextRule = $approvalService->getLayer($feature, $nextLevel, $karyawan->kode_dept, $karyawan->kode_jabatan, $karyawan->kode_cabang);
                                if ($nextRule) {
                                    \App\Services\ApprovalNotificationService::sendNewRequestNotification($requestObj, $nextLevel);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error sending approval status notification: ' . $e->getMessage());
                }
            }
        });
    }
}
