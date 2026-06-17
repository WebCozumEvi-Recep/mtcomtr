<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateClick extends Model
{
    protected $table = 'affiliate_clicks';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'affiliate_id',
        'affiliate_link_id',
        'domain_id',
        'channel',
        'keyword',
        'media_id',
        'click_id',
        'ip_address',
        'user_agent',
        'referer',
        'device'
    ];

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
