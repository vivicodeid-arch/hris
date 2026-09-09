<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class SlipgajiNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $slipgaji;

    /**
     * Create a new notification instance.
     */
    public function __construct($slipgaji)
    {
        $this->slipgaji = $slipgaji;
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
        $namaBulan = getNamabulan($this->slipgaji->bulan);
        return [
            'title' => 'Slip Gaji Diterbitkan 💰',
            'message' => 'Slip gaji Anda untuk bulan ' . $namaBulan . ' ' . $this->slipgaji->tahun . ' telah diterbitkan.',
            'url' => route('slipgaji.index'),
            'type' => 'slipgaji',
            'icon' => 'ti-wallet'
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

        $namaBulan = getNamabulan($this->slipgaji->bulan);

        return (new WebPushMessage)
            ->title('Slip Gaji Diterbitkan 💰')
            ->body('Slip gaji Anda untuk bulan ' . $namaBulan . ' ' . $this->slipgaji->tahun . ' telah diterbitkan. Silakan cek detailnya.')
            ->icon($icon)
            ->badge(asset('assets/img/icon-96x96.png'))
            ->data([
                'action_url' => route('slipgaji.index')
            ]);
    }
}
