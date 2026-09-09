<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approvekoreksi extends Model
{
    use HasFactory;
    protected $table = 'presensi_koreksi_approve';
    protected $guarded = [];
}
