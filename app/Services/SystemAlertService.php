<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Order;
use App\Models\Product;
use App\Models\Offer;
use App\Models\SystemAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class SystemAlertService
{
    public function getCriticalAlerts()
    {
        $criticalAlerts = [];

        // 1. Domain Alarms
        $domains = Domain::with(['latestOrder'])->get();
        foreach ($domains as $domain) {
            $lastOrder = $domain->latestOrder;
            if ($lastOrder && $lastOrder->created_at->diffInHours() >= 2) {
                $criticalAlerts[] = [
                    'key' => 'da_' . $domain->domain_name . '_Siparis_Durdu',
                    'message' => '<strong>' . $domain->domain_name . '</strong>: Sipariş Durdu (2s)',
                    'type' => 'warning'
                ];
            }
            
            if ($domain->unique_visitor_count > 100) {
                $orderCount = $domain->orders()->count();
                if (($orderCount / $domain->unique_visitor_count) < 0.01) {
                    $criticalAlerts[] = [
                        'key' => 'da_' . $domain->domain_name . '_Dusuk_Donusum',
                        'message' => '<strong>' . $domain->domain_name . '</strong>: Düşük Dönüşüm',
                        'type' => 'warning'
                    ];
                }
            }
        }

        // 2. Delayed Cargo
        $delayedCargoCount = Order::where('status', 'kargoya_verildi')
            ->where('updated_at', '<', Carbon::now()->subDays(3))
            ->count();
        if ($delayedCargoCount > 0) {
            $criticalAlerts[] = [
                'key' => 'dc_delayed_cargo',
                'message' => '<strong>' . $delayedCargoCount . '</strong> sipariş 3 gündür teslim edilmedi.',
                'type' => 'danger'
            ];
        }

        // 3. Unread System Alerts
        $unreadSystemAlerts = SystemAlert::whereDoesntHave('users', function($q) {
            $q->where('users.id', Auth::id());
        })->latest()->take(10)->get();

        foreach($unreadSystemAlerts as $sa) {
            $criticalAlerts[] = [
                'id' => $sa->id,
                'key' => 'sa_' . $sa->id,
                'message' => $sa->message,
                'type' => $sa->type,
                'is_system' => true
            ];
        }

        // 4. Consistency Checks
        // Domains without brand
        $noBrandDomains = Domain::whereNull('brand_id')->get();
        foreach($noBrandDomains as $d) {
            $criticalAlerts[] = [
                'key' => 'db_nobrand_' . $d->id,
                'message' => '<strong>' . $d->domain_name . '</strong>: Marka atanmamış!',
                'type' => 'danger'
            ];
        }

        // Products without image or SKU
        $badProducts = Product::whereNull('image_url')
            ->orWhere('image_url', '')
            ->orWhereNull('sku')
            ->orWhere('sku', '')
            ->get();

        foreach($badProducts as $p) {
            $missing = [];
            if (!$p->getRawOriginal('image_url')) $missing[] = 'Görsel';
            if (!$p->sku) $missing[] = 'SKU';
            
            if (!empty($missing)) {
                $criticalAlerts[] = [
                    'key' => 'prod_alert_' . $p->id,
                    'message' => '<strong>Ürün: ' . $p->name . '</strong>: ' . implode(', ', $missing) . ' eksik!',
                    'type' => 'warning'
                ];
            }
        }

        // Offers with missing 2nd image or item price/qty
        $badOffers = Offer::whereNull('active_image')
            ->orWhereHas('items', function($q) {
                $q->where('price', '<=', 0)->orWhere('quantity', '<=', 0);
            })
            ->with(['domain'])
            ->get();

        foreach($badOffers as $o) {
            $missing = [];
            if (!$o->active_image) $missing[] = '2. Görsel';
            
            // Check items for specific error detail if needed, but the query already found them
            $hasBadItems = $o->items()->where('price', '<=', 0)->orWhere('quantity', '<=', 0)->exists();
            if ($hasBadItems) $missing[] = 'Paket Kalemi Hatalı';

            $criticalAlerts[] = [
                'key' => 'off_alert_' . $o->id,
                'message' => '<strong>Paket: ' . $o->offer_name . ' (' . ($o->domain->domain_name ?? 'Bilinmeyen') . ')</strong>: ' . implode(', ', $missing) . ' tanımlanmamış!',
                'type' => 'warning'
            ];
        }

        return array_reverse($criticalAlerts);
    }
}
