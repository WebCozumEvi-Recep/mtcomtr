<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpsellOffer extends Model
{
    protected $fillable = [
        'domain_id', 'name', 'title', 'description', 'offer_type',
        'target_product_id', 'target_package_id', 'original_price',
        'discount_price', 'display_timing', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'original_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'target_product_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'target_package_id');
    }
}
