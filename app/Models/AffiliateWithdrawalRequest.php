<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateWithdrawalRequest extends Model
{
    protected $table = 'affiliate_withdrawal_requests';

    protected $fillable = [
        'affiliate_id',
        'requested_amount',
        'gross_amount',
        'withholding_amount',
        'vat_amount',
        'net_payment',
        'status',
        'iban',
        'admin_note',
        'payment_receipt',
        'paid_at'
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'withholding_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'net_payment' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function affiliate()
    {
        return $this->belongsTo(AffiliateUser::class, 'affiliate_id');
    }
}
