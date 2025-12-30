<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBooster extends Model
{
    use HasFactory;

    protected $table = 'user_boosters';
    
    public $timestamps = false; // Only has created_at, not updated_at, so disable timestamps

    protected $fillable = [
        'user_id', 'booster_type', 'started_at', 'expires_at', 'is_active', 'created_at'
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
