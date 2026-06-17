<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = ['domain_id', 'product_id', 'offer_name', 'api_offer_id', 'offer_image', 'active_image', 'quantity', 'price', 'is_popular'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_popular' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function items()
    {
        return $this->hasMany(OfferItem::class);
    }

    public function affiliateCommission()
    {
        return $this->hasOne(AffiliatePackageCommission::class, 'package_id');
    }
}
