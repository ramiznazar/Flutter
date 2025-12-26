<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMediaToken extends Model
{
    use HasFactory;

    protected $table = 'social_media_tokens';
    
    public $timestamps = false; // Disable timestamps since we use claim_date

    protected $fillable = [
        'user_id', 'social_media_id', 'claim_date'
    ];

    protected $casts = [
        'claim_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function socialMedia()
    {
        return $this->belongsTo(SocialMediaSetting::class, 'social_media_id');
    }
}
