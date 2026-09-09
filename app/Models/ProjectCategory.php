<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCategory extends Model
{
    use HasFactory;

    protected $table = 'project_categories';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'warna',
    ];

    /**
     * Get the projects in this category.
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'category_id');
    }
}
