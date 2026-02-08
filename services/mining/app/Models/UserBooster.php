<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBooster extends Model
{
    protected $table = 'user_boosters';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'booster_type', 'started_at', 'expires_at', 'is_active', 'created_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
