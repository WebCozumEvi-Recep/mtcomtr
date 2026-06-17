<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder query()
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $operator = null, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder with(mixed $relations)
 * @method static mixed sum(string $column)
 * @method static int count()
 * @property int $id
 * @property string $domain_name
 * @property bool $is_active
 * @property int $visitor_count
 * @property int $unique_visitor_count
 */
class Domain extends Model
{
    use HasFactory;

    protected $fillable = ['domain_name', 'api_domain_id', 'cloudflare_zone_id', 'cloudflare_account_id', 'bunny_pullzone_id', 'bunny_hostname', 'is_active', 'ssl_active', 'ssl_certificate_expires_at', 'visitor_count', 'unique_visitor_count', 'brand_id'];

    protected function casts(): array
    {
        return [
            'ssl_certificate_expires_at' => 'datetime',
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function cloudflareAccount()
    {
        return $this->belongsTo(CloudflareAccount::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'domain_product');
    }

    public function config()
    {
        return $this->hasOne(FunnelConfig::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function gallery()
    {
        return $this->hasMany(FunnelImage::class)
            ->orderByRaw('original_name + 0 ASC')
            ->orderBy('original_name', 'asc')
            ->orderBy('sort_order', 'asc');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function latestOrder()
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    public function funnelEvents()
    {
        return $this->hasMany(FunnelEvent::class);
    }

    public function expenses()
    {
        return $this->hasMany(DomainExpense::class);
    }

    /**
     * Harici entegrasyon gövdesi / sorgu: api_domain_id (doluysa öncelik) veya domain_id.
     * İkisi birden gönderilirse api_domain_id ile bulunan kayıt, domain_id ile aynı olmalıdır; aksi halde null.
     */
    public static function forIntegrationPayload(array $payload): ?self
    {
        $apiId = isset($payload['api_domain_id']) ? trim((string) $payload['api_domain_id']) : '';
        $rawId = $payload['domain_id'] ?? null;
        $domainId = is_numeric($rawId) ? (int) $rawId : 0;

        if ($apiId !== '') {
            $domain = static::query()->where('api_domain_id', $apiId)->first();
            if (! $domain) {
                return null;
            }
            if ($domainId > 0 && $domainId !== (int) $domain->id) {
                return null;
            }

            return $domain;
        }

        if ($domainId > 0) {
            return static::query()->find($domainId);
        }

        return null;
    }

    public function getTotalExpenseAttribute()
    {
        return $this->expenses()->sum('amount');
    }

    public function upsellOffers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UpsellOffer::class);
    }

    public function affiliateSetting()
    {
        return $this->hasOne(AffiliateDomain::class, 'domain_id');
    }

    public function affiliatePackageCommissions()
    {
        return $this->hasMany(AffiliatePackageCommission::class, 'domain_id');
    }

    public function affiliateLinks()
    {
        return $this->hasMany(AffiliateLink::class, 'domain_id');
    }
}
