# Dokumentasi Instalasi & Panduan Web Push Notification PWA

Dokumen ini menjelaskan langkah-langkah instalasi, konfigurasi, dan penggunaan fitur Web Push Notification pada aplikasi PWA Presensi GPS V2 menggunakan Laravel.

## 🛠️ Persyaratan Sistem
1. Koneksi **HTTPS** yang valid (wajib untuk lingkungan production, di localhost dapat berjalan di HTTP biasa).
2. Browser modern yang mendukung Service Worker dan Web Push API (Chrome, Edge, Firefox, Safari iOS 16.4+).

---

## 🚀 Langkah Instalasi & Konfigurasi Backend

### 1. Menginstal Package Dependensi
Package `laravel-notification-channels/webpush` telah diinstal ke dalam proyek. Jika melakukan instalasi bersih di server baru, jalankan:
```bash
composer install
```

### 2. Konfigurasi Database (Tabel Subscription)
Tabel database `push_subscriptions` digunakan untuk menyimpan token perangkat masing-masing pengguna. Jalankan migrasi database:
```bash
php artisan migrate
```

### 3. Pembuatan Kunci VAPID (Voluntary Application Server Identification)
Kunci VAPID digunakan untuk mengamankan komunikasi notifikasi antara server Laravel Anda dan penyedia push service browser (seperti FCM atau Mozilla Push Service). Jalankan perintah berikut untuk menggenerasi kunci baru:
```bash
php artisan webpush:vapid
```
Perintah ini akan menambahkan variabel berikut secara otomatis pada file `.env` Anda:
```env
VAPID_PUBLIC_KEY=xxxxxx...
VAPID_PRIVATE_KEY=xxxxxx...
```

### 4. Konfigurasi Model User
Pastikan model `App\Models\User` menggunakan trait `HasPushSubscriptions` agar Laravel dapat menghubungkan user dengan token perangkatnya.
```php
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    use HasPushSubscriptions;
}
```

---

## 📡 Cara Kerja & Alur Registrasi Frontend

1. **Service Worker (`public/sw.js`)**:
   Mendengarkan event `'push'` dari browser/sistem operasi, mengekstrak payload data dari server Laravel, dan menampilkan pemberitahuan sistem (banner notifikasi) menggunakan fungsi `self.registration.showNotification()`.
2. **Layout Mobile (`resources/views/layouts/mobile/modern.blade.php`)**:
   Ketika user login, skrip JS secara otomatis:
   - Mengecek dukungan fitur Push Notification.
   - Meminta izin/permission kepada user (`Notification.requestPermission()`).
   - Membuat PushSubscription menggunakan kunci `VAPID_PUBLIC_KEY` dari `.env`.
   - Mengirim payload subscription ke backend melalui endpoint POST `/push-subscriptions` untuk disimpan di database.

---

## ✉️ Cara Mengirim Notifikasi Push dari Backend Laravel

Untuk mengirimkan notifikasi kepada user (misal: saat pengajuan izin disetujui), Anda dapat membuat Notification class baru di Laravel.

### 1. Membuat Kelas Notifikasi
```bash
php artisan make:notification PengajuanIzinApproved
```

### 2. Menggunakan WebPush Channel
Modifikasi file Notification yang baru dibuat untuk menggunakan channel `WebPushChannel` dan mengembalikan payload `WebPushMessage`. Contoh:

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PengajuanIzinApproved extends Notification
{
    use Queueable;

    protected $izinText;

    public function __construct($izinText)
    {
        $this->izinText = $izinText;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Pengajuan Disetujui! 🎉')
            ->body('Pengajuan ' . $this->izinText . ' Anda telah disetujui oleh admin.')
            ->icon('/assets/img/icon-192x192.png')
            ->badge('/assets/img/icon-96x96.png')
            ->data([
                'action_url' => route('pengajuanizin.index')
            ]);
    }
}
```

### 3. Mengirimkan Notifikasi ke User
Panggil method `notify` pada model `User`:
```php
use App\Models\User;
use App\Notifications\PengajuanIzinApproved;

$user = User::find($id_user);
$user->notify(new PengajuanIzinApproved('Izin Cuti'));
```

---

## 🔍 Troubleshooting & Solusi Masalah Umum

### 1. Notifikasi tidak muncul di iOS (iPhone/iPad)
- Pastikan versi iOS Anda minimal **iOS 16.4**.
- Aplikasi PWA **harus ditambahkan ke Home Screen** (Add to Home Screen) terlebih dahulu. Notifikasi push Web di Safari/iOS hanya berfungsi jika aplikasi berjalan dalam mode standalone (PWA terinstal).

### 2. Error "Push subscription failed: VAPID keys not configured"
- Pastikan Anda sudah menjalankan `php artisan webpush:vapid` dan variabel `VAPID_PUBLIC_KEY` & `VAPID_PRIVATE_KEY` sudah terisi di file `.env`.
- Jika Anda mengubah VAPID keys di server, pengguna harus melakukan unregister/subscribe ulang agar token mereka terenkripsi menggunakan kunci yang baru.
