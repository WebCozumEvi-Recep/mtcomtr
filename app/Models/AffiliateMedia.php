<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateMedia extends Model
{
    protected $table = 'affiliate_media';

    protected $fillable = [
        'domain_id',
        'title',
        'media_type',
        'channel',
        'size_label',
        'file_path',
        'share_text',
        'status'
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function links()
    {
        return $this->hasMany(AffiliateLink::class, 'media_id');
    }
}
