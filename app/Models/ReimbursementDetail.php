<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReimbursementDetail extends Model
{
    use HasFactory;

    protected $table = 'reimbursement_detail';
    protected $guarded = ['id'];

    public function reimbursement()
    {
        return $this->belongsTo(Reimbursement::class, 'reimbursement_id', 'id');
    }

    public function jenis_reimbursement()
    {
        return $this->belongsTo(JenisReimbursement::class, 'kode_jenis_reimburse', 'kode_jenis_reimburse');
    }
}
