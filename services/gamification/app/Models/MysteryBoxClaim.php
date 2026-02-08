<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MysteryBoxClaim extends Model
{
    protected $table = 'mystery_box_claims';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'box_type', 'clicks', 'last_clicked_at',
        'ads_watched', 'ads_required', 'last_ad_watched_at',
        'cooldown_until', 'box_opened', 'reward_coins', 'opened_at',
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
