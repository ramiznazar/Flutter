<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared settings table (mining service only reads mining_speed).
 */
class Setting extends Model
{
    protected $table = 'settings';

    public $timestamps = false;

    protected $fillable = ['mining_speed'];
}
