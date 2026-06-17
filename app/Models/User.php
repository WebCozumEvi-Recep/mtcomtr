<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'custom_role_id', 'favorites'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'favorites' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        // Any user who is NOT a standard 'user' is considered an admin-panel user
        return $this->role !== UserRole::User || $this->custom_role_id !== null;
    }

    public function isGlobalAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function hasPermission(string $permission): bool
    {
        // Global admin has all permissions
        if ($this->isGlobalAdmin()) {
            return true;
        }

        if (!$this->customRole) {
            return false;
        }

        return in_array($permission, $this->customRole->permissions ?? []);
    }

    public function getFirstAllowedRoute(): string
    {
        if ($this->isGlobalAdmin() || $this->hasPermission('dashboard.view')) {
            return 'admin.dashboard';
        }

        $map = [
            'orders.view'   => 'admin.orders',
            'cargo.view'    => 'admin.cargo.reconciliation',
            'domains.view'  => 'admin.domains',
            'products.view' => 'admin.catalog',
            'brands.view'   => 'admin.brands.index',
            'users.view'    => 'admin.users',
            'settings.view' => 'admin.settings.index',
        ];

        foreach ($map as $permission => $route) {
            if ($this->hasPermission($permission)) {
                return $route;
            }
        }

        return 'login'; // Fallback
    }

    public function customRole()
    {
        return $this->belongsTo(Role::class, 'custom_role_id');
    }
}
