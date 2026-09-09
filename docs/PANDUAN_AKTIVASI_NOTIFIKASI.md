# 📢 Panduan Aktivasi & Konfigurasi Push Notification PWA (Server & User)

Dokumen ini berisi panduan lengkap untuk **mengaktifkan dan mengonfigurasi** fitur **Web Push Notification PWA** pada aplikasi **Presensi GPS V2** setelah di-upload ke server (hosting/VPS), serta panduan langkah aktivasi untuk pengguna (karyawan) dan admin.

---

## 🛠️ BAGIAN 1: Konfigurasi di Server (Developer/SysAdmin)

Agar fitur Push Notification dapat berjalan di server produksi, Anda wajib melakukan langkah-langkah berikut setelah mengunggah source code ke server:

### 1. Wajib Menggunakan Protokol HTTPS (SSL)
> [!IMPORTANT]
> Fitur Web Push Notification (Service Worker & Push API) **wajib berjalan di bawah protokol HTTPS** dengan sertifikat SSL yang valid di lingkungan server produksi.
> Fitur ini tidak akan berfungsi jika server Anda menggunakan HTTP biasa. (Di `localhost`, HTTP diperbolehkan oleh browser untuk kebutuhan development).

### 2. Jalankan Migrasi Database
Tabel `push_subscriptions` digunakan untuk menyimpan token notifikasi dari perangkat masing-masing pengguna. Jalankan migrasi database di server Anda:
```bash
php artisan migrate
```

### 3. Generate VAPID Keys di Server
VAPID keys diperlukan untuk mengamankan dan memverifikasi pengiriman notifikasi antara server Laravel Anda dengan push service browser (seperti Google FCM, Apple Push Notification Service, dll).

Jalankan perintah berikut di terminal server Anda (pada folder root project):
```bash
php artisan webpush:vapid
```
Perintah ini akan secara otomatis menambahkan kunci baru ke file `.env` di server Anda:
```env
VAPID_PUBLIC_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
VAPID_PRIVATE_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

> [!TIP]
> Jika Anda sudah memiliki VAPID Keys yang digunakan sebelumnya (misal dari local development atau server lama), Anda cukup menyalin nilai `VAPID_PUBLIC_KEY` dan `VAPID_PRIVATE_KEY` tersebut ke dalam file `.env` server secara manual.

### 4. Bersihkan Cache Konfigurasi (Config Cache)
Agar Laravel dapat membaca VAPID keys yang baru saja ditambahkan ke `.env`, bersihkan cache konfigurasi dengan menjalankan:
```bash
php artisan config:clear
php artisan cache:clear
```
Jika Anda menggunakan caching konfigurasi di production (direkomendasikan), jalankan:
```bash
php artisan config:cache
```

### 5. Pastikan Service Worker Berjalan
Pastikan file `public/sw.js` dapat diakses secara publik melalui URL: `https://domain-anda.com/sw.js`. Browser memerlukan file ini di root path domain untuk mendaftarkan Service Worker PWA.

---

## 📱 BAGIAN 2: Panduan Aktivasi untuk Pengguna (Karyawan)

Setelah konfigurasi server selesai, pengguna (karyawan) dapat mengaktifkan notifikasi di HP/PC mereka melalui langkah-langkah berikut:

### 🤖 A. Pengguna Android (Google Chrome / Browser Lain)
1. Buka browser **Google Chrome** di HP Anda dan akses website aplikasi presensi.
2. **Instal Aplikasi PWA (Sangat Direkomendasikan):** 
   - Klik tombol menu (titik tiga) di kanan atas Chrome, lalu pilih **"Tambahkan ke Layar Utama"** atau **"Instal Aplikasi"** (Install App).
   - Setelah terinstal, buka aplikasi Presensi GPS langsung dari layar utama HP Anda.
3. Masuk ke halaman **Profil** (menu di kanan bawah).
4. Geser toggle **"Notifikasi Push PWA"** menjadi **ON** (Aktif).
5. Ketika muncul jendela konfirmasi dari browser/sistem HP, ketuk **"Allow"** atau **"Izinkan"**.

### 🍎 B. Pengguna iOS / iPhone / iPad (Safari)
> [!IMPORTANT]
> Sesuai kebijakan Apple, push notification web hanya didukung mulai dari **iOS 16.4+** dan **Aplikasi WAJIB ditambahkan ke Layar Utama (Add to Home Screen)**. Fitur ini tidak akan aktif jika hanya dibuka melalui browser Safari biasa.

