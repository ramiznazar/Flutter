<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopView extends Model
{
    use HasFactory;

    protected $table = 'shop_views';
    
    public $timestamps = false; // Disable timestamps since we use CreatedAt field

    protected $fillable = [
        'Shop_ID', 'User_ID', 'CreatedAt'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'Shop_ID', 'id');
    }
}
