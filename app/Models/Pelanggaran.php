<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    use HasFactory;

    protected $table = 'pelanggaran';
    protected $primaryKey = 'no_sp';

    protected $fillable = [
        'no_sp',
        'nik',
        'tanggal',
        'dari',
        'sampai',
        'jenis_sp',
        'keterangan',
        'no_dokumen',
        'no_kontrak',
        'pasal_pelanggaran',
        'dasar_uu'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'dari' => 'date',
        'sampai' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'keterangan' => 'array',
        'pasal_pelanggaran' => 'array',
        'dasar_uu' => 'array',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Get the karyawan that owns the pelanggaran.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }
}
