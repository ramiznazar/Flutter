<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsLike extends Model
{
    use HasFactory;

    protected $table = 'news_like';
    
    public $timestamps = false; // Disable timestamps since we use CreatedAt field

    protected $fillable = [
        'News_ID', 'User_ID', 'CreatedAt'
    ];

    public function news()
    {
        return $this->belongsTo(News::class, 'News_ID', 'id');
    }
}
