<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycSubmission extends Model
{
    use HasFactory;

    protected $table = 'kyc_submissions';
    
    protected $primaryKey = 'id'; // Explicitly set primary key
    public $incrementing = true; // ID is auto-incrementing
    protected $keyType = 'int'; // ID is an integer
    
    public $timestamps = true; // Has both created_at and updated_at in SQL

    protected $fillable = [
        'user_id', 'full_name', 'dob', 'front_image', 'back_image',
        'status', 'admin_notes', 'didit_request_id', 'didit_status',
        'didit_verification_data', 'didit_verified_at'
    ];

    protected $casts = [
        'dob' => 'date',
        'didit_verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
