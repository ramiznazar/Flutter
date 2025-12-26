<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCompletion extends Model
{
    use HasFactory;

    protected $table = 'task_completions';
    
    public $timestamps = false; // Only has created_at, not updated_at, so disable timestamps

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    protected $fillable = [
        'user_id', 'task_id', 'task_type', 'started_at',
        'reward_available_at', 'reward_claimed', 'reward_claimed_at', 'created_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'reward_available_at' => 'datetime',
        'reward_claimed_at' => 'datetime',
        'reward_claimed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function task()
    {
        return $this->belongsTo(SocialMediaSetting::class, 'task_id');
    }
}
