<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'carrier_company', 'tracking_code', 'status', 'reconciliation_status', 'raw_api_response'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
