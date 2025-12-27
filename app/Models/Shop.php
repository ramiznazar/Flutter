<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $table = 'shop';
    
    protected $primaryKey = 'ID'; // The table uses 'ID' (uppercase) as primary key
    public $incrementing = true; // ID is auto-incrementing
    protected $keyType = 'int'; // ID is an integer
    
    public $timestamps = false; // Disable timestamps since we use CreatedAt field

    protected $fillable = [
        'Image', 'Title', 'Description', 'Link', 'Price',
        'Likes', 'isliked', 'Status', 'CreatedAt'
    ];

    public function views()
    {
        return $this->hasMany(ShopView::class, 'Shop_ID', 'ID');
    }
}
