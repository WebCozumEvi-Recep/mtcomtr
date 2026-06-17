<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliatePackageCommission extends Model
{
    protected $table = 'affiliate_package_commissions';

    protected $fillable = [
        'domain_id',
        'package_id',
        'package_api_key',
        'is_affiliate_active',
        'visible_to_affiliate',
        'commission_type',
        'commission_amount',
        'commission_rate',
        'affiliate_description'
    ];

    protected $casts = [
        'is_affiliate_active' => 'boolean',
        'visible_to_affiliate' => 'boolean',
        'commission_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function package()
    {
        return $this->belongsTo(Offer::class, 'package_id');
    }
}
