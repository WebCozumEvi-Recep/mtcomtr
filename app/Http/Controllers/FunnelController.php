<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domain;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Offer;

class FunnelController extends Controller
{
    public function show(Request $request)
    {
        $host = strtolower($request->getHost());

        // Corporate promo page hosts
        if (in_array($host, ['teksat.com.tr', 'www.teksat.com.tr'], true)) {
            if ($this->requestLooksLikeIntegrationApiClient($request)) {
                return response()->json([
                    'message' => 'Bu adres kurumsal vitrindir; harici sipariş API’si panel uygulama sunucusundaki /api/v1/integration veya /v1/integration yollarındadır. baseUrl’i vitrin alan adıyla karıştırmayın.',
                ], 404);
            }

            return view('welcome');
        }
        
        // Find the domain configuration
        $domain = Domain::with(['products', 'config.paymentProvider', 'offers.items', 'gallery'])
            ->where('domain_name', $host)
            ->where('is_active', true)
            ->first();

        if (!$domain) {
            return response('Sistem yapılandırılmadı veya bu alan adı sistemde aktif değil. Lütfen admin panelinden bu domain için bir yapılandırma tanımlayın.', 404);
        }

        // Increment visitor count
        $domain->increment('visitor_count');

        // Unique visitor check
        $cookieName = 'teksat_v_' . $domain->id;
        if (!$request->hasCookie($cookieName)) {
            $domain->increment('unique_visitor_count');
            // Set cookie for 24 hours
            cookie()->queue($cookieName, '1', 60 * 24);
        }

        $product = $domain->products->first();
        $config = $domain->config;
        $offers = $domain->offers;

        return view('funnel.landing', compact('domain', 'product', 'config', 'offers'));
    }

    /**
     * Kök (GET /) yanlışlıkla entegrasyon istemcisiyle çağrıldığında 200+HTML vitrin dönmesin;
     * JSON + 404 ile yanlış host/path netleşsin.
     */
    private function requestLooksLikeIntegrationApiClient(Request $request): bool
    {
        if ($request->expectsJson()) {
            return true;
        }

        if ($request->header('X-Teksat-Source-Host')) {
            return true;
        }

        if ($request->header('X-Teksat-Signature')) {
            return true;
        }

        $auth = (string) $request->header('Authorization', '');
        if ($auth !== '' && strncasecmp($auth, 'Bearer ', 8) === 0) {
            return true;
        }

        return false;
    }

    public function submit(Request $request)
    {
        try {
            $request->validate([
                'offer_id' => 'required|exists:offers,id',
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'city' => 'required|string',
                'district' => 'required|string',
                'address' => 'required|string',
                'email' => 'nullable|email|max:255',
                'id_number' => 'nullable|string|max:20',
                'payment_method' => 'nullable|string|in:cod,credit_card',
            ]);

            $offer = Offer::findOrFail($request->offer_id);
            $domain = Domain::with('config.paymentProvider')->findOrFail($offer->domain_id);

            // ... (customer logic remains same)
            $customer = Customer::where('phone', $request->phone)->first();
            
            if ($customer && $customer->is_blacklisted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Üzgünüz, bu numara ile şu an sipariş verilemez. Lütfen müşteri hizmetleri ile iletişime geçin.'
                ], 403);
            }

            if (!$customer) {
                $customer = Customer::create([
                    'phone' => $request->phone,
                    'full_name' => $request->name
                ]);
            }

            $calculatedTotal = $offer->items->isNotEmpty() 
                ? $offer->items->sum(function($item) { return $item->quantity * $item->price; })
                : $offer->price;

            $paymentMethod = $request->input('payment_method', 'cod');
            $orderNotes = 'Yeni sipariş - ' . now()->format('d.m.Y H:i') . ' (IP: ' . $request->ip() . ')';
            $paymentResult = ['success' => true]; // Default for COD

