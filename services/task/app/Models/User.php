<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * User model for shared MySQL (task-related columns only).
 */
class User extends Model
{
    protected $table = 'users';

    public $timestamps = false;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'email', 'token', 'coin', 'is_mining', 'mining_start_balance', 'account_status',
    ];

    protected $hidden = ['password'];
}
