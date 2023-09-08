<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lab extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function difficultyInfo() : BelongsTo
    {
        return $this->belongsTo(LabDifficulty::class,'difficulty_id','id');
    }
}
