<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';
    
    protected $primaryKey = 'ID'; // The table uses 'ID' (uppercase) as primary key
    
    public $timestamps = false; // Disable timestamps since we use CreatedAt field

    protected $fillable = [
        'Image', 'Title', 'Description', 'Link', 'CreatedAt',
        'AdShow', 'RAdShow', 'Likes', 'isliked', 'Status'
    ];
    
    // Link column is optional - added dynamically by PHP code when needed
    // This matches the original PHP behavior where Link is added if it doesn't exist

    public function likes()
    {
        return $this->hasMany(NewsLike::class, 'News_ID', 'id');
    }
}
