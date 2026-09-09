<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalFeature extends Model
{
    use HasFactory;

    protected $table = 'approval_features';
    protected $guarded = ['id'];
}
