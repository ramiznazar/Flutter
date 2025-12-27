<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MysteryBoxClaim extends Model
{
    use HasFactory;

    protected $table = 'mystery_box_claims';
    
    protected $primaryKey = 'id'; // Explicitly set primary key
    public $incrementing = true; // ID is auto-incrementing
    protected $keyType = 'int'; // ID is an integer
    
    public $timestamps = false; // Only has created_at, not updated_at, so disable timestamps

    protected $fillable = [
        'user_id', 'box_type', 'clicks', 'last_clicked_at',
        'ads_watched', 'ads_required', 'last_ad_watched_at',
        'cooldown_until', 'box_opened', 'reward_coins', 'opened_at'
    ];

    protected $casts = [
        'last_clicked_at' => 'datetime',
        'last_ad_watched_at' => 'datetime',
        'cooldown_until' => 'datetime',
        'opened_at' => 'datetime',
        'box_opened' => 'boolean',
        'reward_coins' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
