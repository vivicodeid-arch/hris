<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class KaryawanMenuSetting extends Model
{
    use HasFactory;

    protected $table = 'karyawan_menu_settings';
    protected $guarded = [];

    /**
     * Cek apakah menu aktif (tampil) untuk karyawan.
     */
    public static function isActive($kode_menu)
    {
        $settings = Cache::remember('karyawan_menu_settings', 86400, function () {
            return self::pluck('status', 'kode_menu')->toArray();
        });

        return isset($settings[$kode_menu]) ? (bool)$settings[$kode_menu] : true;
    }

    /**
     * Hapus cache setting menu.
     */
    public static function clearCache()
    {
        Cache::forget('karyawan_menu_settings');
    }
}
