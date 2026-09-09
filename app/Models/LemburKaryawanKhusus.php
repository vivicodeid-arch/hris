<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LemburKaryawanKhusus extends Model
{
    use HasFactory;

    protected $table = 'lembur_karyawan_khusus';
    protected $fillable = [
        'nik',
        'upah_perjam',
        'keterangan',
        'status',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
