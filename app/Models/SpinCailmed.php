<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinCailmed extends Model
{
    use HasFactory;

    protected $table = 'spin_cailmed';
    
    public $timestamps = false; // Disable timestamps since we use StartedAt and EndAt

    protected $primaryKey = 'UserID';

    public $incrementing = false;

    protected $fillable = [
        'UserID', 'Total', 'EndAt', 'StartedAt'
    ];
}
