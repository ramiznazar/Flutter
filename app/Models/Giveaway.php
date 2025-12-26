<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Giveaway extends Model
{
    use HasFactory;

    protected $table = 'giveaway';
    
    public $timestamps = false; // Only has created_at, not updated_at, so disable timestamps

    protected $fillable = [
        'icon', 'title', 'description', 'link', 'reward',
        'start_date', 'end_date', 'status', 'redirect_link', 'created_at'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'reward' => 'decimal:2',
        'created_at' => 'datetime',
    ];
}