            if ($paymentMethod === 'credit_card') {
                $ccData = $request->only(['cc_name', 'cc_number', 'cc_expiry', 'cc_cvc']);
                $provider = $domain->config->paymentProvider;
                if (!$provider) {
                    return response()->json(['success' => false, 'message' => 'Ödeme altyapısı yapılandırılmamış.'], 422);
                }

                // Process based on provider type
                $paymentHandled = false;
                if ($provider->provider_type === 'vakifbank') {
                    $paymentHandled = true;
                    $paymentService = new \App\Services\Payments\VakifbankPaymentService($provider);
                    // Use a more unique temporary order number for the bank request to avoid 2023 error
                    $tempOrderNumber = 'TS' . date('YmdHis') . strtoupper(substr(uniqid(), -5)) . rand(1000, 9999);
                    
                    $tempOrder = new Order(['grand_total' => $calculatedTotal]);
                    $tempOrder->internal_order_no = $tempOrderNumber;
                    
                    $paymentResult = $paymentService->process($tempOrder, $ccData);
                }

                // If credit card selected but no provider logic matched
                if (!$paymentHandled) {
                    return response()->json(['success' => false, 'message' => 'Ödeme sağlayıcısı (' . $provider->provider_type . ') henüz desteklenmiyor.'], 422);
                }

                // SECURITY: Explicitly clear card data from memory
                unset($ccData);

                if (!$paymentResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ödeme Hatası: ' . ($paymentResult['message'] ?? 'İşlem reddedildi.')
                    ], 422);
                }

                // If it's a 3D redirect, we return the HTML to the frontend to be executed
                if (isset($paymentResult['is_redirect']) && $paymentResult['is_redirect']) {
                    // Create the order as pending first
                    $order = Order::create([
                        'domain_id' => $domain->id,
                        'offer_id' => $offer->id,
                        'customer_id' => $customer->id,
                        'ip_address' => $request->ip(),
                        'city' => $request->city,
                        'district' => $request->district,
                        'address' => $request->address,
                        'email' => $request->email,
                        'id_number' => $request->input('id_number', '11111111111'),
                        'grand_total' => $calculatedTotal,
                        'original_total' => $calculatedTotal,
                        'final_total' => $calculatedTotal,
                        'status' => 'pending',
                        'payment_method' => $paymentMethod,
                        'payment_status' => 'pending',
                        'order_notes' => $orderNotes . "\n[3D] Banka onay sayfasına yönlendirildi. (Provizyon No: " . $tempOrderNumber . ")"
                    ]);

                    $this->handleAffiliateAttributionAndCommission($order, $request);

                    return response()->json([
                        'success' => true,
                        'is_redirect' => true,
                        'redirect_html' => $paymentResult['redirect_html']
                    ]);
                }

                $orderNotes .= "\n[ÖDEME] Kredi Kartı ile başarıyla tahsil edildi. İşlem No: " . ($paymentResult['transaction_id'] ?? '-');
            }

            // Create Order (Normal flow - COD)
            $order = Order::create([
                'domain_id' => $domain->id,
                'offer_id' => $offer->id,
                'customer_id' => $customer->id,
                'ip_address' => $request->ip(),
                'city' => $request->city,
                'district' => $request->district,
                'address' => $request->address,
                'email' => $request->email,
                'id_number' => $request->input('id_number', '11111111111'),
                'grand_total' => $calculatedTotal,
                'original_total' => $calculatedTotal,
                'final_total' => $calculatedTotal,
                'status' => $paymentMethod === 'credit_card' ? 'confirmed' : 'pending',
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentMethod === 'credit_card' ? 'paid' : 'pending',
                'order_notes' => $orderNotes
            ]);

            $this->handleAffiliateAttributionAndCommission($order, $request);

            return response()->json([
                'success' => true,
                'redirect' => route('funnel.success', ['order' => $order->id])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hata: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment callback from bank
     */
    public function paymentCallback(Request $request)
    {
        // Vakifbank returns parameters in POST
        $status = $request->input('Status'); // Success: Y, Failure: N
        $orderNumber = $request->input('OrderId');
        $resultMsg = $request->input('ResultMessage');
        $transactionId = $request->input('TransactionId');
        
        // Find the order by order_notes or we should have used the real ID
        // For now, we look for the order that was recently created with this order number in notes or we search by internal_order_no if available
        $order = Order::where('order_notes', 'like', '%' . $orderNumber . '%')->latest()->first();

        if (!$order) {
            // Fallback: try to find the latest pending credit card order for this IP
            $order = Order::where('payment_method', 'credit_card')
                          ->where('payment_status', 'pending')
                          ->where('ip_address', $request->ip())
                          ->latest()
                          ->first();
        }

        if ($status === 'Y' && $order) {
            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'order_notes' => $order->order_notes . "\n[3D ONAY] Ödeme başarıyla tamamlandı. İşlem No: " . ($transactionId ?? '-')
            ]);
            
            return redirect()->route('funnel.success', ['order' => $order->id]);
        }

        $message = $resultMsg ?: 'Ödeme banka tarafından reddedildi.';
        return redirect()->route('funnel.landing')->with('error', 'Ödeme başarısız: ' . $message);
    }

    public function success(Order $order)
    {
        return view('funnel.success', compact('order'));
    }

    public function acceptUpsell(Request $request, Order $order)
    {
        try {
            $validated = $request->validate([
                'upsell_offer_id' => 'required|exists:upsell_offers,id'
            ]);

            $offer = \App\Models\UpsellOffer::findOrFail($validated['upsell_offer_id']);

            // Create OrderUpsell record
            $order->upsells()->create([
                'upsell_offer_id' => $offer->id,
                'status' => 'accepted',
                'old_total' => $order->grand_total,
                'new_total' => $order->grand_total + $offer->discount_price,
                'added_amount' => $offer->discount_price,
                'accepted_at' => now()
            ]);

            $updateData = [
                'has_upsell' => true,
                'upsell_total' => ($order->upsell_total ?? 0) + $offer->discount_price,
                'grand_total' => $order->grand_total + $offer->discount_price,
                'final_total' => $order->grand_total + $offer->discount_price,
                'order_notes' => $order->order_notes . "\n[UPSCELL] " . $offer->name . " kabul edildi. (+₺" . $offer->discount_price . ")"
            ];

            // If upsell target is a package, update the order's main package
            if ($offer->target_package_id) {
                $updateData['offer_id'] = $offer->target_package_id;
            }

            $order->update($updateData);

            return response()->json(['success' => true, 'message' => 'Siparişiniz güncellendi.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function rejectUpsell(Request $request, Order $order)
    {
        try {
            $validated = $request->validate([
                'upsell_offer_id' => 'required|exists:upsell_offers,id'
            ]);

            $offer = \App\Models\UpsellOffer::findOrFail($validated['upsell_offer_id']);

            $order->upsells()->create([
                'upsell_offer_id' => $offer->id,
                'status' => 'rejected',
                'old_total' => $order->grand_total,
                'new_total' => $order->grand_total,
                'added_amount' => 0,
                'rejected_at' => now()
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Handle affiliate attribution and commission snapshots.
     */
    private function handleAffiliateAttributionAndCommission(Order $order, Request $request): void
    {
        try {
            $cookieValue = $request->cookie('ts_affiliate_click');
            $data = null;
            if ($cookieValue) {
                $data = json_decode($cookieValue, true);
            }

            if (!$data) {
                if ($request->filled('affiliate_id') && $request->filled('click_id')) {
                    $data = [
                        'click_id' => $request->input('click_id'),
                        'affiliate_id' => $request->input('affiliate_id'),
                        'link_id' => $request->input('affiliate_link_id') ?: $request->input('link_id'),
                        'domain_id' => $request->input('domain_id'),
                        'channel' => $request->input('channel'),
                        'keyword' => $request->input('keyword'),
                        'media_id' => $request->input('media_id'),
                    ];
                }
            }

            if ($data && !empty($data['affiliate_id'])) {
                // Save Attribution
                \App\Models\AffiliateOrderAttribution::create([
                    'order_id' => $order->id,
                    'affiliate_id' => $data['affiliate_id'],
                    'affiliate_link_id' => $data['link_id'] ?? null,
                    'click_id' => $data['click_id'] ?? null,
                    'domain_id' => $data['domain_id'] ?? $order->domain_id,
                    'channel' => $data['channel'] ?? null,
                    'keyword' => $data['keyword'] ?? null,
                    'media_id' => $data['media_id'] ?? null,
                ]);

                // Calculate & Record Commission Snapshot
                $domainSetting = \App\Models\AffiliateDomain::where('domain_id', $order->domain_id)->first();
                if ($domainSetting && $domainSetting->is_affiliate_active) {
                    $pkgCommission = \App\Models\AffiliatePackageCommission::where('package_id', $order->offer_id)
                        ->where('is_affiliate_active', true)
                        ->first();

                    if ($pkgCommission) {
                        $affiliateUser = \App\Models\AffiliateUser::find($data['affiliate_id']);
                        if ($affiliateUser && $affiliateUser->isActive()) {
                            $gross = 0.00;
                            if ($pkgCommission->commission_type === 'fixed') {
                                $gross = (float) $pkgCommission->commission_amount;
                            } elseif ($pkgCommission->commission_type === 'percentage') {
                                $rate = (float) $pkgCommission->commission_rate;
                                $gross = round(($order->grand_total * $rate) / 100, 2);
                            }

                            if ($gross > 0) {
                                $taxType = $affiliateUser->tax_type ?: 'none';
                                $split = \App\Models\AffiliateCommission::calculateCommissionSplit($gross, $taxType);

                                \App\Models\AffiliateCommission::create([
                                    'affiliate_id' => $affiliateUser->id,
                                    'order_id' => $order->id,
                                    'domain_id' => $order->domain_id,
                                    'purchased_package_id' => $order->offer_id,
                                    'affiliate_link_id' => $data['link_id'] ?? null,
                                    'channel' => $data['channel'] ?? null,
                                    'keyword' => $data['keyword'] ?? null,
                                    'order_total' => $order->grand_total,
                                    'commission_type_snapshot' => $pkgCommission->commission_type,
                                    'commission_amount_snapshot' => $pkgCommission->commission_amount ?: 0,
                                    'commission_rate_snapshot' => $pkgCommission->commission_rate ?: 0,
                                    'gross_commission' => $split['gross'],
                                    'tax_type' => $split['tax_type'],
                                    'withholding_amount' => $split['withholding'],
                                    'vat_amount' => $split['vat'],
                                    'net_amount' => $split['net'],
                                    'status' => 'pending',
                                ]);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Affiliate Attribution Error: ' . $e->getMessage());
        }
    }
}
