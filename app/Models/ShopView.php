<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopView extends Model
{
    use HasFactory;

    protected $table = 'shop_views';
    
    public $timestamps = false; // Disable timestamps since we use CreatedAt field

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ID'; // Uppercase ID as per table structure

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
        'Shop_ID', 'User_ID', 'CreatedAt'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'Shop_ID', 'id');
    }
}
