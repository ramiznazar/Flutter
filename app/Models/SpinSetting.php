<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinSetting extends Model
{
    use HasFactory;

    protected $table = 'spin_setting';
    
    public $timestamps = false; // Disable timestamps for spin settings

    protected $fillable = [
        'ShowAd', 'AdType', 'MaxLimit', 'Time', 'SpinShow'
    ];

    protected $casts = [
        'ShowAd' => 'boolean',
        'SpinShow' => 'boolean',
    ];
}
