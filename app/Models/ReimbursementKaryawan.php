<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReimbursementKaryawan extends Model
{
    use HasFactory;

    protected $table = 'reimbursement_karyawan';
    protected $guarded = ['id'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function jenis_reimbursement()
    {
        return $this->belongsTo(JenisReimbursement::class, 'kode_jenis_reimburse', 'kode_jenis_reimburse');
    }
}
