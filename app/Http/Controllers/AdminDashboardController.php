<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Domain;
use App\Models\Product;
use App\Models\DomainExpense;

class AdminDashboardController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (!auth()->user()->isGlobalAdmin() && !auth()->user()->hasPermission('dashboard.view')) {
            return redirect()->route(auth()->user()->getFirstAllowedRoute());
        }
        
        $range = request()->query('range', 'today');
        $date = now()->format('Y-m-d');
        $startDate = request()->query('start_date');
        $endDate = request()->query('end_date');
        
        $query = Order::query();
        $expenseQuery = DomainExpense::query();
        $reconciliationQuery = Order::where('payment_status', 'reconciled');

        $rangeLabel = 'Dönemlik';
        if ($range === 'today') {
            $query->whereDate('created_at', $date);
            $expenseQuery->whereDate('spent_at', $date);
            $reconciliationQuery->whereDate('reconciled_at', $date);
            $rangeLabel = 'Bugünkü';
        } elseif ($range === 'this_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $expenseQuery->whereBetween('spent_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $reconciliationQuery->whereBetween('reconciled_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $rangeLabel = 'Haftalık';
        } elseif ($range === 'this_month') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            $expenseQuery->whereMonth('spent_at', now()->month)->whereYear('spent_at', now()->year);
            $reconciliationQuery->whereMonth('reconciled_at', now()->month)->whereYear('reconciled_at', now()->year);
            $rangeLabel = 'Aylık';
        } elseif ($range === 'this_year') {
            $query->whereYear('created_at', now()->year);
            $expenseQuery->whereYear('spent_at', now()->year);
            $reconciliationQuery->whereYear('reconciled_at', now()->year);
            $rangeLabel = 'Yıllık';
        } elseif ($range === 'custom') {
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                $expenseQuery->whereBetween('spent_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                $reconciliationQuery->whereBetween('reconciled_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
                $expenseQuery->whereDate('spent_at', '>=', $startDate);
                $reconciliationQuery->whereDate('reconciled_at', '>=', $startDate);
            } elseif ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
                $expenseQuery->whereDate('spent_at', '<=', $endDate);
                $reconciliationQuery->whereDate('reconciled_at', '<=', $endDate);
            }
            $rangeLabel = 'Özel';
        } else {
            $rangeLabel = 'Genel';
        }

        // 1. ÜST KPI ALANI
        $todayOrdersCount = (clone $query)->count();
        $approvedOrdersCount = (clone $query)->whereIn('status', ['onaylandı', 'kargoya_verildi', 'teslim_edildi'])->count();
        $deliveredOrdersCount = (clone $query)->where('status', 'teslim_edildi')->count();
        
        $todayRevenue = (clone $query)->where('status', '!=', 'iptal')->sum('grand_total');
        $approvedRevenue = (clone $query)->whereIn('status', ['onaylandı', 'kargoya_verildi', 'teslim_edildi'])->sum('grand_total');
        $codPaidTotal = $reconciliationQuery->sum('grand_total');
        $codPendingTotal = (clone $query)->whereIn('status', ['kargoya_verildi', 'teslim_edildi'])->where('payment_status', 'pending')->sum('grand_total');
        $marketingExpenses = $expenseQuery->sum('amount');
        
        $productOrders = (clone $query)->where('status', '!=', 'iptal')->with('offer.product')->get();
        $productCost = $productOrders->sum(function($order) {
            return ($order->offer->quantity ?? 1) * ($order->offer->product->cost_price ?? 0);
        });
            
        $shippingCost = (clone $query)->where('status', '!=', 'iptal')->sum('shipping_cost');
        
        // Upsell Metrics
        $upsellRevenue = (clone $query)->where('status', '!=', 'iptal')->sum('upsell_total');
        $upsellOrderCount = (clone $query)->where('status', '!=', 'iptal')->where('has_upsell', true)->count();
        $upsellConversionRate = $todayOrdersCount > 0 ? ($upsellOrderCount / $todayOrdersCount) * 100 : 0;

        $netProfit = $todayRevenue - $productCost - $shippingCost - $marketingExpenses;

        $activeDomainsCount = Domain::where('is_active', true)->count();
        $pendingApprovalCount = Order::whereIn('status', ['yeni', 'pending'])->count();
        $fraudOrdersCount = Order::where('fraud_score', '>', 50)->count();

        // 2. SATIŞ AKIŞ BLOĞU (Conversion Funnel)
        $funnel = [
            'visitor' => Domain::sum('unique_visitor_count'),
            'total_hits' => Domain::sum('visitor_count'),
            'orders' => $todayOrdersCount,
            'approved' => $approvedOrdersCount,
            'shipped' => (clone $query)->whereIn('status', ['kargoya_verildi', 'teslim_edildi'])->count(),
            'delivered' => $deliveredOrdersCount,
            'paid' => $reconciliationQuery->count(), 
        ];

        // 3. DOMAIN PERFORMANS TABLOSU
        $domainStats = Domain::with(['products', 'offers', 'latestOrder'])
            ->get()
            ->map(function($domain) use ($range, $date, $startDate, $endDate) {
                // Pre-filter relationships for calculations
                $orderQuery = $domain->orders();
                $expenseQuery = $domain->expenses();
                $eventQuery = $domain->funnelEvents();
                
                if ($range === 'today') {
                    $orderQuery->whereDate('created_at', $date);
                    $expenseQuery->whereDate('spent_at', $date);
                    $eventQuery->whereDate('created_at', $date);
                } elseif ($range === 'this_week') {
                    $start = now()->startOfWeek(); $end = now()->endOfWeek();
                    $orderQuery->whereBetween('created_at', [$start, $end]);
                    $expenseQuery->whereBetween('spent_at', [$start, $end]);
                    $eventQuery->whereBetween('created_at', [$start, $end]);
                } elseif ($range === 'this_month') {
                    $orderQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    $expenseQuery->whereMonth('spent_at', now()->month)->whereYear('spent_at', now()->year);
                    $eventQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                } elseif ($range === 'this_year') {
                    $orderQuery->whereYear('created_at', now()->year);
                    $expenseQuery->whereYear('spent_at', now()->year);
                    $eventQuery->whereYear('created_at', now()->year);
                } elseif ($range === 'custom') {
                    if ($startDate && $endDate) {
                        $orderQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                        $expenseQuery->whereBetween('spent_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                        $eventQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    } elseif ($startDate) {
                        $orderQuery->whereDate('created_at', '>=', $startDate);
                        $expenseQuery->whereDate('spent_at', '>=', $startDate);
                        $eventQuery->whereDate('created_at', '>=', $startDate);
                    } elseif ($endDate) {
                        $orderQuery->whereDate('created_at', '<=', $endDate);
                        $expenseQuery->whereDate('spent_at', '<=', $endDate);
                        $eventQuery->whereDate('created_at', '<=', $endDate);
                    }
                }

                $orders = $orderQuery->with('offer.product')->get();
                $expenses = $expenseQuery->get();
                $events = $eventQuery->get();
                
                $orderCount = $orders->count();
                $cost = $orders->sum(function($o){ return ($o->offer->quantity ?? 0) * ($o->offer->product->cost_price ?? 0); });
                $ship = $orders->sum('shipping_cost');
                $mkt = $expenses->sum('amount');
                $rev = $orders->where('status', '!=' , 'iptal')->sum('grand_total');
                
                $v_range = $events->where('event_type', 'page_view')->unique('session_id')->count();
                $h_range = $events->where('event_type', 'page_view')->count();
                $f_unique = $events->where('event_type', 'form_open')->unique('session_id')->count();
                $f_total = $events->where('event_type', 'form_open')->count();
                $s_50 = $events->where('event_type', 'scroll_50')->unique('session_id')->count();

                $approved = $orders->whereIn('status', ['onaylandı', 'kargoya_verildi', 'teslim_edildi'])->count();
                $approved_rev = $orders->whereIn('status', ['onaylandı', 'kargoya_verildi', 'teslim_edildi'])->sum('grand_total');
                $delivered = $orders->where('status', 'teslim_edildi')->count();
                $paidCount = $orders->where('payment_status', 'reconciled')->count();
                $fraudCount = $orders->where('fraud_score', '>', 50)->count();

                $lastOrder = $domain->latestOrder;

                $alarms = [];
                if ($lastOrder && $lastOrder->created_at->diffInHours() >= 2) $alarms[] = 'Sipariş Durdu (2s)';
                if ($v_range > 100 && ($orderCount/$v_range) < 0.01) $alarms[] = 'Düşük Dönüşüm';
                if ($fraudCount > 0) $alarms[] = $fraudCount . ' Şüpheli Sipariş';

                $totalQty = $orders->sum(function($o){ return $o->offer->quantity ?? 0; });
                $avgPrice = $totalQty > 0 ? ($rev / $totalQty) : 0;
                $aov = $orderCount > 0 ? ($rev / $orderCount) : 0;

                return [
                    'id' => $domain->id,
                    'name' => $domain->domain_name,
                    'product' => $domain->products->first()->name ?? 'N/A',
                    'visitors' => $v_range > 0 ? $v_range : ($domain->unique_visitor_count ?? 0),
                    'hits' => $h_range > 0 ? $h_range : ($domain->visitor_count ?? 0),
                    'form_open_unique' => $f_unique,
                    'form_open_total' => $f_total,
                    'scroll_50' => $s_50,
                    'orders' => $orderCount,
                    'approved_count' => $approved,
                    'cr' => $v_range > 0 ? ($orderCount / $v_range) * 100 : (($domain->unique_visitor_count ?? 0) > 0 ? ($orderCount / $domain->unique_visitor_count) * 100 : 0),
                    'approve_rate' => $orderCount > 0 ? ($approved / $orderCount) * 100 : 0,
                    'delivery_rate' => $approved > 0 ? ($delivered / $approved) * 100 : 0,
                    'collection_rate' => $delivered > 0 ? ($paidCount / $delivered) * 100 : 0,
                    'revenue' => $rev,
                    'approved_revenue' => $approved_rev,
                    'net_profit' => $rev - $cost - $ship - $mkt,
                    'avg_price' => $avgPrice,
                    'aov' => $aov,
                    'last_order_at' => $lastOrder ? $lastOrder->created_at->format('H:i') : 'Yok',
                    'status' => $domain->is_active ? 'Aktif' : 'Pasif',
                    'alarms' => $alarms,
                    'fraud_score' => $orderCount > 0 ? ($fraudCount / $orderCount) * 100 : 0
                ];
            });

        $criticalAlerts = app(\App\Services\SystemAlertService::class)->getCriticalAlerts();

        $recentOrders = Order::with(['customer', 'domain', 'offer.product'])
            ->whereIn('status', ['yeni', 'aranacak', 'onaylandı'])
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'orders_page');

        // General System Health Score (100 - average risk)
        $systemRisk = $domainStats->avg('fraud_score') ?? 0;
        $systemHealth = 100 - $systemRisk;

        // Gelişmiş Funnel Analizi (Interaction Based)
        $eventQuery = \App\Models\FunnelEvent::query();
        if ($range === 'today') {
            $eventQuery->whereDate('created_at', $date);
        } elseif ($range === 'this_week') {
            $eventQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($range === 'this_month') {
            $eventQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        } elseif ($range === 'this_year') {
            $eventQuery->whereYear('created_at', now()->year);
        } elseif ($range === 'custom') {
            if ($startDate && $endDate) {
                $eventQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($startDate) {
                $eventQuery->whereDate('created_at', '>=', $startDate);
            } elseif ($endDate) {
                $eventQuery->whereDate('created_at', '<=', $endDate);
            }
        }

        $advancedFunnel = [
            'page_view' => (clone $eventQuery)->where('event_type', 'page_view')->distinct('session_id')->count('session_id'),
            'total_hits' => (clone $eventQuery)->where('event_type', 'page_view')->count(),
            'scroll_50' => (clone $eventQuery)->where('event_type', 'scroll_50')->distinct('session_id')->count('session_id'),
            'cta_click' => (clone $eventQuery)->where('event_type', 'cta_click')->distinct('session_id')->count('session_id'),
            'form_open' => (clone $eventQuery)->where('event_type', 'form_open')->distinct('session_id')->count('session_id'),
            'order' => (clone $eventQuery)->where('event_type', 'order_complete')->distinct('session_id')->count('session_id'),
        ];

        // AI Recommendations
        $aiSuggestions = [];
        if ($advancedFunnel['page_view'] > 0) {
            $ctaRate = ($advancedFunnel['cta_click'] / $advancedFunnel['page_view']) * 100;
            $scrollRate = ($advancedFunnel['scroll_50'] / $advancedFunnel['page_view']) * 100;

            if ($ctaRate < 10) {
                $aiSuggestions[] = [
                    'type' => 'warning',
                    'title' => 'CTA Tıklama Oranı Düşük (%' . number_format($ctaRate, 1) . ')',
                    'message' => 'Buton renklerini daha dikkat çekici yapmayı ve metni aksiyon odaklı değiştirmeyi deneyin.'
                ];
            }

            if ($scrollRate < 40) {
                $aiSuggestions[] = [
                    'type' => 'warning',
                    'title' => 'Kaydırma Oranı Düşük (%' . number_format($scrollRate, 1) . ')',
                    'message' => 'Kullanıcılar sayfanın aşağısına inmiyor. İlk görsel (Hero) zayıf olabilir.'
                ];
            }
            
            if ($advancedFunnel['form_open'] > 0) {
                $formSubmitRate = ($advancedFunnel['order'] / $advancedFunnel['form_open']) * 100;
                if ($formSubmitRate < 30) {
                    $aiSuggestions[] = [
                        'type' => 'danger',
                        'title' => 'Form Terk Etme Oranı Yüksek',
                        'message' => 'Kullanıcılar formu açıyor ama doldurmuyor. Formu sadeleştirin.'
                    ];
                }
            }
        }

        $criticalAlerts = array_reverse($criticalAlerts);

        return view('admin.dashboard', compact(
            'todayOrdersCount', 'approvedOrdersCount', 'deliveredOrdersCount',
            'todayRevenue', 'codPaidTotal', 'codPendingTotal', 'marketingExpenses', 'netProfit',
            'activeDomainsCount', 'pendingApprovalCount', 'fraudOrdersCount',
            'funnel', 'domainStats', 'criticalAlerts', 'recentOrders', 'rangeLabel', 'systemHealth',
            'advancedFunnel', 'aiSuggestions', 'approvedRevenue', 'upsellRevenue', 'upsellOrderCount', 'upsellConversionRate'
        ));
    }
}
