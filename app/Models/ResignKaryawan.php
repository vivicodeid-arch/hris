<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResignKaryawan extends Model
{
    use HasFactory;

    protected $table = 'resign_karyawans';
    protected $guarded = ['id'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kategoriResign()
    {
        return $this->belongsTo(KategoriResign::class, 'kode_kategori', 'kode_kategori');
    }
}
