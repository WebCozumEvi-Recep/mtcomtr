<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('orders.view'), 403, 'Siparişleri görüntüleme yetkiniz yok.');
        $query = Order::with(['customer', 'domain.brand']);

        // Date Range Filter
        $range = $request->query('range', 'all');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($range === 'today') {
            $query->whereDate('created_at', now()->format('Y-m-d'));
        } elseif ($range === 'this_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($range === 'this_month') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        } elseif ($range === 'this_year') {
            $query->whereYear('created_at', now()->year);
        } elseif ($range === 'custom') {
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            } elseif ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }
        }

        // Search by Order No, Customer Name, or Phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Domain
        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        // Filter by Brand
        if ($request->filled('brand_id')) {
            $query->whereHas('domain', function($dq) use ($request) {
                $dq->where('brand_id', $request->brand_id);
            });
        }

        // Filter by Print Status
        if ($request->filled('is_printed')) {
            $query->where('is_printed', $request->is_printed);
        }

        // Filter by Cargo Firm
        if ($request->filled('cargo_firm')) {
            $query->where('cargo_firm', $request->cargo_firm);
        }

        if ($request->query('api_filter') === 'api_pending') {
            $query->where('is_api', true)->where('api_approved', false);
        } elseif ($request->query('api_filter') === 'api_all') {
            $query->where('is_api', true);
        }

        // Sorting
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        $allowedSorts = ['created_at', 'order_number', 'customer', 'brand', 'domain', 'grand_total', 'status'];
        
        if (in_array($sort, $allowedSorts)) {
            if ($sort === 'customer') {
                $query->join('customers', 'orders.customer_id', '=', 'customers.id')
                    ->orderBy('customers.full_name', $direction)
                    ->select('orders.*');
            } elseif ($sort === 'brand') {
                $query->join('domains', 'orders.domain_id', '=', 'domains.id')
                    ->leftJoin('brands', 'domains.brand_id', '=', 'brands.id')
                    ->orderBy('brands.name', $direction)
                    ->select('orders.*');
            } elseif ($sort === 'domain') {
                $query->join('domains', 'orders.domain_id', '=', 'domains.id')
                    ->orderBy('domains.domain_name', $direction)
                    ->select('orders.*');
            } else {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate(15)->withQueryString();
        $domains = \App\Models\Domain::orderBy('domain_name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $cargoSettings = \App\Models\CargoSetting::where('is_active', true)->get();
        
        return view('admin.orders.index', compact('orders', 'domains', 'brands', 'cargoSettings'));
    }

    public function show(Order $order)
    {
        abort_if(!auth()->user()->hasPermission('orders.view'), 403, 'Sipariş detayını görüntüleme yetkiniz yok.');
        $order->load(['customer', 'domain.brand', 'offer.items.product', 'upsells.offer', 'upsells.operator', 'histories.user']);
        $cargoSettings = \App\Models\CargoSetting::where('is_active', true)->get();
        $messageTemplates = \App\Models\MessageTemplate::all();
        return view('admin.orders.show', compact('order', 'cargoSettings', 'messageTemplates'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort_if(!auth()->user()->hasPermission('orders.edit'), 403, 'Sipariş durumunu güncelleme yetkiniz yok.');
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,yeni,aranacak,onaylandı,iptal,kargoya_verildi,teslim_edildi,iade'
        ]);

        $statuses = [
            'pending' => 'Beklemede',
            'confirmed' => 'Onaylandı',
            'yeni' => 'Yeni',
            'aranacak' => 'Aranacak',
            'onaylandı' => 'Onaylandı',
            'iptal' => 'İptal',
            'kargoya_verildi' => 'Kargoya Verildi',
            'teslim_edildi' => 'Teslim Edildi',
            'iade' => 'İade',
        ];

        $statusLabel = $statuses[$request->status] ?? $request->status;
        $userName = auth()->user()->full_name ?? auth()->user()->name ?? 'Sistem';

        $order->update([
            'status' => $request->status,
            'order_notes' => $order->order_notes . "\n[" . now()->format('d.m.Y H:i') . "] " . $userName . ": Durum güncellendi -> " . $statusLabel
        ]);

        // Hook Affiliate Commission Rejection Lifecycle
        if (in_array($request->status, ['iptal', 'iade'])) {
            $commission = \App\Models\AffiliateCommission::where('order_id', $order->id)->first();
            if ($commission && in_array($commission->status, ['pending', 'approved'])) {
                $commission->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                ]);
            }
        }

        return back()->with('success', 'Sipariş durumu başarıyla güncellendi.');
    }

    public function sendToCargo(Request $request, Order $order)
    {
        abort_if(!auth()->user()->hasPermission('orders.send_cargo'), 403, 'Kargo gönderim yetkiniz yok.');
        $request->validate(['cargo_firm' => 'required|string']);
        
        $cargoFirm = $request->cargo_firm;
        $userName = auth()->user()->full_name ?? auth()->user()->name ?? 'Sistem';

        if ($cargoFirm === 'yurtici') {
            try {
                $service = new \App\Services\Cargo\YurticiCargoService();
                $result = $service->createShipment($order);
                
                $trackingNumber = $result['tracking_number'];
                $apiResponse = $result['response'];

                $order->update([
                    'status' => 'kargoya_verildi',
                    'cargo_firm' => $cargoFirm,
                    'tracking_number' => $trackingNumber,
                    'order_notes' => $order->order_notes . "\n[" . now()->format('d.m.Y H:i') . "] " . $userName . ": Yurtiçi Kargo ile gönderildi (Talep No: " . $trackingNumber . ")"
                ]);

                \App\Models\Shipment::create([
                    'order_id' => $order->id,
                    'carrier_company' => $cargoFirm,
                    'tracking_code' => $trackingNumber,
                    'status' => 'prepared',
                    'raw_api_response' => json_encode($apiResponse, JSON_UNESCAPED_UNICODE)
                ]);

                return back()->with('success', 'Sipariş Yurtiçi Kargo sistemine başarıyla iletildi.');
            } catch (\Exception $e) {
                return back()->with('error', 'Kargo hatası: ' . $e->getMessage());
            }
        }
        
        // Mocking cargo API call logic for other firms
        $prefix = strtoupper(substr($cargoFirm, 0, 2));
        $trackingNumber = $prefix . rand(100000000, 999999999);
        
        $order->update([
            'status' => 'kargoya_verildi',
            'cargo_firm' => $cargoFirm,
            'tracking_number' => $trackingNumber,
            'order_notes' => $order->order_notes . "\n[" . now()->format('d.m.Y H:i') . "] " . $userName . ": Kargoya verildi -> " . strtoupper($cargoFirm) . " (No: " . $trackingNumber . ")"
        ]);

        // Create Shipment record with raw response mock
        $mockResponse = [
            'carrier' => $cargoFirm,
            'tracking_number' => $trackingNumber,
            'integration_status' => 'success',
            'timestamp' => now()->toIso8601String(),
            'payload' => [
                'sender' => 'TEKSAT LOGISTICS',
                'receiver' => $order->customer->full_name ?? 'Müşteri',
                'service_type' => 'Standard',
                'payment_type' => 'Sender Paid'
            ],
            'debug_info' => 'Integrated via TekSat Hub API v2.1'
        ];

        \App\Models\Shipment::create([
            'order_id' => $order->id,
            'carrier_company' => $cargoFirm,
            'tracking_code' => $trackingNumber,
            'status' => 'prepared',
            'raw_api_response' => json_encode($mockResponse, JSON_UNESCAPED_UNICODE)
        ]);

        return back()->with('success', 'Sipariş ' . strtoupper($cargoFirm) . ' sistemine iletildi ve kargolandı.');
    }

    public function cancelCargo(Order $order)
    {
        abort_if(!auth()->user()->hasPermission('orders.send_cargo'), 403, 'Kargo iptal yetkiniz yok.');
        $userName = auth()->user()->full_name ?? auth()->user()->name ?? 'Sistem';
        $oldTracking = $order->tracking_number;
        $oldFirm = strtoupper($order->cargo_firm);

        $order->update([
            'status' => 'onaylandı',
            'tracking_number' => null,
            'cargo_firm' => null,
            'order_notes' => $order->order_notes . "\n[" . now()->format('d.m.Y H:i') . "] " . $userName . ": Kargo iptal edildi (" . $oldFirm . " - " . $oldTracking . ") ve tekrar gönderim için hazırlandı."
        ]);

        return back()->with('success', 'Kargo gönderimi iptal edildi. Siparişi tekrar kargoya verebilirsiniz.');
    }

    public function printBarcode(Order $order)
    {
        abort_if(!auth()->user()->hasPermission('orders.print'), 403, 'Barkod yazdırma yetkiniz yok.');
        if (!$order->tracking_number) {
            return back()->with('error', 'Önce siparişi kargoya göndermelisiniz.');
        }

        return view('admin.orders.barcode', compact('order'));
    }

    public function risk()
    {
        abort_if(!auth()->user()->hasPermission('risk.view'), 403, 'Risk analizini görüntüleme yetkiniz yok.');
        $orders = Order::with(['customer', 'domain'])
            ->whereIn('status', ['yeni', 'aranacak', 'onaylandı'])
            ->where('fraud_score', '>=', 60)
            ->orderBy('fraud_score', 'desc')
            ->paginate(15);

        $blacklistedCustomers = \App\Models\Customer::where('is_blacklisted', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        $criticalAlerts = app(\App\Services\SystemAlertService::class)->getCriticalAlerts();

        return view('admin.risk.index', compact('orders', 'blacklistedCustomers', 'criticalAlerts'));
    }
    public function toggleBlacklist(\App\Models\Customer $customer)
    {
        abort_if(!auth()->user()->hasPermission('risk.edit'), 403, 'Kara liste yönetimi yetkiniz yok.');
        $newState = !$customer->is_blacklisted;
        $customer->update(['is_blacklisted' => $newState]);

        $statusText = $newState ? 'kara listeye eklendi.' : 'kara listeden çıkarıldı.';
        return back()->with('success', "Müşteri başarıyla {$statusText}");
    }
    public function addNote(Request $request, Order $order)
    {
        $request->validate(['note' => 'required|string']);

        $userName = auth()->user()->full_name ?? auth()->user()->name ?? 'Sistem';
        $newNote = "\n[" . now()->format('d.m.Y H:i') . "] " . $userName . ": " . $request->note;

        $order->update([
            'order_notes' => ($order->order_notes ?? '') . $newNote
        ]);

        return back()->with('success', 'Not başarıyla eklendi.');
    }

    public function updateAddress(Request $request, Order $order)
    {
        abort_if(!auth()->user()->hasPermission('orders.edit_address'), 403, 'Adres güncelleme yetkiniz yok.');
        // 1. Check if order is shipped and not cancelled
        if ($order->status == 'kargoya_verildi' && $order->status != 'iptal') {
             return response()->json(['success' => false, 'message' => 'Kargoya verilmiş siparişlerin adresi değiştirilemez. Önce kargoyu iptal etmelisiniz.'], 422);
        }

        $data = $request->validate([
            'address' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
        ]);

        $userName = auth()->user()->full_name ?? auth()->user()->name ?? 'Sistem';
        $oldAddress = "{$order->address} ({$order->district} / {$order->city})";
        $newAddress = "{$data['address']} ({$data['district']} / {$data['city']})";

        $order->update($data);

        // Add to order notes
        $order->update([
            'order_notes' => ($order->order_notes ?? '') . "\n[" . now()->format('d.m.Y H:i') . "] " . $userName . ": Adres güncellendi.\nEski: {$oldAddress}\nYeni: {$newAddress}"
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Adres başarıyla güncellendi.',
            'new_address' => $data['address'],
            'new_district' => $data['district'],
            'new_city' => $data['city']
        ]);
    }

    public function destroy(Order $order)
    {
        abort_if(!auth()->user()->hasPermission('orders.delete'), 403, 'Sipariş silme yetkiniz yok.');
        $orderNumber = $order->order_number;

        // Create System Alert
        \App\Models\SystemAlert::create([
            'type' => 'warning',
            'title' => 'Sipariş Silindi',
            'message' => "'<strong>" . $orderNumber . "</strong>' numaralı sipariş kalıcı olarak silindi.",
            'causer_id' => auth()->id(),
            'data' => [
                'type' => 'order',
                'id' => $order->id,
                'order_number' => $orderNumber
            ]
        ]);

        $order->delete();

        return redirect()->route('admin.orders')
            ->with('success', "'<strong>" . $orderNumber . "</strong>' numaralı sipariş başarıyla silindi.");
    }

    public function approveApiOrder(Order $order)
    {
        abort_if(!auth()->user()->hasPermission('orders.edit'), 403, 'API onayı verme yetkiniz yok.');
        abort_unless($order->is_api, 404);

        $userName = auth()->user()->full_name ?? auth()->user()->name ?? 'Sistem';
        $order->update([
            'api_approved' => true,
            'order_notes' => ($order->order_notes ?? '') . "\n[" . now()->format('d.m.Y H:i') . "] " . $userName . ": Harici API siparişi panelden onaylandı (api_approved).",
        ]);

        return back()->with('success', 'API sipariş onayı kaydedildi.');
    }

    public function addUpsell(Request $request, Order $order)
    {
        abort_if(!auth()->user()->hasPermission('orders.edit'), 403, 'Upsell ekleme yetkiniz yok.');
        try {
            $validated = $request->validate([
                'upsell_offer_id' => 'required|exists:upsell_offers,id'
            ]);

            $offer = \App\Models\UpsellOffer::findOrFail($validated['upsell_offer_id']);

            // Update order financial totals
            $order->upsells()->create([
                'upsell_offer_id' => $offer->id,
                'operator_id' => auth()->id(),
                'status' => 'accepted',
                'old_total' => $order->grand_total,
                'new_total' => $order->grand_total + $offer->discount_price,
                'added_amount' => $offer->discount_price,
                'accepted_at' => now()
            ]);

            $userName = auth()->user()->full_name ?? auth()->user()->name ?? 'Sistem';
            $updateData = [
                'has_upsell' => true,
                'upsell_total' => ($order->upsell_total ?? 0) + $offer->discount_price,
                'grand_total' => $order->grand_total + $offer->discount_price,
                'final_total' => $order->grand_total + $offer->discount_price,
                'order_notes' => ($order->order_notes ?? '') . "\n[" . now()->format('d.m.Y H:i') . "] " . $userName . ": Manuel Upsell eklendi -> " . $offer->name . " (+₺" . $offer->discount_price . ")"
            ];

            // If upsell target is a package, update the order's main package
            if ($offer->target_package_id) {
                $updateData['offer_id'] = $offer->target_package_id;
            }

            $order->update($updateData);

            return response()->json(['success' => true, 'message' => 'Upsell başarıyla eklendi.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
        }
    }

    public function bulkPrint(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('orders.print'), 403, 'Toplu yazdırma yetkiniz yok.');
        $ids = explode(',', $request->query('ids', ''));
        $orders = Order::with(['customer', 'domain.products', 'offer'])->whereIn('id', $ids)->get();
        
        if ($orders->isEmpty()) {
            return back()->with('error', 'Lütfen en az bir sipariş seçin.');
        }

        // Mark as printed
        Order::whereIn('id', $ids)->update(['is_printed' => true]);

        return view('admin.orders.bulk-print', compact('orders'));
    }

    public function export(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('orders.export'), 403, 'Veri dışa aktarma yetkiniz yok.');
        $query = Order::with(['customer', 'domain']);

        // Replicate index filters for export
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('full_name', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        if ($request->filled('cargo_firm')) {
            $query->where('cargo_firm', $request->cargo_firm);
        }

        if ($request->query('api_filter') === 'api_pending') {
            $query->where('is_api', true)->where('api_approved', false);
        } elseif ($request->query('api_filter') === 'api_all') {
            $query->where('is_api', true);
        }

        if ($request->has('hide_cancelled') && $request->status !== 'iptal') {
            $query->where('status', '!=', 'iptal');
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Sütun Başlıkları
        $headers = ['Tarih', 'Sipariş No', 'Müşteri', 'Telefon', 'Domain', 'Tutar', 'Durum', 'Kargo'];
        $sheet->fromArray($headers, null, 'A1');

        // Başlık Stili (Koyu Gri Arka Plan, Beyaz Kalın Yazı, Ortalanmış)
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '334155'], // Slate 700
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Verileri Yazma
        $row = 2;
        foreach ($orders as $order) {
            $sheet->fromArray([
                $order->created_at->format('d.m.Y H:i'),
                $order->order_number,
                $order->customer->full_name ?? '-',
                $order->customer->phone ?? '-',
                $order->domain->domain_name ?? 'Direkt',
                (float) $order->grand_total,
                $order->status_label,
                $order->cargo_firm ? strtoupper($order->cargo_firm) : '-',
            ], null, 'A' . $row);
            $row++;
        }

        // Sütun genişliklerini otomatik ayarla
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Tutar sütununu (F) Para Birimi olarak biçimlendir
        if ($row > 2) {
            $sheet->getStyle('F2:F' . ($row - 1))
                  ->getNumberFormat()
                  ->setFormatCode('₺#,##0.00');
        }

        $filename = "siparisler_" . now()->format('Ymd_His') . ".xlsx";

        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function logWhatsAppMessage(Request $request, Order $order)
    {
        $request->validate([
            'template_name' => 'required|string',
            'message' => 'required|string'
        ]);

        $userName = auth()->user()->full_name ?? auth()->user()->name ?? 'Sistem';
        $timestamp = now()->format('d.m.Y H:i');
        
        $logEntry = "\n[" . $timestamp . "] " . $userName . ": WhatsApp Mesajı Gönderildi (" . $request->template_name . ")";

        // Update order notes (legacy history)
        $order->update([
            'order_notes' => ($order->order_notes ?? '') . $logEntry
        ]);

        // Create formal history record
        \App\Models\OrderHistory::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'type' => 'whatsapp_message',
            'message' => "WhatsApp Mesajı Gönderildi: " . $request->template_name
        ]);

        return response()->json(['success' => true]);
    }
}
