<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ActiveLab extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'lab_id',
        'flag',
        'project_name',
        'port'
    ];
    protected $dates = ['launch_time'];

    public function activeLabInfo() : BelongsTo {
        return $this->belongsTo(Lab::class,"lab_id","id");
    }
    public function userInfo() : BelongsTo {
        return $this->belongsTo(User::class,"user_id","id");
    }
}
