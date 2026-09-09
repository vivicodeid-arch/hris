# ✅ SELESAI - Perbaikan Logika Presensi Lintas Hari

Tanggal implementasi: 15 April 2026

## Perubahan yang Dilakukan

### 1. Database
- Menambah kolom `batas_presensi_pulang` (TIME, nullable) di tabel `presensi_jamkerja`
- Migration: `2026_04_15_162833_add_batas_presensi_pulang_to_presensi_jamkerja_table`

### 2. Data Master - Jam Kerja (UI)
- **create.blade.php**: Ditambahkan input "Batas Jam Pulang Lintas Hari" yang muncul hanya jika Lintas Hari = Ya
- **edit.blade.php**: Sama seperti create, dengan nilai yang sudah terisi dari database
- **JamkerjaController.php**: Validasi dan penyimpanan field baru di method `store` dan `update`

### 3. Logika Presensi (Semua Controller)
Perubahan diterapkan di **5 controller**:
- `PresensiController.php` (Web) - method `create` dan `store`
- `Api/PresensiController.php` (Mobile App)
- `PublicPresensiController.php` (Kiosk RFID)
- `FacerecognitionpresensiController.php` (Face Recognition)
- `Api/AdmsController.php` (Mesin Fingerprint)

### Logika Baru:
1. **Batas Jam Dinamis**: Sistem sekarang mengecek `batas_presensi_pulang` di **jam kerja masing-masing** dulu. Jika kosong, baru fallback ke `batas_presensi_lintashari` di **General Setting**.
2. **Auto-Switch jika Sudah Pulang**: Jika karyawan sudah melakukan absen pulang (`jam_out != null`) untuk shift lintas hari kemarin, maka scan berikutnya OTOMATIS dianggap sebagai presensi untuk **tanggal hari ini**, tanpa peduli batas jam cut-off.

## Cara Penggunaan
1. Buka menu **Konfigurasi > Jam Kerja**
2. Edit atau buat jam kerja baru
3. Set **Lintas Hari** = Ya
4. Isi **Batas Jam Pulang Lintas Hari** (contoh: `10:00`)
5. Jika dikosongkan, otomatis menggunakan nilai dari General Setting
