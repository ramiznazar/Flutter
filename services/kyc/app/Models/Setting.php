<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared settings table (KYC: kyc_mining_sessions, kyc_referrals_required).
 */
class Setting extends Model
{
    protected $table = 'settings';

    public $timestamps = false;

    protected $fillable = [
        'kyc_mining_sessions', 'kyc_referrals_required',
    ];
}
