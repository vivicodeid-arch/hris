<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TestPushNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Halo dari Presensi GPS! 👋')
            ->body('Ini adalah notifikasi uji coba push dari PWA Anda.')
            ->icon('/assets/img/icon-192x192.png')
            ->badge('/assets/img/icon-96x96.png')
            ->data([
                'action_url' => route('dashboard.index')
            ]);
    }
}
