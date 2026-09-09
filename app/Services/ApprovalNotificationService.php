<?php

namespace App\Services;

use App\Models\ApprovalLayer;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\Userkaryawan;
use App\Notifications\NewApprovalRequestNotification;
use Illuminate\Support\Facades\Log;

class ApprovalNotificationService
{
    /**
     * Send new approval request notifications to the active role users and their delegates.
     */
    public static function sendNewRequestNotification($requestObj, $level)
    {
        try {
            $feature = self::getFeatureCode($requestObj);
            if (!$feature) return;

            $karyawan = Karyawan::find($requestObj->nik);
            if (!$karyawan) return;

            $kodeDept = $karyawan->kode_dept;
            $kodeJabatan = $karyawan->kode_jabatan;
            $kodeCabang = $karyawan->kode_cabang;

            $approvalService = app(ApprovalService::class);
            $layer = $approvalService->getLayer($feature, $level, $kodeDept, $kodeJabatan, $kodeCabang);

            if (!$layer || !$layer->role_name) {
                return;
            }

            // 1. Get all users who have the role
            $admins = User::role($layer->role_name)->get();

            // 2. Get all delegate users associated with these admins
            $delegates = Userkaryawan::whereIn('approval_admin_id', $admins->pluck('id'))
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();

            // 3. Combine both and de-duplicate
            $recipients = $admins->concat($delegates)->unique('id');

            $featureName = self::getFeatureName($requestObj);

            foreach ($recipients as $recipient) {
                // Don't notify the applicant themselves
                $recipientKaryawan = Userkaryawan::where('id_user', $recipient->id)->first();
                if ($recipientKaryawan && $recipientKaryawan->nik === $requestObj->nik) {
                    continue;
                }

                $recipient->notify(new NewApprovalRequestNotification(
                    $requestObj,
                    $featureName,
                    $karyawan->nama_karyawan,
                    $level
                ));
            }
        } catch (\Exception $e) {
            Log::error('Error sending new approval request notification: ' . $e->getMessage());
        }
    }

    private static function getFeatureCode($requestObj): ?string
    {
        $class = get_class($requestObj);
        switch ($class) {
            case \App\Models\Izinabsen::class:
            case \App\Models\Izinsakit::class:
            case \App\Models\Izincuti::class:
            case \App\Models\Izindinas::class:
                return 'IZIN';
            case \App\Models\Reimbursement::class:
                return 'REIMBURSEMENT';
            case \App\Models\Koreksi::class:
                return 'KOREKSI';
            default:
                return null;
        }
    }

    private static function getFeatureName($requestObj): string
    {
        $class = get_class($requestObj);
        switch ($class) {
            case \App\Models\Izinabsen::class:
                return 'Izin Absen';
            case \App\Models\Izinsakit::class:
                return 'Izin Sakit';
            case \App\Models\Izincuti::class:
                return 'Izin Cuti';
            case \App\Models\Izindinas::class:
                return 'Izin Dinas';
            case \App\Models\Reimbursement::class:
                return 'Reimbursement';
            case \App\Models\Koreksi::class:
                return 'Koreksi Absen';
            default:
                $classParts = explode('\\', $class);
                return end($classParts);
        }
    }
}
