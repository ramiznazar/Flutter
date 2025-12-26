<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGuide extends Model
{
    use HasFactory;

    protected $table = 'user_guide';
    
    public $timestamps = false; // Disable timestamps for user guide

    protected $primaryKey = 'userID';

    public $incrementing = false;

    protected $fillable = [
        'userID', 'home', 'mining', 'wallet', 'badges', 'level',
        'teamProfile', 'news', 'shop', 'userProfile'
    ];

    protected $casts = [
        'home' => 'boolean',
        'mining' => 'integer',
        'wallet' => 'boolean',
        'badges' => 'boolean',
        'level' => 'boolean',
        'teamProfile' => 'boolean',
        'news' => 'boolean',
        'shop' => 'boolean',
        'userProfile' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }
}
