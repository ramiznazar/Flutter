<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    use HasFactory;

    protected $table = 'user_levels';
    
    public $timestamps = false; // Disable timestamps since we use achieved_at

    protected $fillable = [
        'user_id', 'mining_session', 'spin_wheel', 'current_level', 'achieved_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'current_level');
    }
}
