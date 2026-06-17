<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class CargoReconciliationController extends Controller
{
    public function index(\Illuminate\Http\Request $request): \Illuminate\View\View
    {
        abort_if(!\Illuminate\Support\Facades\Auth::user()->hasPermission('cargo.view'), 403, 'Kargo mutabakat sayfasını görüntüleme yetkiniz yok.');
        
        $query = Order::with(['customer', 'domain', 'offer'])->where('status', 'teslim_edildi');

        // Date Range Filter
        $range = $request->query('range', 'all');
        $date = $request->query('date', Carbon::now()->format('Y-m-d'));

        if ($range === 'today') {
            $query->whereDate('created_at', $date);
        } elseif ($range === 'this_week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($range === 'this_month') {
            $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        } elseif ($range === 'this_year') {
            $query->whereYear('created_at', Carbon::now()->year);
        }

        // Search by Order No, Customer Name, or Phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('internal_order_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by cargo firm
        if ($request->filled('cargo_firm')) {
            $query->where('cargo_firm', $request->cargo_firm);
        }

        // Hide Reconciled logic
        $shouldHidePaid = !$request->has('filter') || $request->has('hide_paid');
        if ($shouldHidePaid) {
            $query->where('payment_status', '!=', 'reconciled');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.cargo.reconciliation', compact('orders'));
    }

    public function markAsPaid(\App\Models\Order $order): \Illuminate\Http\RedirectResponse
    {
        abort_if(!\Illuminate\Support\Facades\Auth::user()->hasPermission('cargo.edit'), 403, 'Mutabakat onaylama yetkiniz yok.');
        
        $userName = \Illuminate\Support\Facades\Auth::user()->full_name ?? \Illuminate\Support\Facades\Auth::user()->name ?? 'Sistem';
        
        $order->update([
            'payment_status' => 'reconciled',
            'reconciled_at' => \Illuminate\Support\Carbon::now(),
            'order_notes' => $order->order_notes . "\n[" . \Illuminate\Support\Carbon::now()->format('d.m.Y H:i') . "] " . $userName . ": Ödeme doğrulandı (Mutabakat yapıldı)."
        ]);

        $commission = \App\Models\AffiliateCommission::where('order_id', $order->id)->first();
        if ($commission && $commission->status === 'pending') {
            $commission->update([
                'status' => 'approved',
                'approved_at' => \Illuminate\Support\Carbon::now(),
            ]);
        }

        return redirect()->back()->with('success', '#' . $order->order_number . ' nolu siparişin ödemesi doğrulandı.');
    }

    public function unmarkAsPaid(\App\Models\Order $order): \Illuminate\Http\RedirectResponse
    {
        abort_if(!\Illuminate\Support\Facades\Auth::user()->hasPermission('cargo.edit'), 403, 'Mutabakat iptal etme yetkiniz yok.');
        
        $userName = \Illuminate\Support\Facades\Auth::user()->full_name ?? \Illuminate\Support\Facades\Auth::user()->name ?? 'Sistem';

        $order->update([
            'payment_status' => 'pending',
            'reconciled_at' => null,
            'order_notes' => $order->order_notes . "\n[" . \Illuminate\Support\Carbon::now()->format('d.m.Y H:i') . "] " . $userName . ": Ödeme doğrulaması iptal edildi."
        ]);

        $commission = \App\Models\AffiliateCommission::where('order_id', $order->id)->first();
        if ($commission && $commission->status === 'approved') {
            $commission->update([
                'status' => 'pending',
                'approved_at' => null,
            ]);
        }

        return redirect()->back()->with('error', '#' . $order->order_number . ' nolu siparişin ödeme doğrulaması geri alındı.');
    }
}
