<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spin extends Model
{
    use HasFactory;

    protected $table = 'spin';
    
    public $timestamps = false; // Disable timestamps since we use CreatedAt field

    protected $fillable = [
        'Prize', 'Type', 'Color', 'CreatedAt', 'Status'
    ];

    protected $casts = [
        'Status' => 'boolean',
    ];
}
