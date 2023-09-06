<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompletedLab extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lab_id'
    ];

    public $timestamps = false;

    public function completedLabInfo() : BelongsTo {
        return $this->belongsTo(Lab::class,"lab_id","id");
    }

}
