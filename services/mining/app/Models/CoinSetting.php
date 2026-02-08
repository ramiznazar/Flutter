<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinSetting extends Model
{
    protected $table = 'coin_settings';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'seconds_per_coin', 'max_seconds_allow', 'claim_time_in_sec',
        'max_coin_claim_allow', 'token', 'token_price',
    ];
}
