<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinSetting extends Model
{
    use HasFactory;

    protected $table = 'coin_settings';
    
    public $timestamps = false; // Disable timestamps for settings

    protected $fillable = [
        'seconds_per_coin', 'max_seconds_allow', 'claim_time_in_sec',
        'max_coin_claim_allow', 'token', 'token_price'
    ];
}
