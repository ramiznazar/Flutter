<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = 'users';
    
    public $timestamps = false; // Disable timestamps since we use custom date fields

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int'; // Using 'int' to match SQL backup, change to 'string' if using UUID

    protected $fillable = [
        'name', 'email', 'phone', 'country', 'password', 'token', 'coin',
        'is_mining', 'mining_end_time', 'coin_end_time', 'total_coin_claim',
        'last_active', 'mining_time', 'username', 'username_count',
        'total_invite', 'invite_setup', 'account_status', 'ban_reason',
        'ban_date', 'otp', 'join_date', 'custom_coin_speed', 'auth_token',
        'mining_start_balance'
    ];

    protected $hidden = [
        'password',
    ];

    // Relationships
    public function userGuide()
    {
        return $this->hasOne(UserGuide::class, 'userID', 'id');
    }

    public function userLevels()
    {
        return $this->hasMany(UserLevel::class, 'user_id');
    }

    public function kycSubmissions()
    {
        return $this->hasMany(KycSubmission::class, 'user_id');
    }

    public function taskCompletions()
    {
        return $this->hasMany(TaskCompletion::class, 'user_id');
    }

    public function userBoosters()
    {
        return $this->hasMany(UserBooster::class, 'user_id');
    }

    public function mysteryBoxClaims()
    {
        return $this->hasMany(MysteryBoxClaim::class, 'user_id');
    }

    public function socialMediaTokens()
    {
        return $this->hasMany(SocialMediaToken::class, 'user_id');
    }
}
