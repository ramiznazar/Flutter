<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $table = 'badges';
    
    public $timestamps = false; // Disable timestamps for badges

    protected $fillable = [
        'id', 'badge_name', 'mining_sessions_required', 'spin_wheel_required',
        'invite_friends_required', 'crutox_in_wallet_required',
        'social_media_task_completed', 'badges_icon'
    ];
}
