<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IntegrationController extends Controller
{
    /**
     * Sipariş aktarımında kullanılacak tüm funnel domainleri (id + site adresi).
     * Yetki: Bearer api_key + izinli çağıran host (middleware).
     */
    public function domains(Request $request)
    {
        $domains = Domain::query()
            ->orderBy('domain_name')
            ->get(['id', 'domain_name', 'api_domain_id']);

        return response()->json([
            'success' => true,
            'domains' => $domains,
        ]);
    }

    /**
     * Domaine bağlı ürünler (domain–ürün pivotu + paketlerde geçen ürünler, tekil liste).
     */
    public function products(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'nullable|integer|exists:domains,id',
            'api_domain_id' => 'nullable|string|max:255',
        ]);

        if (! $request->filled('domain_id') && ! $request->filled('api_domain_id')) {
            return response()->json([
                'success' => false,
                'message' => 'domain_id veya api_domain_id sorgu parametresi gerekli.',
            ], 422);
        }

        $domain = Domain::forIntegrationPayload($validated);
        if (! $domain) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz domain_id veya api_domain_id.',
            ], 422);
        }

        $domain = Domain::with(['products', 'offers.items'])->findOrFail($domain->id);

        $productIds = collect();
        foreach ($domain->products as $p) {
            $productIds->push($p->id);
        }
        foreach ($domain->offers as $offer) {
            if ($offer->product_id) {
                $productIds->push($offer->product_id);
            }
            foreach ($offer->items as $item) {
                $productIds->push($item->product_id);
            }
        }

        $uniqueIds = $productIds->unique()->filter()->values();
        if ($uniqueIds->isEmpty()) {
        return response()->json([
            'success' => true,
            'domain_id' => (int) $domain->id,
            'api_domain_id' => $domain->api_domain_id,
            'products' => [],
        ]);
        }

        $products = Product::query()
            ->whereIn('id', $uniqueIds)
            ->orderBy('name')
            ->get();

        $out = $products->map(function (Product $p) {
            return [
                'id' => $p->id,
                'api_product_id' => $p->api_product_id,
                'name' => $p->name,
                'price' => (string) $p->price,
                'sku' => $p->sku,
            ];
        });

        return response()->json([
            'success' => true,
            'domain_id' => (int) $domain->id,
            'api_domain_id' => $domain->api_domain_id,
            'products' => $out,
        ]);
    }

    /**
     * Domaine ait paketler (offers) ve kalemleri (offer_items).
     */
    public function offers(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'nullable|integer|exists:domains,id',
            'api_domain_id' => 'nullable|string|max:255',
        ]);

        if (! $request->filled('domain_id') && ! $request->filled('api_domain_id')) {
            return response()->json([
                'success' => false,
                'message' => 'domain_id veya api_domain_id sorgu parametresi gerekli.',
            ], 422);
        }

        $domain = Domain::forIntegrationPayload($validated);
        if (! $domain) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz domain_id veya api_domain_id.',
            ], 422);
        }

        $domain = Domain::findOrFail($domain->id);

        $offers = Offer::with(['items.product'])
            ->where('domain_id', $domain->id)
            ->orderBy('id')
            ->get();

        $mapped = $offers->map(function (Offer $o) {
            $items = $o->items->map(function ($it) {
                $row = [
                    'id' => $it->id,
                    'product_id' => $it->product_id,
                    'quantity' => (int) $it->quantity,
                    'price' => (string) $it->price,
                ];
                if ($it->relationLoaded('product') && $it->product) {
                    $row['product'] = [
                        'id' => $it->product->id,
                        'api_product_id' => $it->product->api_product_id,
                        'name' => $it->product->name,
                        'price' => (string) $it->product->price,
                        'sku' => $it->product->sku,
                    ];
                } else {
                    $row['product'] = null;
                }

                return $row;
            });

            return [
                'id' => $o->id,
                'api_offer_id' => $o->api_offer_id,
                'offer_name' => $o->offer_name,
                'quantity' => (int) $o->quantity,
                'price' => (string) $o->price,
                'is_popular' => (bool) $o->is_popular,
                'product_id' => $o->product_id,
                'items' => $items,
            ];
        });

        return response()->json([
            'success' => true,
            'domain_id' => (int) $domain->id,
            'api_domain_id' => $domain->api_domain_id,
            'offers' => $mapped,
        ]);
    }

    /**
     * İmzalı sipariş (HMAC): domain + `api_product_id` + müşteri/adres + **birim / toplam / adet** + COD; isteğe bağlı `api_offer_id` → `orders.offer_id` (paket eşlemesi).
     * Yetki: izinli çağıran host + imza (middleware).
     */
    public function storeOrder(Request $request)
    {
        /** @var Domain|null $domain */
        $domain = $request->attributes->get('integration_domain');
        if (! $domain instanceof Domain) {
            return response()->json([
                'success' => false,
                'message' => 'Entegrasyon domain bilgisi eksik (imza middleware).',
            ], 500);
        }

        $this->mergeIntegrationOrderAliases($request);

        $apiRaw = $request->input('api_domain_id');
        if (is_string($apiRaw) && trim($apiRaw) === '') {
            $request->merge(['api_domain_id' => null]);
        }

        $apiOrderRaw = $request->input('api_order_id');
        if (is_string($apiOrderRaw)) {
            $trimmed = trim($apiOrderRaw);
            $request->merge(['api_order_id' => $trimmed === '' ? null : $trimmed]);
        }

        $validator = Validator::make($request->all(), [
            'domain_id' => 'nullable|integer|exists:domains,id',
            'api_domain_id' => ['nullable', 'string', 'max:255', Rule::exists('domains', 'api_domain_id')],
            'api_product_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'address' => 'required|string|max:2000',
            'price' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1|max:99999',
            'api_offer_id' => 'nullable|string|max:255',
            'api_order_id' => ['nullable', 'string', 'max:255', Rule::unique('orders', 'api_order_id')],
            'payment_method' => 'nullable|string|in:cod',
            'order_date' => 'nullable|string|max:64',
            'email' => 'nullable|email|max:255',
            'id_number' => 'nullable|string|max:20',
            'cargo_tracking_no' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if (empty($validated['domain_id'] ?? null) && empty($validated['api_domain_id'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'domain_id veya api_domain_id gerekli.',
            ], 422);
        }

        $payloadDomain = Domain::forIntegrationPayload($validated);
        if (! $payloadDomain || $payloadDomain->id !== $domain->id) {
            return response()->json([
                'success' => false,
                'message' => 'İmza gövdesindeki domain_id / api_domain_id, çözülen domain ile uyuşmuyor.',
            ], 422);
        }

        $paymentMethod = $validated['payment_method'] ?? 'cod';

        $product = Product::query()
            ->where('api_product_id', $validated['api_product_id'])
            ->whereHas('domains', function ($q) use ($domain) {
                $q->where('domains.id', $domain->id);
            })
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Bu domain için geçerli bir api_product_id yok (ürün bu siteye bağlı değil veya kimlik boş).',
            ], 422);
        }

        $quantity = (int) $validated['quantity'];
        $prices = $this->normalizeIntegrationOrderLinePrices($request, $quantity);
        if ($prices === null) {
            return response()->json([
                'success' => false,
                'message' => 'Fiyat alanları: unit_price veya price (birim) ve/veya total_price; en az bir birim veya toplam gönderin (adet ile birlikte).',
            ], 422);
        }

        $unitPrice = $prices['unit_price'];
        $lineTotal = $prices['total_price'];

        $domainOffers = Offer::with(['items'])
            ->where('domain_id', $domain->id)
            ->orderBy('id')
            ->get();

        $apiOfferNeedle = isset($validated['api_offer_id']) && is_string($validated['api_offer_id'])
            ? trim($validated['api_offer_id'])
            : '';
        $apiOfferNeedle = $apiOfferNeedle !== '' ? $apiOfferNeedle : null;

        [$matchedOffer, $offerMatchError] = $this->resolveIntegrationOffer(
            $domainOffers,
            $product,
            $apiOfferNeedle,
            $quantity,
            $lineTotal
        );

        if ($offerMatchError !== null) {
            return response()->json([
                'success' => false,
                'message' => $offerMatchError,
            ], 422);
        }

        $orderPlacedAt = null;
        if (! empty($validated['order_date'])) {
            $orderPlacedAt = date('Y-m-d H:i:s', $validated['order_date']);

        }

        $orderNotes = 'API entegrasyon — api_product_id: ' . $validated['api_product_id']
            . ' — kayıt: ' . now()->format('d.m.Y H:i');
        if (! empty($validated['api_domain_id'])) {
            $orderNotes .= ' — api_domain_id: ' . $validated['api_domain_id'];
        }
        $orderNotes .= ' — birim: ' . $unitPrice . ' × adet: ' . $quantity . ' = toplam: ' . $lineTotal;
        if ($matchedOffer) {
            $orderNotes .= ' — paket id: ' . $matchedOffer->id
                . ($matchedOffer->api_offer_id ? ' (api_offer_id: ' . $matchedOffer->api_offer_id . ')' : '');
        }
        if ($orderPlacedAt !== null) {
            $orderNotes .= ' — sipariş tarihi (created_at): ' . $orderPlacedAt;
        }
        if (! empty($validated['api_order_id'])) {
            $orderNotes .= ' — api_order_id: ' . $validated['api_order_id'];
        }

        $integrationTrackingNumber = $this->integrationCargoTrackingNumberFromRequest($request);
        $hasCargoTracking = $integrationTrackingNumber !== null && $integrationTrackingNumber !== '';

        try {
            DB::beginTransaction();

            $customer = Customer::where('phone', $validated['phone'])->first();
            if ($customer && $customer->is_blacklisted) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Bu telefon numarası ile sipariş kabul edilmiyor.',
                ], 403);
            }

            if (! $customer) {
                $customer = Customer::create([
                    'phone' => $validated['phone'],
                    'full_name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                ]);
            } else {
                $customer->full_name = $validated['name'];
                if (! empty($validated['email'])) {
                    $customer->email = $validated['email'];
                }
                $customer->save();
            }

            $orderAttrs = [
                'domain_id' => $domain->id,
                'offer_id' => $matchedOffer?->id,
                'customer_id' => $customer->id,
                'ip_address' => $request->ip(),
                'city' => $validated['city'],
                'district' => $validated['district'],
                'address' => $validated['address'],
                'email' => $validated['email'] ?? null,
                'id_number' => $validated['id_number'] ?? '11111111111',
                'grand_total' => $lineTotal,
                'original_total' => $lineTotal,
                'final_total' => $lineTotal,
                'status' => $hasCargoTracking ? 'kargoya_verildi' : 'yeni',
                'cargo_firm' => $hasCargoTracking ? 'yurtici' : null,
                'tracking_number' => $integrationTrackingNumber,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'order_notes' => $orderNotes,
                'is_api' => true,
                'api_sent_at' => now(),
                'api_approved' => false,
                'api_order_id' => $validated['api_order_id'] ?? null,
            ];

            $order = Order::create($orderAttrs);

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'total_price' => $lineTotal,
            ]);

            if ($orderPlacedAt !== null) {
                Order::query()->whereKey($order->id)->update([
                    'created_at' => $orderPlacedAt,
                    'updated_at' => $orderPlacedAt,
                ]);
                OrderItem::query()->whereKey($orderItem->id)->update([
                    'created_at' => $orderPlacedAt,
                    'updated_at' => $orderPlacedAt,
                ]);
                $order->refresh();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sipariş alındı. Panelden onay verilene kadar api_approved=false.',
                'order_id' => $order->id,
                'internal_order_no' => $order->internal_order_no,
                'api_order_id' => $order->api_order_id,
            ], 201);
        } catch (QueryException $e) {
            DB::rollBack();

            if (str_contains($e->getMessage(), 'api_order_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu api_order_id ile zaten bir sipariş kayıtlı; aynı harici sipariş iki kez gönderilemez.',
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Sipariş kaydedilemedi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Sipariş kaydedilemedi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Gövdede `cargo_tracking_no` anahtarı var ve değer boş değilse: `orders.tracking_number`, `cargo_firm` = yurtici, `status` = kargoya_verildi.
     * Aksi halde takip null, durum yeni, cargo_firm null.
     */
    private function integrationCargoTrackingNumberFromRequest(Request $request): ?string
    {
        if (! array_key_exists('cargo_tracking_no', $request->all())) {
            return null;
        }

        $v = $request->input('cargo_tracking_no');
        if ($v === null) {
            return null;
        }

        if (is_string($v)) {
            $t = trim($v);

            return $t === '' ? null : $t;
        }

        if (is_scalar($v)) {
            $t = trim((string) $v);

            return $t === '' ? null : $t;
        }

        return null;
    }

    /**
     * Türkçe JSON anahtarlarını İngilizce alanlara eşler (ikisi birden gönderilirse dolu olan İngilizce önceliklidir).
     */
    private function mergeIntegrationOrderAliases(Request $request): void
    {
        $map = [
            'harici_domain_id' => 'api_domain_id',
            'ad_soyad' => 'name',
            'telefon' => 'phone',
            'il' => 'city',
            'ilce' => 'district',
            'adres' => 'address',
            'adet' => 'quantity',
            'fiyat' => 'price',
            'birim_fiyat' => 'unit_price',
            'toplam_fiyat' => 'total_price',
            'paket_kodu' => 'api_offer_id',
            'paket_id' => 'api_offer_id',
            'harici_siparis_id' => 'api_order_id',
            'siparis_kodu' => 'api_order_id',
            'siparis_tarihi' => 'order_date',
            'odeme_turu' => 'payment_method',
        ];

        foreach ($map as $from => $to) {
            if (! $request->filled($to) && $request->has($from)) {
                $request->merge([$to => $request->input($from)]);
            }
        }
    }

    /**
     * Birim ve/veya satır toplamından değerleri üretir. İkisi birden gönderilirse çarpım kontrolü yapılmaz.
     *
     * @return array{unit_price: float, total_price: float}|null
     */
    private function normalizeIntegrationOrderLinePrices(Request $request, int $quantity): ?array
    {
        if ($quantity < 1) {
            return null;
        }

        $unit = null;
        if ($request->filled('unit_price')) {
            $unit = (float) $request->input('unit_price');
        } elseif ($request->filled('price')) {
            $unit = (float) $request->input('price');
        }

        $total = $request->filled('total_price') ? (float) $request->input('total_price') : null;

        if ($unit === null && $total === null) {
            return null;
        }

        if ($unit === null) {
            $unit = round($total / $quantity, 4);
        }
        if ($total === null) {
            $total = round($unit * $quantity, 2);
        }

        $unit = round($unit, 4);
        $total = round($total, 2);

        return [
            'unit_price' => $unit,
            'total_price' => $total,
        ];
    }

    /**
     * Paket (offer) eşlemesi: önce api_offer_id; yoksa siparişteki ürünü içeren paketlerde
     * paket ürün adedi (offer.quantity) ile sipariş quantity eşitse ve tek aday varsa doğrudan o paket.
     * Aynı adetten birden fazla paket varsa total_price veya api_offer_id ile ayrıştırılır; son çare tutar eşlemesi.
     *
     * @return array{0: ?Offer, 1: ?string}
     */
    private function resolveIntegrationOffer(
        EloquentCollection $domainOffers,
        Product $product,
        ?string $apiOfferNeedle,
        int $requestQty,
        float $lineTotal
    ): array {
        if ($apiOfferNeedle !== null) {
            $byApi = $domainOffers->first(function (Offer $o) use ($apiOfferNeedle) {
                $v = $o->api_offer_id;

                return $v !== null && trim((string) $v) === $apiOfferNeedle;
            });

            if (! $byApi) {
                return [null, 'Belirtilen api_offer_id bu domainde bulunamadı.'];
            }

            if (! $this->integrationOfferContainsProduct($byApi, $product)) {
                return [null, 'Belirtilen api_offer_id paketi, siparişteki api_product_id ürününü içermiyor.'];
            }

            return [$byApi, null];
        }

        $applicable = $domainOffers->filter(fn (Offer $o) => $this->integrationOfferContainsProduct($o, $product))->values();

        if ($applicable->isEmpty()) {
            return [null, null];
        }

        $tol = 0.02;

        $samePieceCount = $applicable->filter(fn (Offer $o) => (int) $o->quantity === $requestQty)->values();

        if ($samePieceCount->count() === 1) {
            return [$samePieceCount->first(), null];
        }

        if ($samePieceCount->count() > 1) {
            $priceHits = $samePieceCount->filter(fn (Offer $o) => abs((float) $o->price - $lineTotal) <= $tol)->values();
            if ($priceHits->count() === 1) {
                return [$priceHits->first(), null];
            }

            return [null, 'Bu ürün için aynı paket adedi (ürün adedi) ile birden fazla paket tanımlı. Siparişte total_price (satır toplamı) veya api_offer_id göndererek doğru paketi seçin.'];
        }

        foreach ($applicable as $o) {
            if (abs((float) $o->price - $lineTotal) <= $tol && (int) $o->quantity === $requestQty) {
                return [$o, null];
            }
        }

        foreach ($applicable as $o) {
            if (abs((float) $o->price - $lineTotal) <= $tol) {
                return [$o, null];
            }
        }

        return [null, 'Bu ürün için tanımlı paketlerde, gönderilen adet veya toplam tutar ile eşleşen paket yok. GET /v1/integration/offers çıktısındaki quantity ve price değerleriyle hizalayın veya api_offer_id gönderin.'];
    }

    private function integrationOfferContainsProduct(Offer $offer, Product $product): bool
    {
        if ($offer->relationLoaded('items') && $offer->items->isNotEmpty()) {
            return $offer->items->contains('product_id', $product->id);
        }

        if ($offer->product_id) {
            return (int) $offer->product_id === (int) $product->id;
        }

        return false;
    }
}
