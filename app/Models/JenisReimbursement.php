<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisReimbursement extends Model
{
    use HasFactory;

    protected $table = 'jenis_reimbursement';
    protected $guarded = ['id'];

    public function enrollment()
    {
        return $this->hasMany(ReimbursementKaryawan::class, 'kode_jenis_reimburse', 'kode_jenis_reimburse');
    }

    public function details()
    {
        return $this->hasMany(ReimbursementDetail::class, 'kode_jenis_reimburse', 'kode_jenis_reimburse');
    }
}