1. Buka browser bawaan **Safari** di iPhone Anda dan akses website aplikasi presensi.
2. Klik tombol **"Share"** (ikon kotak dengan panah ke atas di bagian bawah layar).
3. Gulir ke bawah lalu pilih menu **"Add to Home Screen"** (Tambahkan ke Layar Utama).
4. Keluar dari Safari, lalu buka aplikasi Presensi yang sudah terinstal di layar utama iPhone Anda (PWA).
5. Login ke akun Anda, lalu masuk ke menu **Profil**.
6. Geser toggle **"Notifikasi Push PWA"** menjadi **ON**.
7. Saat muncul permintaan izin notifikasi dari iOS, ketuk **"Allow"** (Izinkan).

### 💻 C. Pengguna Desktop / Laptop (Chrome, Edge, Firefox)
1. Buka browser (rekomendasi Chrome/Edge) dan akses website aplikasi presensi.
2. Login dan masuk ke menu **Profil** (di kanan bawah atau menu dropdown profil).
3. Temukan bagian **"Notifikasi Push PWA"** dan geser toggle ke posisi **ON**.
4. Saat muncul pop-up izin notifikasi di pojok kiri atas browser Anda, klik **"Allow"** atau **"Izinkan"**.

---

## 🔍 Solusi Masalah / Troubleshooting Pengguna

### Gejala: Toggle otomatis kembali mati / Muncul Pesan "Izin Notifikasi Diblokir"
Ini terjadi karena pengguna sebelumnya pernah memilih **"Block" / "Blokir"** saat browser meminta izin notifikasi. Browser akan mengingat keputusan tersebut dan memblokir permintaan izin berikutnya secara otomatis.

#### Cara Mengatasinya:
* **Google Chrome (Android / Desktop):**
  1. Klik ikon **Gembok / Pengaturan Situs** di sebelah kiri kolom alamat URL browser (address bar).
  2. Temukan bagian **Notifications / Notifikasi**.
  3. Ubah statusnya dari **Block/Blokir** menjadi **Allow/Izinkan** (atau klik *Reset Permission*).
  4. Refresh/segarkan halaman web, masuk kembali ke **Profil**, lalu aktifkan kembali toggle Notifikasi.

* **Safari (iOS / iPhone PWA):**
  1. Buka aplikasi **Settings (Pengaturan)** bawaan iPhone Anda.
  2. Masuk ke menu **Notifications (Pemberitahuan)**.
  3. Cari nama aplikasi **Presensi GPS** terinstal di daftar aplikasi.
  4. Aktifkan menu **"Allow Notifications"** (Izinkan Pemberitahuan).
  5. Buka kembali aplikasi Presensi PWA dan aktifkan toggle pada halaman profil.

* **Mozilla Firefox:**
  1. Klik ikon informasi/gembok di sebelah kiri kolom alamat URL.
  2. Hapus izin blokir dengan mengeklik tanda silang **(X)** di sebelah tulisan "Blocked" pada bagian izin notifikasi.
  3. Refresh halaman dan aktifkan kembali toggle di halaman profil.

---

## ⚙️ BAGIAN 3: Panduan Admin (Uji Coba & Manajemen Notifikasi)

Sebagai administrator, Anda dapat mengelola seluruh perangkat pengguna yang berlangganan notifikasi dan mengirimkan test notifikasi untuk memastikan sistem berjalan dengan baik.

### 1. Akses Menu Utilities / Utilitas
1. Login sebagai **Super Admin**.
2. Di sidebar kiri, buka menu **Utilities** > **Push Subscriptions** (atau akses langsung URL `/push-subscription`).
3. Halaman ini akan menampilkan daftar seluruh perangkat karyawan yang telah sukses berlangganan beserta tipe browser dan tanggal berlangganan.

### 2. Mengirim Test Notifikasi (Uji Coba)
Pada tabel daftar subscription, klik tombol **Uji Coba Notifikasi** (ikon Lonceng Biru 🔔) di sebelah kanan nama karyawan.
- Sistem akan mengirimkan push notification uji coba secara real-time ke perangkat karyawan tersebut.
- Jika berhasil, perangkat karyawan akan menampilkan banner notifikasi berisi pesan test.
- Tombol uji coba ini diletakkan bersebelahan dengan tombol Hapus (ikon Tempat Sampah Merah 🗑️) untuk memudahkan administrasi.
