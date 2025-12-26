<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currency';
    
    public $timestamps = false; // Disable timestamps for currency

    protected $fillable = [
        'currency', 'value', 'icon', 'status'
    ];
}
