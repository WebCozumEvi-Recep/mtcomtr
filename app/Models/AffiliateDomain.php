<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateDomain extends Model
{
    protected $table = 'affiliate_domains';

    protected $fillable = [
        'domain_id',
        'is_affiliate_active',
        'affiliate_title',
        'affiliate_description',
        'cookie_days',
        'attribution_rule',
        'media_enabled',
        'warning_text',
        'forbidden_terms'
    ];

    protected $casts = [
        'is_affiliate_active' => 'boolean',
        'media_enabled' => 'boolean',
        'cookie_days' => 'integer',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }
}
