<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * User model for shared MySQL (KYC: eligibility, submit, status).
 */
class User extends Model
{
    protected $table = 'users';

    public $timestamps = false;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'email', 'account_status', 'total_invite',
    ];

    protected $hidden = ['password'];
}
