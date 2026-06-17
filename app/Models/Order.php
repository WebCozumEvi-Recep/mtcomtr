<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder query()
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $operator = null, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 * @method static \Illuminate\Database\Eloquent\Builder whereDate(string $column, mixed $operator = null, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder whereBetween(string $column, array $values)
 * @method static \Illuminate\Database\Eloquent\Builder whereMonth(string $column, mixed $value)
 * @method static \Illuminate\Database\Eloquent\Builder whereYear(string $column, mixed $value)
 * @method static \Illuminate\Database\Eloquent\Builder with(mixed $relations)
 * @method static \Illuminate\Database\Eloquent\Builder orderBy(string $column, string $direction = 'asc')
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginate(int $perPage = null, array $columns = ['*'], string $pageName = 'page', int $page = null)
 * @method static mixed sum(string $column)
 * @method static int count()
 * @method void loadMissing(mixed $relations)
 * @property int $id
 * @property string $internal_order_no
 * @property string $order_number
 * @property string $status
 * @property string $payment_status
 * @property string $order_notes
 * @property float $grand_total
 * @property string $address
 * @property string $city
 * @property string $district
 * @property \Illuminate\Support\Carbon|null $reconciled_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Customer|null $customer
 */
class Order extends \Illuminate\Database\Eloquent\Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = ['domain_id', 'offer_id', 'customer_id', 'email', 'id_number', 'ip_address', 'city', 'district', 'address', 'grand_total', 'status', 'fraud_score', 'order_notes', 'cargo_firm', 'tracking_number', 'is_cargo', 'internal_order_no', 'payment_status', 'reconciled_at', 'original_total', 'upsell_total', 'final_total', 'has_upsell', 'is_printed', 'payment_method', 'is_api', 'api_sent_at', 'api_approved', 'api_order_id'];

    protected $casts = [
        'grand_total' => 'decimal:2',
        'original_total' => 'decimal:2',
        'upsell_total' => 'decimal:2',
        'final_total' => 'decimal:2',
        'has_upsell' => 'boolean',
        'is_printed' => 'boolean',
        'is_api' => 'boolean',
        'api_approved' => 'boolean',
        'api_sent_at' => 'datetime',
        'is_cargo' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->internal_order_no)) {
                $lastOrder = static::orderBy('id', 'desc')->first();
                $lastId = $lastOrder ? $lastOrder->id + 1 : 1;
                $order->internal_order_no = 'TS-' . \Illuminate\Support\Carbon::now()->format('Ymd') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function getOrderNumberAttribute()
    {
        if (!empty($this->internal_order_no)) {
            return $this->internal_order_no;
        }
        return '#' . (1000 + $this->id);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Beklemede',
            'confirmed' => 'Onaylandı',
            'yeni' => 'Yeni',
            'aranacak' => 'Aranacak',
            'onaylandı' => 'Onaylandı',
            'iptal' => 'İptal',
            'kargoya_verildi' => 'Kargoya Verildi',
            'teslim_edildi' => 'Teslim Edildi',
            'iade' => 'İade',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getTrackingUrlAttribute()
    {
        if (!$this->tracking_number) return null;

        $firm = strtolower($this->cargo_firm);
        
        if (str_contains($firm, 'aras')) {
            return "https://www.araskargo.com.tr/traking?trackingNumber={$this->tracking_number}";
        } elseif (str_contains($firm, 'yurtici')) {
            return "https://www.yurticikargo.com/tr/online-servisler/gonderi-sorgula?code={$this->tracking_number}";
        } elseif (str_contains($firm, 'mng')) {
            return "https://www.mngkargo.com.tr/gonderitakip?gonderino={$this->tracking_number}";
        } elseif (str_contains($firm, 'surat')) {
            return "https://www.suratkargo.com.tr/KargoTakip/?kargo_no={$this->tracking_number}";
        } elseif (str_contains($firm, 'ptt')) {
            return "https://gonderitakip.ptt.gov.tr/Track/PttResult?gonderi_no={$this->tracking_number}";
        }

        return null;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function upsells(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderUpsell::class);
    }

    public function shipments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function histories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function affiliateAttribution()
    {
        return $this->hasOne(AffiliateOrderAttribution::class, 'order_id');
    }

    public function affiliateCommissions()
    {
        return $this->hasMany(AffiliateCommission::class, 'order_id');
    }
}
