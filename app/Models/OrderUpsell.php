<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderUpsell extends Model
{
    protected $fillable = [
        'order_id', 'upsell_offer_id', 'operator_id', 'status', 'old_total',
        'new_total', 'added_amount', 'accepted_at', 'rejected_at'
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    protected $casts = [
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'old_total' => 'decimal:2',
        'new_total' => 'decimal:2',
        'added_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(UpsellOffer::class, 'upsell_offer_id');
    }
}
