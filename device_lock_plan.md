# Rencana Implementasi Penguncian Perangkat (Device Lock / Device Binding)

Dokumen ini menjelaskan rancangan sistem penguncian akun karyawan pada satu perangkat fisik (browser/handphone). Sekali login di sebuah device, akun tidak bisa login di device lain kecuali di-reset oleh admin.

## Alur Sistem

1. **Pendaftaran Perangkat Pertama Kali**:
   Saat karyawan pertama kali login, Javascript di halaman login akan memeriksa apakah ada `user_device_id` di `localStorage` browser. Jika belum ada, sistem akan membuat ID Unik (UUID) baru secara otomatis dan menyimpannya di `localStorage`. ID ini akan dikirim bersama form login dan disimpan di database pada kolom `device_id` di tabel `users`.
   
2. **Kunci Akun**:
   Setiap kali karyawan login berikutnya, Javascript akan mengirim `device_id` yang tersimpan di `localStorage`. Sistem akan mencocokkan `device_id` tersebut dengan yang ada di database.
   
3. **Penolakan Login**:
   Jika `device_id` yang dikirim dari browser tidak cocok dengan yang ada di database (artinya karyawan mencoba login dari browser/hp lain), maka login akan ditolak secara otomatis dan menampilkan pesan error.
   
4. **Reset Perangkat oleh Admin**:
   Super Admin/Admin dapat me-reset atau menghapus `device_id` karyawan lewat menu manajemen user (Settings -> Users). Setelah di-reset, status `device_id` kembali menjadi NULL dan karyawan tersebut bisa login kembali menggunakan perangkat baru (yang nantinya akan otomatis terikat sebagai perangkat aktif baru).

---

## Rencana Perubahan Code

### 1. Database Migration
Buat migration baru untuk menambahkan kolom `device_id` di tabel `users`:
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('device_id')->nullable()->after('password');
});
```

### 2. Form Login View
Ubah file `resources/views/auth/login.blade.php`:
- Tambahkan input hidden: `<input type="hidden" name="device_id" id="device_id">`
- Tambahkan script Javascript untuk generate UUID unik (misalnya kombinasi random string & timestamp) jika belum ada di `localStorage`, lalu masukkan nilainya ke input `#device_id`.

### 3. Controller Login
Ubah file `app/Http/Controllers/Auth/AuthenticatedSessionController.php` pada method `store`:
- Setelah user berhasil terautentikasi (`$request->authenticate()`):
  - Cek jika user login memiliki role `karyawan`.
  - Jika ya:
    - Ambil nilai `device_id` dari request.
    - Jika `device_id` di database masih kosong (NULL), simpan `device_id` dari request ke database.
    - Jika `device_id` di database tidak kosong, bandingkan nilainya. Jika tidak cocok, lakukan `Auth::logout()`, batalkan session, lalu redirect kembali ke halaman login dengan pesan: `"Akun Anda terikat pada device lain. Silakan hubungi admin untuk melakukan reset device."`

### 4. Route Reset Device
Tambahkan route baru di `routes/web.php` (di dalam grup middleware super admin/admin):
```php
Route::post('/users/{id}/reset-device', [UserController::class, 'resetDevice'])->name('users.reset-device');
```

### 5. Controller User Management
Ubah `app/Http/Controllers/UserController.php`:
- Implementasikan method `resetDevice($id)`:
  ```php
  public function resetDevice($id)
  {
      $id = Crypt::decrypt($id);
      User::where('id', $id)->update(['device_id' => null]);
      return Redirect::back()->with(['success' => 'Device binding berhasil di-reset.']);
  }
  ```

### 6. View User Management
Ubah `resources/views/settings/users/index.blade.php`:
- Di tabel user, untuk baris user yang memiliki role `karyawan` dan memiliki `device_id` tidak kosong, tampilkan tombol "Reset Device".
- Tombol ini akan memicu form POST/DELETE ke route `/users/{id}/reset-device` setelah user mengonfirmasi pop-up dialog.
