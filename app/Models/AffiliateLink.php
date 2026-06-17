<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    protected $table = 'affiliate_links';

    protected $fillable = [
        'affiliate_id',
        'domain_id',
        'domain_url',
        'channel',
        'keyword',
        'media_id',
        'short_code',
        'full_affiliate_url',
        'target_path',
        'status'
    ];

    public function affiliate()
    {
        return $this->belongsTo(AffiliateUser::class, 'affiliate_id');
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function media()
    {
        return $this->belongsTo(AffiliateMedia::class, 'media_id');
    }

    public function clicks()
    {
        return $this->hasMany(AffiliateClick::class, 'affiliate_link_id');
    }

    public function orderAttributions()
    {
        return $this->hasMany(AffiliateOrderAttribution::class, 'affiliate_link_id');
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class, 'affiliate_link_id');
    }
}
