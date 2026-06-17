<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['full_name', 'phone', 'email', 'risk_score', 'is_blacklisted'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
