<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared settings table (task service only reads/updates daily_tasks_reset_time).
 */
class Setting extends Model
{
    protected $table = 'settings';

    public $timestamps = false;

    protected $fillable = ['id', 'daily_tasks_reset_time'];
}
