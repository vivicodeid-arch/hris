<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ApprovalStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $approval;

    /**
     * Create a new notification instance.
     */
    public function __construct($approval)
    {
        $this->approval = $approval;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Hanya kirim notifikasi push ke karyawan yang telah mengaktifkan push subscription
        if ($notifiable->hasRole('karyawan') && method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /**
     * Get the human-readable type of the approval.
     */
    private function getApproveTypeName(): string
    {
        $type = $this->approval->approvable_type;
        switch ($type) {
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
            case \App\Models\Lembur::class:
                return 'Lembur';
            case \App\Models\Koreksi::class:
                return 'Koreksi Absen';
            default:
                $classParts = explode('\\', $type);
                return end($classParts);
        }
    }

    /**
     * Get the role name of the user who approved it.
     */
    private function getApproverRoleName(): string
    {
        $approver = \App\Models\User::find($this->approval->user_id);
        if ($approver) {
            $roleName = $approver->getRoleNames()->first();
            if ($roleName) {
                return ucwords(str_replace('_', ' ', $roleName));
            }
        }
        return 'Admin';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $typeName = $this->getApproveTypeName();
        $roleName = $this->getApproverRoleName();
        
        return [
            'title' => 'Ajuan Disetujui 📄',
            'message' => 'Pengajuan ' . $typeName . ' Anda telah disetujui oleh ' . $roleName . '.',
            'url' => route('dashboard.index'),
            'type' => 'approval',
            'icon' => 'ti-check-box'
        ];
    }

    /**
     * Get the WebPush representation of the notification.
     */
    public function toWebPush(object $notifiable, object $notification): WebPushMessage
    {
        $icon = asset('assets/img/icon-192x192.png');
        $setting = \App\Models\Pengaturanumum::first();
        if ($setting && $setting->logo && \Illuminate\Support\Facades\Storage::exists('public/logo/' . $setting->logo)) {
            $icon = asset('storage/logo/' . $setting->logo);
        }

        $typeName = $this->getApproveTypeName();
        $roleName = $this->getApproverRoleName();

        return (new WebPushMessage)
            ->title('Ajuan Disetujui 📄')
            ->body('Pengajuan ' . $typeName . ' Anda telah disetujui oleh ' . $roleName . '.')
            ->icon($icon)
            ->badge(asset('assets/img/icon-96x96.png'))
            ->data([
                'action_url' => route('dashboard.index')
            ]);
    }
}
