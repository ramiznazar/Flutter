<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $table = 'level';
    
    public $timestamps = false; // Disable timestamps for levels

    protected $fillable = [
        'lvl_name', 'mining_sessions', 'spin_wheel', 'total_invite',
        'user_account_old', 'perk_crutox_per_time', 'perk_mining_time',
        'perk_crutox_reward', 'perk_other_access', 'is_ads_block'
    ];

    public function userLevels()
    {
        return $this->hasMany(UserLevel::class, 'current_level');
    }
}
