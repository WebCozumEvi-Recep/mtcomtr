<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    protected $table = 'affiliate_commissions';

    protected $fillable = [
        'affiliate_id',
        'order_id',
        'domain_id',
        'purchased_package_id',
        'affiliate_link_id',
        'channel',
        'keyword',
        'order_total',
        'commission_type_snapshot',
        'commission_amount_snapshot',
        'commission_rate_snapshot',
        'gross_commission',
        'tax_type',
        'withholding_amount',
        'vat_amount',
        'net_amount',
        'status',
        'approved_at',
        'rejected_at',
        'paid_at'
    ];

    protected $casts = [
        'order_total' => 'decimal:2',
        'commission_amount_snapshot' => 'decimal:2',
        'commission_rate_snapshot' => 'decimal:2',
        'gross_commission' => 'decimal:2',
        'withholding_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function affiliate()
    {
        return $this->belongsTo(AffiliateUser::class, 'affiliate_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function purchasedPackage()
    {
        return $this->belongsTo(Offer::class, 'purchased_package_id');
    }

    public function link()
    {
        return $this->belongsTo(AffiliateLink::class, 'affiliate_link_id');
    }

    /**
     * Commission calculation logic.
     * For 'individual', withholding is gross * 0.10. Net is gross - withholding.
     * For 'company', VAT is gross * 0.20. Net is gross + VAT.
     * For 'none', withholding and VAT are 0. Net is gross.
     */
    public static function calculateCommissionSplit(float $gross, string $taxType): array
    {
        $withholding = 0.00;
        $vat = 0.00;
        $net = $gross;

        $withholdingRate = (float) Setting::val('affiliate_withholding_rate', 20.0);
        $vatRate = (float) Setting::val('affiliate_vat_rate', 20.0);

        if ($taxType === 'individual') {
            $withholding = round($gross * ($withholdingRate / 100), 2);
            $net = round($gross - $withholding, 2);
        } elseif ($taxType === 'company') {
            $vat = round($gross * ($vatRate / 100), 2);
            $net = round($gross + $vat, 2);
        }

        return [
            'gross' => $gross,
            'tax_type' => $taxType,
            'withholding' => $withholding,
            'vat' => $vat,
            'net' => $net
        ];
    }
}
