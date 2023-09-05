<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveLab extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'lab_id',
        'flag',
        'project_name'
    ];

}
