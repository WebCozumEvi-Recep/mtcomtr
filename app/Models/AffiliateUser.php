<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AffiliateUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'affiliate_users';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'affiliate_code',
        'status',
        'tax_type',
        'iban',
        'tax_office',
        'tax_number',
        'company_name',
        'address'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function links()
    {
        return $this->hasMany(AffiliateLink::class, 'affiliate_id');
    }

    public function clicks()
    {
        return $this->hasMany(AffiliateClick::class, 'affiliate_id');
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class, 'affiliate_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(AffiliateWithdrawalRequest::class, 'affiliate_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
