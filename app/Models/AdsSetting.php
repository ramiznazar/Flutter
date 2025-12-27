<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdsSetting extends Model
{
    use HasFactory;

    protected $table = 'ads_setting';
    
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    
    public $timestamps = false; // Disable timestamps for ads settings

    protected $fillable = [
        'applovin_sdk_key', 'applovin_inter_id', 'applovin_reward_id',
        'applovin_native_id', 'status'
    ];
}
