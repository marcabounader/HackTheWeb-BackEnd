<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'badge_id'
    ];

    
    public function badgeInfo(): BelongsTo
    {
        return $this->belongsTo(Badge::class,'badge_id','id');
    }
}

