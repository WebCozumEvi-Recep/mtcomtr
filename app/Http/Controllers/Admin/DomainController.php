<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CdnProvider;
use App\Models\CloudflareAccount;
use App\Models\Domain;
use App\Models\Brand;
use App\Models\FunnelImage;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\PaymentProvider;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

class DomainController extends Controller
{
    public function __construct()
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');
        @ini_set('upload_max_filesize', '64M');
        @ini_set('post_max_size', '64M');
    }

    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Domainleri görüntüleme yetkiniz yok.');
        $query = Domain::with(['products', 'cloudflareAccount', 'brand'])
            ->withCount('orders')
            ->withCount(['orders as approved_orders_count' => function($query) {
                $query->whereIn('status', ['onaylandı', 'kargoya_verildi', 'teslim_edildi']);
            }])
            ->withSum('orders', 'grand_total')
            ->withSum(['orders as approved_orders_sum_grand_total' => function($query) {
                $query->whereIn('status', ['onaylandı', 'kargoya_verildi', 'teslim_edildi']);
            }], 'grand_total')
            ->withSum('expenses', 'amount')
            ->withSum(['orders as total_quantity' => function($query) {
                $query->join('offers', 'orders.offer_id', '=', 'offers.id');
            }], 'offers.quantity')
            // Gelişmiş Funnel Takibi
            ->withCount(['funnelEvents as scroll_50_unique' => function($query) {
                $query->where('event_type', 'scroll_50')->select(\DB::raw('count(distinct(session_id))'));
            }])
            ->withCount(['funnelEvents as scroll_50_total' => function($query) {
                $query->where('event_type', 'scroll_50');
            }])
            ->withCount(['funnelEvents as form_open_unique' => function($query) {
                $query->where('event_type', 'form_open')->select(\DB::raw('count(distinct(session_id))'));
            }])
            ->withCount(['funnelEvents as form_open_total' => function($query) {
                $query->where('event_type', 'form_open');
            }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('domain_name', 'LIKE', "%{$search}%")
                    ->orWhere('api_domain_id', 'LIKE', "%{$search}%");
            });
        }

        $domains = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.domains.index', compact('domains'));
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermission('domains.edit'), 403, 'Yeni domain ekleme yetkiniz yok.');
        $products = Product::all();
        $brands = Brand::orderBy('name')->get();
        $cloudflareAccounts = CloudflareAccount::orderBy('name')->get();
        $paymentProviders = PaymentProvider::where('is_active', true)->get();

        return view('admin.domains.form', [
            'domain' => new Domain,
            'products' => $products,
            'brands' => $brands,
            'cloudflareAccounts' => $cloudflareAccounts,
            'paymentProviders' => $paymentProviders,
        ]);
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('domains.edit'), 403, 'Yeni domain ekleme yetkiniz yok.');
        $data = $request->validate([
            'domain_name' => 'required|string|max:255|unique:domains,domain_name',
            'api_domain_id' => 'nullable|string|max:255|unique:domains,api_domain_id',
            'brand_id' => 'nullable|exists:brands,id',
            'cloudflare_zone_id' => 'required|string|max:64',
            'cloudflare_account_id' => 'nullable|exists:cloudflare_accounts,id',
            'offers' => 'required|array',
            'bunny_pullzone_id' => 'nullable|string|max:64',
            'bunny_hostname' => 'nullable|string|max:255',
        ]);

        $apiDomainId = isset($data['api_domain_id']) && $data['api_domain_id'] !== '' ? $data['api_domain_id'] : null;

        $domain = Domain::create([
            'domain_name' => $data['domain_name'],
            'api_domain_id' => $apiDomainId,
            'brand_id' => $data['brand_id'] ?? null,
            'cloudflare_zone_id' => $data['cloudflare_zone_id'],
            'cloudflare_account_id' => $data['cloudflare_account_id'] ?? null,
            'bunny_pullzone_id' => $data['bunny_pullzone_id'] ?? null,
            'bunny_hostname' => $data['bunny_hostname'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'ssl_active' => false,
        ]);

        // No longer syncing domain-product as it's handled in offers
        // $domain->products()->sync([$data['product_id']]);
        $configData = $request->only([
            'seo_title', 'seo_description', 'primary_color', 'secondary_color', 
            'header_scripts', 'body_scripts', 'footer_scripts', 'success_scripts',
            'whatsapp_number', 'countdown_minutes', 'stock_countdown_start',
            'facebook_pixel_id', 'google_analytics_id', 'google_verification_code', 'tiktok_pixel_id',
            'payment_provider_id'
        ]);
        $configData['allow_credit_card'] = $request->boolean('allow_credit_card');

        $brandingPath = public_path('uploads/branding');
        if (! File::exists($brandingPath)) {
            File::makeDirectory($brandingPath, 0755, true);
        }

        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $filename = 'fav_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($brandingPath, $filename);
            $configData['favicon_path'] = $filename;
        }

        if ($request->hasFile('og_image')) {
            $file = $request->file('og_image');
            $filename = 'og_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($brandingPath, $filename);
            $configData['og_image_path'] = $filename;
        }

        $domain->config()->create($configData);

        // Offers
        $uploadPath = public_path('uploads/offers');
        if (! File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        foreach ($request->input('offers', []) as $index => $off) {
            if (! isset($off['offer_name'])) {
                continue;
            }

            $offerImage = null;
            if ($request->hasFile("offers.$index.image")) {
                $file = $request->file("offers.$index.image");
                $offerImage = 'off_'.time().'_'.$index.'.'.$file->getClientOriginalExtension();
                $file->move($uploadPath, $offerImage);
            }

            $activeImage = null;
            if ($request->hasFile("offers.$index.active_image")) {
                $file = $request->file("offers.$index.active_image");
                $activeImage = 'off_active_'.time().'_'.$index.'.'.$file->getClientOriginalExtension();
                $file->move($uploadPath, $activeImage);
            }

            $offerPrice = 0;
            $offerQty = 0;
            if (isset($off['items']) && is_array($off['items'])) {
                foreach ($off['items'] as $item) {
                    $offerPrice += ($item['quantity'] ?? 1) * ($item['price'] ?? 0);
                    $offerQty += ($item['quantity'] ?? 1);
                }
            } else {
                $offerPrice = $off['price'] ?? 0;
                $offerQty = $off['quantity'] ?? 1;
            }

            $offer = $domain->offers()->create([
                'product_id' => $data['product_id'],
                'offer_name' => $off['offer_name'] ?? 'Paket',
                'api_offer_id' => isset($off['api_offer_id']) && $off['api_offer_id'] !== '' ? trim((string) $off['api_offer_id']) : null,
                'offer_image' => $offerImage,
                'active_image' => $activeImage,
                'quantity' => $offerQty,
                'price' => $offerPrice,
                'is_popular' => isset($off['is_popular']),
            ]);

            // Initial Affiliate Package Commission (linked to this offer)
            $offer->affiliateCommission()->create([
                'domain_id' => $domain->id,
                'is_affiliate_active' => false,
                'commission_type' => 'fixed',
                'commission_amount' => 0.00,
                'commission_rate' => 0.00,
            ]);

            if (isset($off['items']) && is_array($off['items'])) {
                foreach ($off['items'] as $item) {
                    if (!empty($item['product_id'])) {
                        $offer->items()->create([
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'] ?? 1,
                            'price' => $item['price'] ?? 0,
                        ]);
                    }
                }
            } else {
                // Fallback to primary product if no items defined
                $offer->items()->create([
                    'product_id' => $data['product_id'],
                    'quantity' => $off['quantity'] ?? 1,
                    'price' => $off['price'] ?? 0,
                ]);
            }
        }

        // Create initial affiliate domain setting
        $domain->affiliateSetting()->create([
            'is_affiliate_active' => false,
            'cookie_days' => 30,
            'attribution_rule' => 'last_click',
            'media_enabled' => true,
        ]);

        // Gallery processing... (Keeping previous logic)
        $this->handleGallery($request, $domain);
        $this->saveUpsells($request, $domain);

        $cfNotice = $this->autoProvisionCloudflareDnsNotice($domain);
        $warnings = array_values(array_filter([$cfNotice['warning']]));
        $redirect = redirect()->route('admin.domains')
            ->with('success', 'Domain ve satış kurgusu oluşturuldu.'.$cfNotice['success_suffix']);
        if ($warnings !== []) {
            $redirect->with('warning', implode(' | ', $warnings));
        }

        return $redirect;
    }

    public function edit(Domain $domain)
    {
        abort_if(!auth()->user()->hasPermission('domains.edit'), 403, 'Domain düzenleme yetkiniz yok.');
        $domain->load(['config', 'offers.items.product', 'offers.affiliateCommission', 'affiliateSetting', 'products', 'gallery', 'brand']);
        $products = Product::all();
        $brands = Brand::orderBy('name')->get();
        $cloudflareAccounts = CloudflareAccount::orderBy('name')->get();
        $paymentProviders = PaymentProvider::where('is_active', true)->get();

        return view('admin.domains.form', compact('domain', 'products', 'brands', 'cloudflareAccounts', 'paymentProviders'));
    }

    public function destroy(Domain $domain)
    {
        abort_if(!auth()->user()->hasPermission('domains.delete'), 403, 'Domain silme yetkiniz yok.');
        // 1. Delete gallery files from storage
        foreach ($domain->gallery as $image) {
            $path = public_path('uploads/funnels/' . $image->image_path);
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        // 2. Delete related records that don't have DB cascade or need cleanup
        // Orders don't have cascade in DB, so we delete them manually
        $domain->orders()->delete();

        // 3. Create System Alert
        \App\Models\SystemAlert::create([
            'type' => 'danger',
            'title' => 'Site Silindi',
            'message' => "'<strong>" . $domain->domain_name . "</strong>' sitesi ve tüm bağlı verileri kalıcı olarak silindi.",
            'causer_id' => auth()->id(),
            'data' => [
                'type' => 'domain',
                'id' => $domain->id,
                'name' => $domain->domain_name
            ]
        ]);

        // 4. Delete the domain (config, offers, gallery rows, and pivot entries will cascade in DB)
        $domain->delete();

        return redirect()->route('admin.domains')
            ->with('success', "'<strong>" . $domain->domain_name . "</strong>' sitesi ve bağlı tüm veriler başarıyla silindi.");
    }

    public function resetInfrastructure(Domain $domain)
    {
        abort_if(!auth()->user()->hasPermission('domains.edit'), 403, 'Altyapı sıfırlama yetkiniz yok.');
        $domain->update([
            'domain_name' => 'reset-' . $domain->id . '-' . time(),
            'cloudflare_zone_id' => null,
            'cloudflare_account_id' => null,
            'bunny_pullzone_id' => null,
            'bunny_hostname' => null,
            'ssl_certificate_expires_at' => null,
            'is_active' => false,
        ]);

        return redirect()->route('admin.domains.edit', $domain)
            ->with('success', 'Alan adı ve altyapı ayarları sıfırlandı. Yeni bir alan adı tanımlayabilirsiniz.');
    }

    public function clone(Domain $domain)
    {
        abort_if(!auth()->user()->hasPermission('domains.edit'), 403, 'Domain kopyalama yetkiniz yok.');
        try {
            // 1. Replicate Domain
            $newDomain = $domain->replicate();
            // Append a unique suffix to domain name to avoid validation errors
            $newDomain->domain_name = 'kopya-' . time() . '-' . $domain->domain_name;
            $newDomain->api_domain_id = null;
            $newDomain->visitor_count = 0;
            $newDomain->unique_visitor_count = 0;
            $newDomain->is_active = false;
            $newDomain->save();

            // 2. Sync Products
            $newDomain->products()->sync($domain->products->pluck('id'));

            // 3. Replicate Config
            if ($domain->config) {
                $newConfig = $domain->config->replicate();
                $newConfig->domain_id = $newDomain->id;
                $newConfig->save();
            }

            // 4. Replicate Offers
            foreach ($domain->offers as $offer) {
                $newOffer = $offer->replicate();
                $newOffer->domain_id = $newDomain->id;
                $newOffer->save();
            }

            // 5. Replicate Gallery
            foreach ($domain->gallery as $image) {
                $newImage = $image->replicate();
                $newImage->domain_id = $newDomain->id;
                $newImage->save();
            }

            return redirect()->route('admin.domains.edit', $newDomain)
                ->with('success', 'Domain ve tüm içeriği başarıyla kopyalandı. Lütfen yeni alan adını (domain name) güncelleyin.');
        } catch (\Exception $e) {
            return redirect()->route('admin.domains')
                ->with('error', 'Kopyalama sırasında hata oluştu: ' . $e->getMessage());
        }
    }

    public function cloudflareZones(Request $request)
    {
        $raw = $request->query('cloudflare_account_id');
        $accountId = ($raw !== null && $raw !== '') ? (int) $raw : null;
        $account = $accountId ? CloudflareAccount::find($accountId) : null;

        $token = $this->cloudflareTokenFromAccountId($accountId);
        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'API token yok. Cloudflare hesabı seçin veya ayarlarda CLOUDFLARE_TOKEN tanımlayın.',
                'zones' => [],
            ]);
        }

        $allZones = [];
        $page = 1;
        $totalPages = 1;

        do {
            $query = [
                'page' => $page,
                'per_page' => 50,
            ];
            $accountIdentifier = $account && filled($account->account_identifier)
                ? trim((string) $account->account_identifier)
                : '';
            $canUseAccountFilter = (bool) preg_match('/^[a-f0-9]{32}$/i', $accountIdentifier);
            if ($canUseAccountFilter) {
                $query['account.id'] = $accountIdentifier;
            }

            $response = Http::timeout(45)->acceptJson()->withToken($token)->get('https://api.cloudflare.com/client/v4/zones', $query);
            $body = $response->json() ?? [];
            $firstError = $body['errors'][0] ?? [];

            // Bazı token/policy kombinasyonlarında account.id filtresi "payload invalid" döndürebiliyor.
            // Bu durumda aynı sayfayı filtresiz tekrar deneyip en azından zone listesini kurtarıyoruz.
            if (! $response->successful() && $canUseAccountFilter) {
                $errorMessage = strtolower((string) ($firstError['message'] ?? ''));
                $isPayloadInvalid = str_contains($errorMessage, 'payload is invalid')
                    || str_contains($errorMessage, 'invalid request body');
                if ($isPayloadInvalid) {
                    $fallbackQuery = [
                        'page' => $page,
                        'per_page' => 50,
                    ];
                    $response = Http::timeout(45)->acceptJson()->withToken($token)->get('https://api.cloudflare.com/client/v4/zones', $fallbackQuery);
                    $body = $response->json() ?? [];
                    $firstError = $body['errors'][0] ?? [];
                }
            }

            if (! $response->successful()) {
                $message = $firstError['message'] ?? 'Cloudflare API isteği başarısız.';
                $code = isset($firstError['code']) ? ' #'.$firstError['code'] : '';
                $message .= $code.' (HTTP '.$response->status().')';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'zones' => [],
                    'debug' => [
                        'http_status' => $response->status(),
                        'query' => $query,
                        'account_id' => $accountId,
                        'account_identifier' => $accountIdentifier !== '' ? $accountIdentifier : null,
                        'used_account_filter' => $canUseAccountFilter,
                        'errors' => $body['errors'] ?? [],
                        'messages' => $body['messages'] ?? [],
                        'ray_id' => $response->header('cf-ray'),
                    ],
                ], 422);
            }

            $data = $response->json();
            foreach ($data['result'] ?? [] as $z) {
                $allZones[] = [
                    'id' => $z['id'],
                    'name' => $z['name'],
                    'status' => $z['status'] ?? null,
                ];
            }

            $info = $data['result_info'] ?? [];
            $totalPages = max(1, (int) ($info['total_pages'] ?? 1));
            $page++;
        } while ($page <= $totalPages);

        usort($allZones, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return response()->json([
            'success' => true,
            'zones' => $allZones,
        ]);
    }

    public function update(Request $request, Domain $domain)
    {
        abort_if(!auth()->user()->hasPermission('domains.edit'), 403, 'Domain düzenleme yetkiniz yok.');
        $data = $request->validate([
            'domain_name' => 'required|string|max:255|unique:domains,domain_name,'.$domain->id,
            'api_domain_id' => 'nullable|string|max:255|unique:domains,api_domain_id,'.$domain->id,
            'brand_id' => 'nullable|exists:brands,id',
            'cloudflare_zone_id' => 'required|string|max:64',
            'cloudflare_account_id' => 'nullable|exists:cloudflare_accounts,id',
            'bunny_pullzone_id' => 'nullable|string|max:64',
            'bunny_hostname' => 'nullable|string|max:255',
        ]);

        $apiDomainId = isset($data['api_domain_id']) && $data['api_domain_id'] !== '' ? $data['api_domain_id'] : null;

        $domain->update([
            'domain_name' => $data['domain_name'],
            'api_domain_id' => $apiDomainId,
            'brand_id' => $data['brand_id'] ?? null,
            'cloudflare_zone_id' => $data['cloudflare_zone_id'],
            'cloudflare_account_id' => $data['cloudflare_account_id'] ?? null,
            'bunny_pullzone_id' => $data['bunny_pullzone_id'] ?? $domain->bunny_pullzone_id,
            'bunny_hostname' => $data['bunny_hostname'] ?? $domain->bunny_hostname,
            'is_active' => $request->boolean('is_active'),
            'ssl_active' => false,
        ]);

        // No longer syncing domain-product as it's handled in offers
        // $domain->products()->sync([$request->product_id]);
        $configData = $request->only([
            'seo_title', 'seo_description', 'primary_color', 'secondary_color', 
            'header_scripts', 'body_scripts', 'footer_scripts', 'success_scripts',
            'whatsapp_number', 'countdown_minutes', 'stock_countdown_start',
            'facebook_pixel_id', 'google_analytics_id', 'google_verification_code', 'tiktok_pixel_id',
            'payment_provider_id'
        ]);
        $configData['allow_credit_card'] = $request->boolean('allow_credit_card');

        $brandingPath = public_path('uploads/branding');
        if (! File::exists($brandingPath)) {
            File::makeDirectory($brandingPath, 0755, true);
        }

        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $filename = 'fav_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($brandingPath, $filename);
            $configData['favicon_path'] = $filename;
        }

        if ($request->hasFile('og_image')) {
            $file = $request->file('og_image');
            $filename = 'og_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($brandingPath, $filename);
            $configData['og_image_path'] = $filename;
        }

        $domain->config()->updateOrCreate(['domain_id' => $domain->id], $configData);

        // Offers Updated handle
        $uploadPath = public_path('uploads/offers');
        if (! File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        $existingOfferIds = [];
        foreach ($request->input('offers', []) as $index => $off) {
            // Safety check for truncated data
            if (! isset($off['offer_name'])) {
                continue;
            }

            $offerPrice = 0;
            $offerQty = 0;
            if (isset($off['items']) && is_array($off['items'])) {
                foreach ($off['items'] as $item) {
                    $offerPrice += ($item['quantity'] ?? 1) * ($item['price'] ?? 0);
                    $offerQty += ($item['quantity'] ?? 1);
                }
            } else {
                $offerPrice = $off['price'] ?? 0;
                $offerQty = $off['quantity'] ?? 1;
            }

            $offerData = [
                'product_id' => $request->product_id,
                'offer_name' => $off['offer_name'] ?? 'Paket',
                'api_offer_id' => isset($off['api_offer_id']) && $off['api_offer_id'] !== '' ? trim((string) $off['api_offer_id']) : null,
                'quantity' => $offerQty,
                'price' => $offerPrice,
                'is_popular' => isset($off['is_popular']),
            ];

            if ($request->hasFile("offers.$index.image")) {
                $file = $request->file("offers.$index.image");
                $filename = 'off_'.time().'_'.$index.'.'.$file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $offerData['offer_image'] = $filename;
            } else if (!empty($off['offer_image'])) {
                $offerData['offer_image'] = $off['offer_image'];
            }

            if ($request->hasFile("offers.$index.active_image")) {
                $file = $request->file("offers.$index.active_image");
                $filename = 'off_active_'.time().'_'.$index.'.'.$file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $offerData['active_image'] = $filename;
            } else if (!empty($off['active_image'])) {
                $offerData['active_image'] = $off['active_image'];
            }

            $offer = $domain->offers()->updateOrCreate(['id' => $off['id'] ?? null], $offerData);
            $existingOfferIds[] = $offer->id;

            // Save Affiliate Package Commission
            $commissionActive = isset($off['affiliate_active']);
            $offer->affiliateCommission()->updateOrCreate(
                ['package_id' => $offer->id],
                [
                    'domain_id' => $domain->id,
                    'is_affiliate_active' => $commissionActive,
                    'commission_type' => $off['commission_type'] ?? 'fixed',
                    'commission_amount' => (float) ($off['commission_amount'] ?? 0.00),
                    'commission_rate' => (float) ($off['commission_rate'] ?? 0.00),
                    'affiliate_description' => $off['affiliate_description'] ?? null,
                ]
            );

            // Handle Offer Items
            if (isset($off['items']) && is_array($off['items'])) {
                $existingItemIds = [];
                foreach ($off['items'] as $item) {
                    if (!empty($item['product_id'])) {
                        $offerItem = $offer->items()->updateOrCreate(
                            ['id' => $item['id'] ?? null],
                            [
                                'product_id' => $item['product_id'], 
                                'quantity' => $item['quantity'] ?? 1,
                                'price' => $item['price'] ?? 0,
                            ]
                        );
                        $existingItemIds[] = $offerItem->id;
                    }
                }
                $offer->items()->whereNotIn('id', $existingItemIds)->delete();
            } else {
                // If no items provided but it's a new offer, or we want to ensure at least one item
                if ($offer->items()->count() === 0) {
                    $offer->items()->create([
                        'product_id' => $request->product_id,
                        'quantity' => $off['quantity'] ?? 1,
                        'price' => $off['price'] ?? 0,
                    ]);
                }
            }
        }
        $domain->offers()->whereNotIn('id', $existingOfferIds)->delete();

        // Save domain-level affiliate settings
        $affiliateSettingData = $request->input('affiliate', []);
        $domain->affiliateSetting()->updateOrCreate(
            ['domain_id' => $domain->id],
            [
                'is_affiliate_active' => isset($affiliateSettingData['is_affiliate_active']),
                'affiliate_title' => $affiliateSettingData['affiliate_title'] ?? null,
                'affiliate_description' => $affiliateSettingData['affiliate_description'] ?? null,
                'cookie_days' => (int) ($affiliateSettingData['cookie_days'] ?? 30),
                'attribution_rule' => $affiliateSettingData['attribution_rule'] ?? 'last_click',
                'media_enabled' => isset($affiliateSettingData['media_enabled']),
                'warning_text' => $affiliateSettingData['warning_text'] ?? null,
                'forbidden_terms' => $affiliateSettingData['forbidden_terms'] ?? null,
            ]
        );

        $this->handleGallery($request, $domain);
        $this->saveUpsells($request, $domain);

        $cfNotice = $this->autoProvisionCloudflareDnsNotice($domain);
        $warnings = array_values(array_filter([$cfNotice['warning']]));
        $redirect = redirect()->route('admin.domains')
            ->with('success', 'Güncellendi.'.$cfNotice['success_suffix']);
        if ($warnings !== []) {
            $redirect->with('warning', implode(' | ', $warnings));
        }

        return $redirect;
    }

    /**
     * Kayıt sonrası Cloudflare’de A kaydı + SSL (subdomain dahil) idempotent çalışır.
     *
     * @return array{success_suffix: string, warning: ?string}
     */
    private function autoProvisionCloudflareDnsNotice(Domain $domain): array
    {
        $result = $this->provisionCloudflareDnsAndSsl($domain);
        if (! $result['ran']) {
            return ['success_suffix' => '', 'warning' => null];
        }
        if (! $result['success']) {
            return [
                'success_suffix' => '',
                'warning' => 'Cloudflare otomatik kurulum başarısız: '.$result['message'],
            ];
        }

        return [
            'success_suffix' => ' Cloudflare otomatik: '.$result['message'],
            'warning' => null,
        ];
    }

    private function handleGallery($request, $domain)
    {
        // Shared gallery logic
        if ($request->has('existing_gallery')) {
            foreach ($request->input('existing_gallery') as $id => $gdata) {
                $item = FunnelImage::find($id);
                if ($item) {
                    if (isset($gdata['delete'])) {
                        File::delete(public_path('uploads/funnels/'.$item->image_path));
                        $item->delete();
                    } else {
                        $item->update([
                            'link_target' => $gdata['link'] ?? null,
                            'video_url' => $gdata['video'] ?? null,
                            'sort_order' => $gdata['sort'] ?? 0,
                        ]);
                    }
                }
            }
        }

        if ($request->hasFile('gallery_files')) {
            $uploadPath = public_path('uploads/funnels');
            if (! File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }
            foreach ($request->file('gallery_files') as $index => $file) {
                $originalName = $file->getClientOriginalName();
                $filename = 'g_'.time().'_'.$index.'.'.$file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $domain->gallery()->create([
                    'image_path' => $filename,
                    'original_name' => $originalName,
                    'sort_order' => 100 + $index,
                ]);
            }
        }
    }

    public function onboardingCloudflare(Domain $domain)
    {
        $token = $this->resolveCloudflareToken($domain);
        if (! $token) {
            return response()->json(['success' => false, 'message' => 'Cloudflare token eksik. Domain için hesap seçin veya Sistem Ayarlarında CLOUDFLARE_TOKEN tanımlayın.']);
        }

        $domain->loadMissing('cloudflareAccount');
        $accountIdentifier = trim((string) ($domain->cloudflareAccount?->account_identifier ?? ''));

        // 1. Add Zone
        $response = Http::withToken($token)
            ->post('https://api.cloudflare.com/client/v4/zones', $this->cloudflareZoneCreatePayload($domain->domain_name, $accountIdentifier));

        $data = $response->json();
        if ($response->successful()) {
            $zoneId = $data['result']['id'];
            $domain->update(['cloudflare_zone_id' => $zoneId]);
            $ns = $data['result']['name_servers'];

            return response()->json([
                'success' => true,
                'message' => 'Domain Cloudflare\'e eklendi. Lütfen şu Nameserverları tanımlayın: '.implode(', ', $ns),
                'zone_id' => $zoneId,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Hata: '.($data['errors'][0]['message'] ?? 'Bilinmeyen hata')]);
    }

    public function onboardingCloudflareDraft(Request $request)
    {
        try {
            $validated = $request->validate([
                'domain_name' => 'required|string|max:255',
                'cloudflare_account_id' => 'nullable|integer|exists:cloudflare_accounts,id',
            ]);

            $host = trim(strtolower((string) $validated['domain_name']));
            $host = preg_replace('#^https?://#', '', $host);
            $host = trim((string) $host, '/');
            if (! preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/', $host)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçerli bir domain girin (örn: ornek.com).',
                ], 422);
            }

            $accountId = isset($validated['cloudflare_account_id']) ? (int) $validated['cloudflare_account_id'] : null;
            $token = $this->cloudflareTokenFromAccountId($accountId);
            if (! $token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cloudflare API token bulunamadı. Hesap seçin veya sistem token tanımlayın.',
                ], 422);
            }

            $accountIdentifier = '';
            if ($accountId) {
                $account = CloudflareAccount::find($accountId);
                $accountIdentifier = trim((string) ($account?->account_identifier ?? ''));
            }

            $response = Http::timeout(45)->acceptJson()->withToken($token)
                ->post('https://api.cloudflare.com/client/v4/zones', $this->cloudflareZoneCreatePayload($host, $accountIdentifier));

            $data = $response->json() ?? [];
            if (! $response->successful()) {
                $message = $data['errors'][0]['message'] ?? 'Cloudflare zone oluşturulamadı.';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'debug' => [
                        'http_status' => $response->status(),
                        'request_payload' => $this->cloudflareZoneCreatePayload($host, $accountIdentifier),
                        'errors' => $data['errors'] ?? [],
                        'messages' => $data['messages'] ?? [],
                        'ray_id' => $response->header('cf-ray'),
                    ],
                ], 422);
            }

            $zoneId = (string) ($data['result']['id'] ?? '');
            $zoneName = (string) ($data['result']['name'] ?? $host);
            $nameServers = array_values(array_filter(array_map('strval', $data['result']['name_servers'] ?? [])));

            return response()->json([
                'success' => true,
                'message' => 'Zone Cloudflare\'de oluşturuldu. NS kayıtlarını alan adı kayıtçında bu nameserverlara yönlendirin.',
                'zone_id' => $zoneId,
                'zone_name' => $zoneName,
                'name_servers' => $nameServers,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Draft onboarding exception: '.$e->getMessage(),
            ], 500);
        }
    }

    public function checkCloudflareStatus(Domain $domain)
    {
        $zoneId = $domain->cloudflare_zone_id;
        $token = $this->resolveCloudflareToken($domain);

        if (! $zoneId || ! $token) {
            return response()->json([
                'success' => false,
                'status' => null,
                'message' => 'Eksik bilgi (zone veya token).',
            ]);
        }

        $response = Http::withToken($token)
            ->get("https://api.cloudflare.com/client/v4/zones/{$zoneId}");

        $payload = $response->json() ?? [];

        if (! $response->successful()) {
            $err = $payload['errors'][0]['message'] ?? $response->reason() ?? 'İstek başarısız';

            return response()->json([
                'success' => false,
                'status' => null,
                'message' => 'Cloudflare API: '.$err,
            ]);
        }

        $result = $payload['result'] ?? null;
        if (! is_array($result)) {
            return response()->json([
                'success' => false,
                'status' => null,
                'message' => 'Cloudflare yanıtı geçersiz.',
            ]);
        }

        $status = (string) ($result['status'] ?? 'unknown');
        $nameServers = array_values(array_filter(array_map('strval', $result['name_servers'] ?? [])));

        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => $this->cloudflareZoneStatusUserMessage($status, $nameServers),
            'name_servers' => $nameServers,
        ]);
    }

    public function finalizeCloudflare(Domain $domain)
    {
        $result = $this->provisionCloudflareDnsAndSsl($domain);
        if (! $result['ran']) {
            return response()->json(['success' => false, 'message' => $result['message']]);
        }
        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']]);
        }

        return response()->json([
            'success' => true,
            'skipped' => (bool) ($result['skipped'] ?? false),
            'message' => $result['message'],
        ]);
    }

    public function bunnyStatus(Domain $domain)
    {
        $provider = $this->activeBunnyProvider();
        if (! $provider) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif BunnyCDN saglayicisi bulunamadi. Once CDN Firmalari modulunden Bunny kaydi acip aktif edin.',
            ], 422);
        }

        if (! filled($domain->bunny_pullzone_id)) {
            return response()->json([
                'success' => true,
                'connected' => false,
                'message' => 'Bu domaine ait Bunny Pull Zone henuz olusturulmamis.',
            ]);
        }

        $response = Http::timeout(45)
            ->withHeaders(['AccessKey' => (string) $provider->api_token, 'Accept' => 'application/json'])
            ->get('https://api.bunny.net/pullzone/'.urlencode((string) $domain->bunny_pullzone_id));

        if (! $response->successful()) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'Bunny Pull Zone durumu okunamadi: '.$response->status(),
            ], 422);
        }

        $payload = $response->json() ?? [];
        $zoneHost = $payload['Hostnames'][0]['Value'] ?? $domain->bunny_hostname ?? null;

        return response()->json([
            'success' => true,
            'connected' => true,
            'pullzone_id' => (string) ($payload['Id'] ?? $domain->bunny_pullzone_id),
            'hostname' => $zoneHost,
            'message' => 'Bunny baglantisi aktif. Pull Zone ID: '.($payload['Id'] ?? $domain->bunny_pullzone_id),
        ]);
    }

    public function bunnyProvision(Domain $domain)
    {
        $provider = $this->activeBunnyProvider();
        if (! $provider) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif BunnyCDN kaydi bulunamadi. CDN Firmalari modulunde Bunny kaydini aktif edin.',
            ], 422);
        }
        if (! filled($provider->api_token)) {
            return response()->json([
                'success' => false,
                'message' => 'Bunny API token bos. CDN kaydina API token girin.',
            ], 422);
        }

        if (filled($domain->bunny_pullzone_id)) {
            return response()->json([
                'success' => true,
                'pullzone_id' => $domain->bunny_pullzone_id,
                'hostname' => $domain->bunny_hostname,
                'message' => 'Bu domain icin Bunny Pull Zone zaten olusturulmus.',
            ]);
        }

        $originUrl = $this->bunnyOriginUrl($domain->domain_name);
        $zoneName = $this->bunnyZoneNameForDomain($domain);

        $createResp = Http::timeout(45)
            ->withHeaders(['AccessKey' => (string) $provider->api_token, 'Accept' => 'application/json'])
            ->post('https://api.bunny.net/pullzone', [
                'Name' => $zoneName,
                'OriginUrl' => $originUrl,
            ]);

        if (! $createResp->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Bunny Pull Zone olusturma hatasi: '.$createResp->status().' '.$createResp->body(),
            ], 422);
        }

        $created = $createResp->json() ?? [];
        $pullZoneId = (string) ($created['Id'] ?? '');
        if ($pullZoneId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Bunny yanitinda Pull Zone ID bulunamadi.',
            ], 422);
        }

        $zoneHostname = $this->extractBunnyHostnameFromPayload($created, $zoneName);

        $domain->update([
            'bunny_pullzone_id' => $pullZoneId,
            'bunny_hostname' => $zoneHostname,
        ]);

        return response()->json([
            'success' => true,
            'pullzone_id' => $pullZoneId,
            'hostname' => $zoneHostname,
            'message' => 'Bunny Pull Zone olusturuldu. Domain icin Bunny hostunu kullanabilirsiniz: '.$zoneHostname,
        ]);
    }

    public function bunnyProvisionDraft(Request $request)
    {
        $validated = $request->validate([
            'domain_name' => 'required|string|max:255',
        ]);

        $provider = $this->activeBunnyProvider();
        if (! $provider) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif BunnyCDN kaydi bulunamadi. CDN Firmalari modulunde Bunny kaydini aktif edin.',
            ], 422);
        }
        if (! filled($provider->api_token)) {
            return response()->json([
                'success' => false,
                'message' => 'Bunny API token bos. CDN kaydina API token girin.',
            ], 422);
        }

        $domainName = trim(strtolower($validated['domain_name']));
        $originUrl = $this->bunnyOriginUrl($domainName);
        $zoneName = $this->bunnyZoneNameFromHost($domainName);

        $createResp = Http::timeout(45)
            ->withHeaders(['AccessKey' => (string) $provider->api_token, 'Accept' => 'application/json'])
            ->post('https://api.bunny.net/pullzone', [
                'Name' => $zoneName,
                'OriginUrl' => $originUrl,
            ]);

        if (! $createResp->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Bunny Pull Zone olusturma hatasi: '.$createResp->status().' '.$createResp->body(),
            ], 422);
        }

        $created = $createResp->json() ?? [];
        $pullZoneId = (string) ($created['Id'] ?? '');
        if ($pullZoneId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Bunny yanitinda Pull Zone ID bulunamadi.',
            ], 422);
        }

        $zoneHostname = $this->extractBunnyHostnameFromPayload($created, $zoneName);

        return response()->json([
            'success' => true,
            'pullzone_id' => $pullZoneId,
            'hostname' => $zoneHostname,
            'message' => 'Bunny Pull Zone olusturuldu. Simdi domain kaydini tamamlayabilirsiniz. Bunny host: '.$zoneHostname,
        ]);
    }

    /**
     * Apex veya subdomain hostname için Cloudflare A kayıtları + SSL Full (idempotent).
     *
     * @return array{ran: bool, success: bool, skipped: ?bool, message: string}
     */
    private function provisionCloudflareDnsAndSsl(Domain $domain): array
    {
        $zoneId = $domain->cloudflare_zone_id;
        $token = $this->resolveCloudflareToken($domain);
        $serverIp = Setting::where('key', 'SERVER_IP')->first()?->value ?? config('services.cloudflare.server_ip');

        if (! $zoneId || ! $token || ! $serverIp) {
            return [
                'ran' => false,
                'success' => false,
                'skipped' => null,
                'message' => 'Ayarlar eksik (zone, Cloudflare token veya SERVER_IP).',
            ];
        }

        try {
            $zoneResponse = Http::withToken($token)
                ->timeout(10)
                ->get("https://api.cloudflare.com/client/v4/zones/{$zoneId}");

            if (! $zoneResponse->successful()) {
                return [
                    'ran' => true,
                    'success' => false,
                    'skipped' => null,
                    'message' => 'Zone bilgisi alınamadı.',
                ];
            }
        } catch (\Exception $e) {
            return [
                'ran' => true,
                'success' => false,
                'skipped' => null,
                'message' => 'Cloudflare bağlantı hatası: ' . $e->getMessage(),
            ];
        }

        $apex = strtolower($zoneResponse->json()['result']['name'] ?? '');
        $full = strtolower($domain->domain_name);
        $serverIp = trim($serverIp);

        $isSubdomain = $apex !== ''
            && $full !== $apex
            && str_ends_with($full, '.'.$apex)
            && strlen($full) > strlen($apex) + 1;

        $dnsOk = false;
        $dnsNote = '';

        if ($isSubdomain) {
            $relative = substr($domain->domain_name, 0, -(strlen($apex) + 1));
            $relative = strtolower(preg_replace('/\.+$/', '', $relative));
            $hostFqdn = $relative.'.'.$apex;

            if ($this->cloudflareWildcardAPointsToIp($token, $zoneId, $apex, $serverIp)) {
                $dnsOk = true;
                $dnsNote = 'Wildcard (*.'.$apex.') sunucu IP’si ile uyumlu.';
            } elseif ($this->cloudflareSubdomainARecordPointsToIp($token, $zoneId, $relative, $apex, $serverIp)) {
                $dnsOk = true;
                $dnsNote = 'Subdomain A kaydı ('.$hostFqdn.') zaten sunucu IP’sine işaret ediyor.';
            }
        } else {
            $hasRoot = $this->cloudflareHasARecordToIp($token, $zoneId, $apex, $serverIp)
                || $this->cloudflareHasARecordToIp($token, $zoneId, '@', $serverIp);
            $hasWww = $this->cloudflareHasARecordToIp($token, $zoneId, 'www.'.$apex, $serverIp);
            if ($hasRoot && $hasWww) {
                $dnsOk = true;
                $dnsNote = 'Kök ve www A kayıtları zaten sunucu IP’si ile uyumlu.';
            }
        }

        $sslFull = $this->cloudflareSslIsFull($token, $zoneId);

        if ($dnsOk && $sslFull) {
            return [
                'ran' => true,
                'success' => true,
                'skipped' => true,
                'message' => 'DNS ve domain kayıtları ile SSL (Full) zaten uygun; tekrar işlem yapılmadı. '.$dnsNote,
            ];
        }

        if ($dnsOk && ! $sslFull) {
            Http::withToken($token)
                ->patch("https://api.cloudflare.com/client/v4/zones/{$zoneId}/settings/ssl", [
                    'value' => 'full',
                ]);

            return [
                'ran' => true,
                'success' => true,
                'skipped' => false,
                'message' => 'DNS zaten ayarlıydı; yalnızca SSL Full güncellendi. '.$dnsNote,
            ];
        }

        $parts = [];

        if ($isSubdomain) {
            $relative = substr($domain->domain_name, 0, -(strlen($apex) + 1));
            $relative = strtolower(preg_replace('/\.+$/', '', $relative));
            $hostFqdn = $relative.'.'.$apex;

            if ($this->cloudflareWildcardAPointsToIp($token, $zoneId, $apex, $serverIp)) {
                $parts[] = 'Wildcard mevcut; ayrı subdomain A kaydı eklenmedi.';
            } else {
                $subResult = $this->cloudflareEnsureSubdomainARecord($token, $zoneId, $relative, $apex, $serverIp);
                if (! $subResult['ok']) {
                    return [
                        'ran' => true,
                        'success' => false,
                        'skipped' => null,
                        'message' => 'Subdomain DNS: '.$subResult['message'],
                    ];
                }
                $parts[] = $subResult['message'];
            }
        } else {
            if (! $this->cloudflareHasARecordToIp($token, $zoneId, $apex, $serverIp)
                && ! $this->cloudflareHasARecordToIp($token, $zoneId, '@', $serverIp)) {
                Http::timeout(45)->withToken($token)
                    ->post(
                        "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records",
                        $this->cloudflareNewARecordPayload($apex, $serverIp)
                    );
                $parts[] = 'Kök A kaydı eklendi ('.$apex.').';
            }
            if (! $this->cloudflareHasARecordToIp($token, $zoneId, 'www.'.$apex, $serverIp)) {
                Http::timeout(45)->withToken($token)
                    ->post(
                        "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records",
                        $this->cloudflareNewARecordPayload('www', $serverIp)
                    );
                $parts[] = 'www A kaydı eklendi.';
            }
            if ($parts === []) {
                $parts[] = 'Kök ve www A kayıtları zaten vardı.';
            }
        }

        if (! $sslFull) {
            Http::withToken($token)
                ->patch("https://api.cloudflare.com/client/v4/zones/{$zoneId}/settings/ssl", [
                    'value' => 'full',
                ]);
            $parts[] = 'SSL Full etkinleştirildi.';
        } else {
            $parts[] = 'SSL zaten Full idi.';
        }

        return [
            'ran' => true,
            'success' => true,
            'skipped' => false,
            'message' => implode(' ', $parts),
        ];
    }

    /**
     * Cloudflare DNS A kaydı gövdesi (panel / resmi API ile aynı alanlar).
     *
     * @return array{type: string, name: string, content: string, ttl: int, proxied: bool}
     */
    private function cloudflareNewARecordPayload(string $name, string $content): array
    {
        $proxied = (bool) config('services.cloudflare.dns_proxied', true);
        $payload = [
            'type' => 'A',
            'name' => $name,
            'content' => $content,
            'proxied' => $proxied,
        ];
        if ($proxied) {
            $payload['ttl'] = 1;
        } else {
            $ttl = (int) config('services.cloudflare.dns_ttl', 3600);
            $payload['ttl'] = $ttl >= 60 ? $ttl : 3600;
        }

        return $payload;
    }

    private function cloudflareSslIsFull(string $token, string $zoneId): bool
    {
        $r = Http::withToken($token)->get("https://api.cloudflare.com/client/v4/zones/{$zoneId}/settings/ssl");

        if (! $r->successful()) {
            return false;
        }

        return ($r->json()['result']['value'] ?? '') === 'full';
    }

    private function cloudflareHasARecordToIp(string $token, string $zoneId, string $fqdn, string $serverIp): bool
    {
        $serverIp = trim($serverIp);
        $fqdn = strtolower($fqdn);
        if ($fqdn === '' || $serverIp === '') {
            return false;
        }

        $response = Http::timeout(45)->withToken($token)->get(
            "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records",
            [
                'type' => 'A',
                'name' => $fqdn,
            ]
        );

        if (! $response->successful()) {
            return false;
        }

        foreach ($response->json()['result'] ?? [] as $record) {
            $content = trim((string) ($record['content'] ?? ''));
            if (strcasecmp($content, $serverIp) === 0) {
                return true;
            }
        }

        return false;
    }

    private function normalizeCfDnsName(string $name): string
    {
        return strtolower(rtrim(trim($name), '.'));
    }

    /**
     * Subdomain hostname için A kaydı (Cloudflare’de name FQDN veya yalnızca göreli etiket olabilir) sunucu IP’sine işaret ediyor mu?
     */
    private function cloudflareSubdomainARecordPointsToIp(
        string $token,
        string $zoneId,
        string $relative,
        string $apex,
        string $serverIp
    ): bool {
        $relative = $this->normalizeCfDnsName($relative);
        $apex = $this->normalizeCfDnsName($apex);
        $hostFqdn = $this->normalizeCfDnsName($relative.'.'.$apex);
        $serverIp = trim($serverIp);
        if ($relative === '' || $serverIp === '') {
            return false;
        }

        foreach (array_unique([$hostFqdn, $relative]) as $queryName) {
            if ($queryName === '') {
                continue;
            }
            if ($this->cloudflareHasARecordToIp($token, $zoneId, $queryName, $serverIp)) {
                return true;
            }
        }

        return $this->cloudflareScanARecordNameMatchesIp($token, $zoneId, $hostFqdn, $serverIp);
    }

    /**
     * Sayfalı A listesinde tam hostname eşleşmesi (filtre kaçırdığında).
     */
    private function cloudflareScanARecordNameMatchesIp(string $token, string $zoneId, string $hostFqdn, string $serverIp): bool
    {
        $want = $this->normalizeCfDnsName($hostFqdn);
        $serverIp = trim($serverIp);
        $page = 1;
        $totalPages = 1;
        do {
            $listResp = Http::timeout(45)->withToken($token)->get(
                "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records",
                ['type' => 'A', 'page' => $page, 'per_page' => 100]
            );
            if (! $listResp->successful()) {
                break;
            }
            $payload = $listResp->json();
            foreach ($payload['result'] ?? [] as $record) {
                $n = $this->normalizeCfDnsName((string) ($record['name'] ?? ''));
                $content = trim((string) ($record['content'] ?? ''));
                if ($n === $want && strcasecmp($content, $serverIp) === 0) {
                    return true;
                }
            }
            $info = $payload['result_info'] ?? [];
            $totalPages = max(1, (int) ($info['total_pages'] ?? 1));
            $page++;
        } while ($page <= $totalPages);

        return false;
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function cloudflareEnsureSubdomainARecord(
        string $token,
        string $zoneId,
        string $relative,
        string $apex,
        string $serverIp
    ): array {
        $relative = $this->normalizeCfDnsName($relative);
        $apex = $this->normalizeCfDnsName($apex);
        $hostFqdn = $this->normalizeCfDnsName($relative.'.'.$apex);
        $serverIp = trim($serverIp);

        if ($relative === '' || $serverIp === '') {
            return ['ok' => false, 'message' => 'Subdomain etiketi veya sunucu IP’si boş.'];
        }

        if ($this->cloudflareSubdomainARecordPointsToIp($token, $zoneId, $relative, $apex, $serverIp)) {
            return ['ok' => true, 'message' => 'Subdomain A kaydı zaten sunucu IP’sine işaret ediyor.'];
        }

        $lastError = '';
        foreach (array_unique([$relative, $hostFqdn]) as $recordName) {
            if ($recordName === '') {
                continue;
            }
            $response = Http::timeout(45)->withToken($token)
                ->post(
                    "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records",
                    $this->cloudflareNewARecordPayload($recordName, $serverIp)
                );

            if ($response->successful()) {
                return ['ok' => true, 'message' => 'Subdomain için A kaydı eklendi ('.$recordName.').'];
            }

            $errors = $response->json('errors') ?? [];
            $lastError = is_array($errors) && isset($errors[0]['message'])
                ? (string) $errors[0]['message']
                : $response->body();
            $code = is_array($errors) && isset($errors[0]['code']) ? (int) $errors[0]['code'] : 0;
            $lower = strtolower($lastError);

            if (in_array($code, [81053, 81057], true)
                || str_contains($lower, 'already exists')
                || str_contains($lower, 'duplicate')
                || str_contains($lower, 'identical record')) {
                return ['ok' => true, 'message' => 'Subdomain A kaydı zaten vardı (Cloudflare duplicate).'];
            }
        }

        return ['ok' => false, 'message' => $lastError !== '' ? $lastError : 'A kaydı oluşturulamadı.'];
    }

    /**
     * Zone’da *.apex için A kaydı var ve content sunucu IP’si ile aynı mı (Cloudflare paneldeki * wildcard).
     */
    private function cloudflareWildcardAPointsToIp(string $token, string $zoneId, string $apex, string $serverIp): bool
    {
        $serverIp = trim($serverIp);
        $apex = strtolower($apex);
        if ($apex === '' || $serverIp === '') {
            return false;
        }

        $wantName = '*.'.$apex;
        $response = Http::withToken($token)->get(
            "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records",
            [
                'type' => 'A',
                'name' => $wantName,
            ]
        );

        if (! $response->successful()) {
            return false;
        }

        $results = $response->json()['result'] ?? [];
        foreach ($results as $record) {
            $content = trim((string) ($record['content'] ?? ''));
            if (strcasecmp($content, $serverIp) === 0) {
                return true;
            }
        }

        if ($results !== []) {
            return false;
        }

        $page = 1;
        $totalPages = 1;
        do {
            $listResp = Http::withToken($token)->get(
                "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records",
                ['type' => 'A', 'page' => $page, 'per_page' => 100]
            );
            if (! $listResp->successful()) {
                break;
            }
            $payload = $listResp->json();
            foreach ($payload['result'] ?? [] as $record) {
                $n = strtolower((string) ($record['name'] ?? ''));
                $content = trim((string) ($record['content'] ?? ''));
                if ($n === $wantName && strcasecmp($content, $serverIp) === 0) {
                    return true;
                }
            }
            $info = $payload['result_info'] ?? [];
            $totalPages = max(1, (int) ($info['total_pages'] ?? 1));
            $page++;
        } while ($page <= $totalPages);

        return false;
    }

    private function cloudflareTokenFromAccountId(?int $accountId): ?string
    {
        if ($accountId) {
            $account = CloudflareAccount::find($accountId);
            if ($account) {
                return $this->cloudflareTokenFromAccount($account);
            }

            return null;
        }

        return $this->normalizeCloudflareToken(
            Setting::where('key', 'CLOUDFLARE_TOKEN')->first()?->value
                ?? config('services.cloudflare.token')
        );
    }

    private function cloudflareTokenFromAccount(CloudflareAccount $account): ?string
    {
        $raw = (string) $account->getRawOriginal('api_token');
        if (trim($raw) === '') {
            return null;
        }

        try {
            $token = (string) Crypt::decryptString($raw);
        } catch (DecryptException) {
            // Legacy kayıtlarda token plaintext tutulmuş olabilir.
            $token = $raw;
        }

        return $this->normalizeCloudflareToken($token);
    }

    private function normalizeCloudflareToken(?string $token): ?string
    {
        $token = trim((string) $token);
        if ($token === '') {
            return null;
        }
        if (str_starts_with(strtolower($token), 'bearer ')) {
            $token = trim(substr($token, 7));
        }

        return $token !== '' ? $token : null;
    }

    /**
     * @return array{name: string, jump_start: bool, type: string, account?: array{id: string}}
     */
    private function cloudflareZoneCreatePayload(string $host, ?string $accountIdentifier = null): array
    {
        $payload = [
            'name' => $host,
            'jump_start' => true,
            'type' => 'full',
        ];

        $accountIdentifier = trim((string) $accountIdentifier);
        if ((bool) preg_match('/^[a-f0-9]{32}$/i', $accountIdentifier)) {
            $payload['account'] = ['id' => $accountIdentifier];
        }

        return $payload;
    }

    private function activeBunnyProvider(): ?CdnProvider
    {
        return CdnProvider::query()
            ->where('provider', 'bunny')
            ->where('is_active', true)
            ->first();
    }

    private function bunnyOriginUrl(?string $domainName = null): string
    {
        $host = trim((string) $domainName);
        if ($host !== '') {
            $host = preg_replace('#^https?://#', '', $host);
            $host = trim((string) $host, '/');
            if ($host !== '') {
                return 'https://'.$host;
            }
        }

        return (string) config('app.url', 'http://127.0.0.1');
    }

    private function bunnyZoneNameForDomain(Domain $domain): string
    {
        return $this->bunnyZoneNameFromHost($domain->domain_name, $domain->id);
    }

    private function bunnyZoneNameFromHost(string $domainName, ?int $id = null): string
    {
        $base = strtolower((string) preg_replace('/[^a-z0-9\-]+/i', '-', $domainName));
        $base = trim($base, '-');
        if ($base === '') {
            $base = 'domain';
        }

        $suffix = $id ? (string) $id : (string) now()->timestamp;

        return substr($base.'-'.$suffix, 0, 50);
    }

    private function extractBunnyHostnameFromPayload(array $payload, string $zoneName): string
    {
        $hostnames = $payload['Hostnames'] ?? [];
        if (is_array($hostnames)) {
            foreach ($hostnames as $host) {
                $value = is_array($host) ? ($host['Value'] ?? null) : null;
                if (filled($value) && str_contains((string) $value, '.b-cdn.net')) {
                    return (string) $value;
                }
            }
        }

        return $zoneName.'.b-cdn.net';
    }

    private function resolveCloudflareToken(Domain $domain): ?string
    {
        $domain->loadMissing('cloudflareAccount');
        $account = $domain->cloudflareAccount;

        if ($account && $account->is_active) {
            $token = $this->cloudflareTokenFromAccount($account);
            if ($token) {
                return $token;
            }
        }

        return $this->cloudflareTokenFromAccountId(null);
    }

    /**
     * Kullanıcıya gösterilecek metin; Cloudflare zone yanıtındaki status ve name_servers ile üretilir.
     */
    private function cloudflareZoneStatusUserMessage(string $status, array $nameServers): string
    {
        $ns = implode(', ', $nameServers);

        return match ($status) {
            'active' => 'Tebrikler! Domain Cloudflare üzerinde aktif (API durumu: active).',
            'pending' => 'Domain henüz aktif değil (Cloudflare durumu: pending).'
                .($ns !== ''
                    ? ' NS kayıtlarını şu Cloudflare nameserver adreslerine yönlendirin: '.$ns.'. Yayılma genelde 1–2 dakika sürebilir.'
                    : ' Kayıtçıda NS yönlendirmelerini tamamlayıp 1–2 dakika bekleyin.'),
            'initializing' => 'Zone başlatılıyor (Cloudflare durumu: initializing). Bir süre sonra tekrar deneyin.',
            'moved' => 'Zone taşınmış (Cloudflare durumu: moved). Cloudflare panelinden kontrol edin.',
            'deleted' => 'Zone silinmiş (Cloudflare durumu: deleted).',
            'deactivated' => 'Zone devre dışı (Cloudflare durumu: deactivated).',
            default => 'Cloudflare zone durumu: '.$status.'.'
                .($ns !== '' ? ' Nameserver\'lar: '.$ns.'.' : ''),
        };
    }

    public function storeExpense(Request $request, Domain $domain)
    {
        $data = $request->validate([
            'platform' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'spent_at' => 'required|date',
            'description' => 'nullable|string|max:1000'
        ]);

        $domain->expenses()->create($data);

        return back()->with('success', 'Masraf kaydedildi.');
    }

    public function destroyExpense(\App\Models\DomainExpense $expense)
    {
        $expense->delete();
        return back()->with('success', 'Masraf silindi.');
    }

    private function saveUpsells(Request $request, Domain $domain)
    {
        $existingUpsellIds = [];
        foreach ($request->input('upsells', []) as $index => $up) {
            if (! isset($up['name']) || ! isset($up['discount_price'])) {
                continue;
            }

            $upsellData = [
                'domain_id' => $domain->id,
                'name' => $up['name'],
                'title' => $up['title'] ?? $up['name'],
                'description' => $up['description'] ?? null,
                'offer_type' => $up['offer_type'] ?? 'add_same_product',
                'target_product_id' => !empty($up['target_product_id']) ? $up['target_product_id'] : null,
                'target_package_id' => !empty($up['target_package_id']) ? $up['target_package_id'] : null,
                'original_price' => !empty($up['original_price']) ? $up['original_price'] : null,
                'discount_price' => $up['discount_price'],
                'display_timing' => $up['display_timing'] ?? 'both',
                'is_active' => isset($up['is_active']),
            ];

            $upsell = $domain->upsellOffers()->updateOrCreate(['id' => $up['id'] ?? null], $upsellData);
            $existingUpsellIds[] = $upsell->id;
        }
        $domain->upsellOffers()->whereNotIn('id', $existingUpsellIds)->delete();
    }

    public function resetStats(Domain $domain)
    {
        try {
            // Delete related funnel events
            $domain->funnelEvents()->delete();
            
            // Reset counts
            $domain->update([
                'visitor_count' => 0,
                'unique_visitor_count' => 0
            ]);

            // Clear system cache to ensure data is refreshed
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');

            // Create System Alert
            \App\Models\SystemAlert::create([
                'type' => 'info',
                'title' => 'İstatistikler Sıfırlandı',
                'message' => "'<strong>" . $domain->domain_name . "</strong>' sitesi için ziyaretçi ve trafik verileri sıfırlandı.",
                'causer_id' => auth()->id(),
                'data' => [
                    'type' => 'domain',
                    'id' => $domain->id,
                    'name' => $domain->domain_name
                ]
            ]);

            return back()->with('success', "'<strong>" . $domain->domain_name . "</strong>' için istatistikler başarıyla sıfırlandı.");
        } catch (\Exception $e) {
            return back()->with('error', 'Hata: ' . $e->getMessage());
        }
    }
}
