<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateOrderAttribution extends Model
{
    protected $table = 'affiliate_order_attributions';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'affiliate_id',
        'affiliate_link_id',
        'click_id',
        'domain_id',
        'channel',
        'keyword',
        'media_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function affiliate()
    {
        return $this->belongsTo(AffiliateUser::class, 'affiliate_id');
    }

    public function link()
    {
        return $this->belongsTo(AffiliateLink::class, 'affiliate_link_id');
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function media()
    {
        return $this->belongsTo(AffiliateMedia::class, 'media_id');
    }
}
