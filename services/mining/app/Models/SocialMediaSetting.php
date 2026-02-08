<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialMediaSetting extends Model
{
    protected $table = 'social_media_setting';

    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'Name', 'Icon', 'Link', 'Token', 'task_type', 'Status',
    ];

    protected $casts = [
        'Status' => 'boolean',
    ];

    public function socialMediaTokens()
    {
        return $this->hasMany(SocialMediaToken::class, 'social_media_id');
    }
}
