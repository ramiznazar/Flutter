<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * User model for shared MySQL (mining-related columns only).
 * Balance/token/mining state must never be read from cache — always from DB.
 */
class User extends Model
{
    protected $table = 'users';

    public $timestamps = false;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name', 'email', 'phone', 'country', 'password', 'token', 'coin',
        'is_mining', 'mining_end_time', 'coin_end_time', 'total_coin_claim',
        'last_active', 'mining_time', 'username', 'username_count',
        'total_invite', 'invite_setup', 'account_status', 'ban_reason',
        'ban_date', 'otp', 'join_date', 'custom_coin_speed', 'auth_token',
        'mining_start_balance',
    ];

    protected $hidden = ['password'];

    public function userLevels()
    {
        return $this->hasMany(UserLevel::class, 'user_id');
    }
}
