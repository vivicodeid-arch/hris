<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewApprovalRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $requestObj;
    private $featureName;
    private $karyawanName;
    private $level;

    /**
     * Create a new notification instance.
     */
    public function __construct($requestObj, $featureName, $karyawanName, $level)
    {
        $this->requestObj = $requestObj;
        $this->featureName = $featureName;
        $this->karyawanName = $karyawanName;
        $this->level = $level;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->hasRole('karyawan') && method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ajuan Baru Menunggu Approval 🔔',
            'message' => 'Pengajuan ' . $this->featureName . ' baru dari ' . $this->karyawanName . ' memerlukan persetujuan Anda (Tahap ' . $this->level . ').',
            'url' => route('karyawan-approval.index'),
            'type' => 'new_approval',
            'icon' => 'ti-file'
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

        return (new WebPushMessage)
            ->title('Ajuan Baru Menunggu Approval 🔔')
            ->body('Pengajuan ' . $this->featureName . ' baru dari ' . $this->karyawanName . ' memerlukan persetujuan Anda (Tahap ' . $this->level . ').')
            ->icon($icon)
            ->badge(asset('assets/img/icon-96x96.png'))
            ->data([
                'action_url' => route('karyawan-approval.index')
            ]);
    }
}
