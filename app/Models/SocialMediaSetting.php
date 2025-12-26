<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMediaSetting extends Model
{
    use HasFactory;

    protected $table = 'social_media_setting';
    
    public $timestamps = false; // Disable timestamps for social media settings

    protected $fillable = [
        'Name', 'Icon', 'Link', 'Token', 'task_type', 'Status'
    ];

    protected $casts = [
        'Status' => 'boolean',
    ];

    public function taskCompletions()
    {
        return $this->hasMany(TaskCompletion::class, 'task_id');
    }

    public function socialMediaTokens()
    {
        return $this->hasMany(SocialMediaToken::class, 'social_media_id');
    }
}
