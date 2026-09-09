<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriResign extends Model
{
    use HasFactory;

    protected $table = 'kategori_resign';
    protected $primaryKey = 'kode_kategori';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
