<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdBoosterClaim extends Model
{
    protected $table = 'ad_booster_claims';

    public $timestamps = false;

    protected $fillable = ['user_id', 'claimed_at'];

    protected $casts = ['claimed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
