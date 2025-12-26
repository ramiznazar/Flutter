<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin';
    
    public $timestamps = true; // Enable timestamps for admin

    protected $fillable = [
        'username', 'email', 'password', 'name', 'last_login'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'password' => 'hashed',
    ];
}
