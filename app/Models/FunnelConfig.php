<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Setting;

class FunnelConfig extends Model
{
    protected $fillable = [
        'domain_id', 'favicon_path', 'og_image_path', 'seo_title', 'seo_description', 'primary_color', 'secondary_color', 
        'header_scripts', 'body_scripts', 'footer_scripts', 'success_scripts',
        'whatsapp_number', 'countdown_minutes', 'stock_countdown_start',
        'facebook_pixel_id', 'google_analytics_id', 'google_verification_code', 'tiktok_pixel_id',
        'allow_credit_card', 'payment_provider_id'
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function paymentProvider()
    {
        return $this->belongsTo(PaymentProvider::class);
    }

    public function getFaviconUrlAttribute()
    {
        if (! $this->favicon_path) return null;
        $domainBunnyHost = trim((string) ($this->domain?->bunny_hostname ?? ''));
        $forcedBaseUrl = $domainBunnyHost !== '' ? 'https://'.$domainBunnyHost : null;
        return Setting::mediaUrl('uploads/branding/'.$this->favicon_path, null, $forcedBaseUrl);
    }

    public function getOgImageUrlAttribute()
    {
        if (! $this->og_image_path) return null;
        $domainBunnyHost = trim((string) ($this->domain?->bunny_hostname ?? ''));
        $forcedBaseUrl = $domainBunnyHost !== '' ? 'https://'.$domainBunnyHost : null;
        return Setting::mediaUrl('uploads/branding/'.$this->og_image_path, null, $forcedBaseUrl);
    }
}

// Separate call for Offer
