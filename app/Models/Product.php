<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'api_product_id',
        'price',
        'cost_price',
        'stock_quantity',
        'description',
        'image_url',
        'original_image_name',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function domains()
    {
        return $this->belongsToMany(Domain::class, 'domain_product');
    }

    public function getImageUrlAttribute($value)
    {
        return Setting::mediaUrl($value);
    }
}
