<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PengumumanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $pengumuman;

    /**
     * Create a new notification instance.
     */
    public function __construct($pengumuman)
    {
        $this->pengumuman = $pengumuman;
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pengumuman Baru: ' . $this->pengumuman->judul,
            'message' => substr(strip_tags($this->pengumuman->isi), 0, 100) . '...',
            'url' => route('pengumuman.index'), // Or show page if it exists for users
            'type' => 'pengumuman',
            'icon' => 'ti-bell'
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
            ->title('Pengumuman Baru: ' . $this->pengumuman->judul)
            ->body(substr(strip_tags($this->pengumuman->isi), 0, 100) . '...')
            ->icon($icon)
            ->badge(asset('assets/img/icon-96x96.png'))
            ->data([
                'action_url' => route('pengumuman.index')
            ]);
    }
}

