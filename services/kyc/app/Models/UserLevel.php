<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * User level (mining_session count) for KYC eligibility. Shared MySQL.
 */
class UserLevel extends Model
{
    protected $table = 'user_levels';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'mining_session', 'spin_wheel', 'current_level', 'achieved_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
