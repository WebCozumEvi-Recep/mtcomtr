<!DOCTYPE html>
<html lang="tr">
<head>
    @php
        $domainBunnyBase = !empty($domain->bunny_hostname) ? 'https://'.$domain->bunny_hostname : null;
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="domain-id" content="{{ $domain->id }}">
    <title>{{ $config->seo_title ?? ($product->name ?? 'Hoşgeldiniz') }}</title>
    
    @if($config->favicon_path)
    <link rel="icon" type="image/x-icon" href="{{ \App\Models\Setting::mediaUrl('uploads/branding/'.$config->favicon_path, null, $domainBunnyBase) }}">
    @endif

    <meta property="og:title" content="{{ $config->seo_title ?? ($product->name ?? 'Hoşgeldiniz') }}">
    @if($config->seo_description)
    <meta name="description" content="{{ $config->seo_description }}">
    <meta property="og:description" content="{{ $config->seo_description }}">
    @endif
    
    @if($config->og_image_path)
    <meta property="og:image" content="{{ \App\Models\Setting::mediaUrl('uploads/branding/'.$config->og_image_path, null, $domainBunnyBase) }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ \App\Models\Setting::mediaUrl('uploads/branding/'.$config->og_image_path, null, $domainBunnyBase) }}">
    @endif

    @if($config->google_verification_code)
    <meta name="google-site-verification" content="{{ $config->google_verification_code }}" />
    @endif

    <!-- Base Marketing Scripts -->
    @if($config->google_analytics_id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $config->google_analytics_id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $config->google_analytics_id }}');
    </script>
    @endif

    @if($config->facebook_pixel_id)
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;
    s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $config->facebook_pixel_id }}');
    fbq('track', 'PageView');
    fbq('track', 'ViewContent');
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $config->facebook_pixel_id }}&ev=PageView&noscript=1" /></noscript>
    @endif

    @if($config->tiktok_pixel_id)
    <script>
    !function (w, d, t) {
      w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","detach","update","setRealtimeFilteringConfigs","setAccount","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=d.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
      ttq.load('{{ $config->tiktok_pixel_id }}');
      ttq.page();
    }(window, document, 'ttq');
    </script>
    @endif

    {!! $config->header_scripts !!}
    
    <!-- Premium Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        :root { 
            --primary: {{ $config->primary_color ?? '#e11d48' }}; 
            --primary-bg: {{ ($config->primary_color ?? '#e11d48') }}10;
            --secondary: {{ $config->secondary_color ?? '#14532d' }};
        }
        body { font-family: 'Outfit', sans-serif; background: #fdfdfd; color: #1e293b; }
        
        .sticky-countdown {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: var(--primary); color: #fff; 
            padding: 10px 0;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
        }
        .timer-box {
            background: #111827;
            border-radius: 12px;
            min-width: 52px;
            height: 52px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: inset 0 2px 4px rgba(255,255,255,0.05);
        }
        .timer-val { font-size: 20px; font-weight: 900; line-height: 1; }
        .timer-label { font-size: 7px; font-weight: 800; text-transform: uppercase; opacity: 0.6; margin-top: 2px; letter-spacing: 0.05em; }
        
        .btn-mini-order {
            background: #000;
            color: #fff;
            font-size: 10px;
            font-weight: 900;
            padding: 12px 20px;
            border-radius: 14px;
            letter-spacing: 0.1em;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: all 0.2s;
        }
        .btn-mini-order:active { transform: scale(0.95); }
        
        /* WhatsApp Animated Button */
        .whatsapp-float {
            position: fixed; width: 60px; height: 60px; bottom: 90px; right: 20px;
            background-color: #25d366; color: #FFF;
            border-radius: 50px; text-align: center; font-size: 30px;
            box-shadow: 2px 2px 3px #999; z-index: 100;
            display: flex; align-items: center; justify-content: center;
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(37, 211, 102, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
        }

        .summary-box {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 20px;
        }
        .summary-row { display: flex; justify-content: flex-start; align-items: center; margin-bottom: 8px; font-size: 14px; font-weight: 600; color: #475569; }
        .summary-row div:first-child { width: 65%; }
        .summary-row div:last-child { width: 35%; text-align: right; }
        .free-text { color: #22c55e; font-weight: 800; }
        .grand-total-row { border-top: 1px solid #e2e8f0; margin-top: 12px; padding-top: 12px; display: flex; justify-content: space-between; align-items: center; }
        .grand-total-price { font-size: 24px; font-weight: 900; color: #0f172a; }
        
        .content-container { max-width: 640px; margin: 0 auto; background: #fff; position: relative; }
        @media (min-width: 1024px) {
            .content-container { max-width: 768px; }
        }
        
        .stacked-image { width: 100%; display: block; height: auto; }
        
        .package-card {
            border: 2px solid #f1f5f9;
            border-radius: 24px;
            padding: 2px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            background: #fff;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .package-card img { width: 100%; border-radius: 22px; display: block; }
        .package-card.active {
            border-color: #007bff; /* Blue border like mockup */
            border-width: 4px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .package-card.active::after {
            content: '✓'; position: absolute; top: 10px; right: 10px;
            background: #28a745; color: #white; width: 24px; height: 24px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: bold; z-index: 10; color: white;
        }
        
        .popular-badge {
            position: absolute; top: 0; left: 50%; transform: translateX(-50%);
            background: #ef4444; color: #fff;
            padding: 6px 15px; border-radius: 0 0 15px 15px;
            font-size: 11px; font-weight: 900; z-index: 5;
        }

        .cta-fixed {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 90;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
            padding: 15px 20px; border-top: 1px solid #eee;
            display: flex; gap: 15px; align-items: center; justify-content: center;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease-in-out;
        }
        .cta-hidden { transform: translateY(100%); opacity: 0; pointer-events: none; }

        .btn-order {
            background: var(--primary); color: #fff;
            font-weight: 800; text-transform: uppercase;
            padding: 18px 40px; border-radius: 20px;
            box-shadow: 0 10px 30px -10px var(--primary);
            transition: all 0.2s;
        }
        .btn-order:active { transform: scale(0.95); }

        .form-input {
            width: 100%; background: #f8fafc; border: 1px solid #e2e8f0;
            padding: 16px; border-radius: 16px; outline: none;
            transition: all 0.2s; font-weight: 500;
        }
        .form-input:focus { border-color: var(--primary); background: #fff; }

        .video-btn {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 80px; height: 80px; background: rgba(255,255,255,0.3);
            backdrop-blur: 10px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; border: 2px solid #fff;
        }

        /* Animations */
        @keyframes pulse-custom {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .animate-pulse-slow { animation: pulse-custom 2s infinite ease-in-out; }

        /* Payment Method Styles */
        .payment-method-card {
            border: 2px solid #f1f5f9;
            border-radius: 20px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .payment-method-card.active {
            border-color: var(--primary);
            background: rgba(var(--primary-rgb, 0, 123, 255), 0.05);
        }
        .payment-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
        }
        .payment-method-card.active .payment-icon {
            background: var(--primary);
            color: #fff;
        }
        .card-input-group {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 20px;
            margin-top: 15px;
        }

        /* Card Visual */
        .card-visual {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 20px;
            padding: 24px;
            color: #fff;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }
        .card-visual::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
        }
        .card-chip {
            width: 45px; height: 35px; background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 100%);
            border-radius: 6px; margin-bottom: 20px; position: relative;
        }
        .card-visual-number { font-size: 18px; font-weight: 800; letter-spacing: 2px; margin-bottom: 20px; font-family: 'Courier New', Courier, monospace; }
        .card-visual-footer { display: flex; justify-content: space-between; align-items: flex-end; }
        .card-visual-label { font-size: 8px; font-weight: 800; text-transform: uppercase; opacity: 0.5; margin-bottom: 4px; }
        .card-visual-val { font-size: 12px; font-weight: 800; text-transform: uppercase; }
    </style>
</head>
<body class="bg-slate-50" style="padding-top: 72px;">
    {!! $config->body_scripts !!}

    <!-- Aciliyet Sayacı (Full Width) -->
    <div class="sticky-countdown">
        <div class="w-full flex items-center justify-between px-4 sm:px-8">
            <div class="text-[10px] leading-tight font-black uppercase text-white/90">
                İndirimin Bitmesine<br>Kalan Süre
            </div>
            
            <div class="flex items-center gap-2">
                <div class="timer-box">
                    <span id="master-min" class="timer-val">00</span>
                    <span class="timer-label">Dakİka</span>
                </div>
                <span class="text-xl font-black opacity-50">:</span>
                <div class="timer-box">
                    <span id="master-sec" class="timer-val">00</span>
                    <span class="timer-label">Sanİye</span>
                </div>
            </div>

            <a href="#order-form" class="btn-mini-order">
                SİPARİŞ VER
            </a>
        </div>
    </div>

    <div class="content-container shadow-2xl min-h-screen pb-40 relative overflow-x-hidden">
        
        <!-- Üst Görsel / Galeri Başlangıcı -->
        <div class="w-full">
            @forelse($domain->gallery as $img)
                @if($img->video_url)
                    <div class="relative w-full cursor-pointer" onclick="openGlobalVideo('{{ $img->video_url }}')">
                        <img src="{{ $img->image_url }}" class="w-full block h-auto" loading="lazy" alt="Video Tanıtımı">
                        <div class="video-btn">
                            <svg class="w-12 h-12 text-white fill-current" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.333-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                        </div>
                    </div>
                @else
                    <a href="{{ $img->link_target ?? '#order-form' }}" class="block w-full">
                        <img src="{{ $img->image_url }}" class="w-full block h-auto" loading="lazy" alt="Ürün Bilgisi">
                    </a>
                @endif
            @empty
                <div class="p-20 text-center text-slate-300">
                    <p class="font-bold">Henüz ürün görseli yüklenmemiş.</p>
                </div>
            @endforelse
        </div>

        <!-- Stok Geri Sayımı -->
        @if($config->stock_countdown_start)
        <div class="stock-countdown-container mt-10 mb-6 mx-4 sm:mx-0 overflow-hidden rounded-3xl shadow-xl border-4 border-white">
            <div class="p-5 text-center" style="background-color: var(--primary);">
                <h3 class="text-white text-xl font-black uppercase tracking-tight leading-tight">Bilgilerinizi Hemen Doldurun</h3>
                <h2 class="text-white text-3xl font-black uppercase tracking-tighter mt-1">İNDİRİMİ KAÇIRMAYIN !</h2>
            </div>
            <div class="p-5 flex items-center justify-between gap-4" style="background-color: var(--secondary);">
                <div class="flex-1">
                    <h4 class="text-white font-black text-lg leading-tight">İndirim için Stok Sayısı</h4>
                    <p class="text-white/70 text-[11px] font-bold leading-snug mt-1">Depodaki indirimli stok sayısını gösterir ve günceldir. Stoklar bittiğinde indirimde sona erer.</p>
                </div>
                <div class="bg-white rounded-3xl shadow-[0_8px_0_rgba(0,0,0,0.15)] px-8 py-5 min-w-[110px] text-center border-2 border-white/20">
                    <span id="stock-count" class="text-5xl font-black text-slate-900 tabular-nums">{{ $config->stock_countdown_start }}</span>
                </div>
            </div>
        </div>
        <script>
            function initStockCountdown() {
                const stockEl = document.getElementById('stock-count');
                if (!stockEl) return;

                const storageKey = 'stock_count_{{ $domain->id }}';
                let currentStock = parseInt(stockEl.innerText);
                
                // Get from session storage to keep it consistent for the visitor
                let savedStock = sessionStorage.getItem(storageKey);
                if (savedStock) {
                    currentStock = parseInt(savedStock);
                    stockEl.innerText = currentStock;
                }

                function decreaseStock() {
                    // Random decrease between 1-3 units
                    // Only decrease if stock is above a minimum threshold (e.g. 12)
                    if (currentStock > 12) {
                        const drop = Math.floor(Math.random() * 2) + 1;
                        currentStock -= drop;
                        
                        // Ensure we don't drop below the minimum threshold suddenly
                        if (currentStock < 12) currentStock = 12;

                        // Update UI with a slight animation if desired, but simple text update is usually enough
                        stockEl.innerText = currentStock;
                        
                        // Save the new value
                        sessionStorage.setItem(storageKey, currentStock);
                    }

                    // Schedule next decrease between 8 to 20 seconds
                    const nextTick = Math.floor(Math.random() * 12000) + 8000;
                    setTimeout(decreaseStock, nextTick);
                }

                // Initial save if not already saved
                if (!savedStock) {
                    sessionStorage.setItem(storageKey, currentStock);
                }

                // Start the countdown loop with an initial delay
                setTimeout(decreaseStock, Math.floor(Math.random() * 5000) + 3000);
            }
            // Add to window load
            window.addEventListener('load', initStockCountdown);
        </script>
        @endif

        <!-- Paket Seçimi -->
        <div class="p-6 md:p-10 bg-white border-t border-slate-100" id="order-form">
            <h2 class="text-center text-2xl font-black mb-8 tracking-tighter uppercase italic">Kampanyalı Paketler</h2>
            
            <div class="grid grid-cols-1 gap-2 mb-6">
                @foreach($offers as $offer)
                    @php
                        $normalUrl = $offer->offer_image ? \App\Models\Setting::mediaUrl('uploads/offers/'.$offer->offer_image, null, $domainBunnyBase) : 'https://placehold.co/800';
                        $activeUrl = $offer->active_image ? \App\Models\Setting::mediaUrl('uploads/offers/'.$offer->active_image, null, $domainBunnyBase) : $normalUrl;
                        
                        // Calculate price from items or fallback
                        $offerPrice = $offer->items->isNotEmpty()
                            ? $offer->items->sum(function($item) { return $item->quantity * $item->price; })
                            : $offer->price;
                    @endphp
                    <div class="package-card {{ $offer->is_popular ? 'active' : '' }}" 
                         data-normal="{{ $normalUrl }}" 
                         data-active="{{ $activeUrl }}"
                         data-offer-id="{{ $offer->id }}"
                         data-offer-price="{{ $offerPrice }}"
                         onclick="selectPackage(this, '{{ $offer->id }}', '{{ $offerPrice }}')">
                        @if($offer->is_popular)
                            <div class="popular-badge italic">EN ÇOK TERCİH EDİLEN</div>
                        @endif
                        
                        <img src="{{ $offer->is_popular ? $activeUrl : $normalUrl }}" alt="{{ $offer->offer_name }}">
                    </div>
                @endforeach
            </div>



            <!-- Sipariş ve Giriş Bilgileri -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-[1px] flex-1 bg-slate-200"></div>
                    <span class="text-[10px] font-black text-slate-400 uppercase italic">GİRİŞ BİLGİLERİ</span>
                    <div class="h-[1px] flex-1 bg-slate-200"></div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <input type="text" id="cust_name" placeholder="Ad Soyad" class="form-input">
                    <div class="relative">
                        <input type="tel" id="cust_phone" placeholder="Telefon (05XX XXX XX XX)" class="form-input" maxlength="17">
                        <div id="phone_error" class="hidden text-[10px] font-bold text-red-500 mt-1 pl-2">Lütfen geçerli bir telefon numarası girin</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <input type="email" id="cust_email" placeholder="E-Posta Adresi (Opsiyonel)" class="form-input">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <select id="cust_city" class="form-input" onchange="updateDistricts()">
                        <option value="">Şehir Seçin</option>
                    </select>
                    <select id="cust_district" class="form-input">
                        <option value="">İlçe Seçin</option>
                    </select>
                </div>
                
                <textarea id="cust_address" placeholder="Tam Adresiniz (Cadde, Mahalle, Kapı No)" class="form-input" rows="3"></textarea>
            </div>

            <!-- Ödeme Yöntemi Seçimi -->
            <div class="mt-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-[1px] flex-1 bg-slate-200"></div>
                    <span class="text-[10px] font-black text-slate-400 uppercase italic">Ödeme Yöntemi</span>
                    <div class="h-[1px] flex-1 bg-slate-200"></div>
                </div>

                <div class="grid grid-cols-1 {{ ($config->allow_credit_card && $config->paymentProvider) ? 'sm:grid-cols-2' : '' }} gap-3">
                    <div class="payment-method-card active" onclick="selectPaymentMethod('cod')">
                        <div class="payment-icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <div class="text-sm font-black text-slate-900 uppercase leading-none">KAPIDA NAKİT ÖDEME</div>
                            <div class="text-[10px] text-slate-500 font-bold mt-1">Nakit olarak kargo görevlisine ödenir.</div>
                        </div>
                    </div>

                    @if($config->allow_credit_card && $config->paymentProvider)
                    <div class="payment-method-card" onclick="selectPaymentMethod('credit_card')">
                        <div class="payment-icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <div>
                            <div class="text-sm font-black text-slate-900 uppercase leading-none">KREDİ KARTI</div>
                            <div class="text-[10px] text-slate-500 font-bold mt-1">Online Güvenli Ödeme</div>
                        </div>
                    </div>
                    @endif
                </div>

                @if($config->allow_credit_card && $config->paymentProvider)
                <!-- Kredi Kartı Formu -->
                <div id="credit-card-form" class="card-input-group hidden animate-in fade-in slide-in-from-top-2 duration-300">
                    <!-- Kart Görseli -->
                    <div class="card-visual">
                        <div class="flex justify-between items-start mb-6">
                            <div class="card-chip"></div>
                            <svg class="w-10 h-10 text-white/20" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                        </div>
                        <div class="card-visual-number" id="v-cc-number">**** **** **** ****</div>
                        <div class="card-visual-footer">
                            <div>
                                <div class="card-visual-label">KART SAHİBİ</div>
                                <div class="card-visual-val" id="v-cc-name">AD SOYAD</div>
                            </div>
                            <div class="text-right">
                                <div class="card-visual-label">S.K.T</div>
                                <div class="card-visual-val" id="v-cc-expiry">AA / YY</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 ml-2">Kart Üzerindeki İsim</label>
                            <input type="text" id="cc_name" placeholder="AD SOYAD" class="form-input bg-white" oninput="updateCardVisual()">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 ml-2">Kart Numarası</label>
                            <input type="tel" id="cc_number" placeholder="0000 0000 0000 0000" class="form-input bg-white" maxlength="19" oninput="updateCardVisual()">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 ml-2">S.K.T (AY/YIL)</label>
                                <input type="tel" id="cc_expiry" placeholder="AA / YY" class="form-input bg-white" maxlength="7" oninput="updateCardVisual()">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-1 ml-2">CVC</label>
                                <input type="tel" id="cc_cvc" placeholder="000" class="form-input bg-white" maxlength="4">
                            </div>
                        </div>
                        <div class="flex items-start gap-2 p-2">
                            <div class="mt-1">
                                <svg class="w-4 h-4 text-brand-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                            </div>
                            <p class="text-[10px] text-slate-500 font-medium leading-snug">Kart bilgileriniz 256-bit SSL ile şifrelenir ve doğrudan {{ $config->paymentProvider->name }} altyapısına iletilir. Sunucularımızda saklanmaz.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sipariş Özeti -->
            <div class="summary-box mb-8 mt-8">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Sipariş Özeti</h3>
                <div class="summary-row">
                    <div>Ara Toplam</div>
                    <div id="summary-subtotal">0,00 ₺</div>
                </div>
                <div class="summary-row">
                    <div>Kargo Ücreti</div>
                    <div class="free-text">ÜCRETSİZ</div>
                </div>
                <div class="summary-row">
                    <div>Kapıda Ödeme Hizmet Bedeli</div>
                    <div class="free-text">ÜCRETSİZ</div>
                </div>
                <div class="grand-total-row">
                    <div class="font-black text-slate-800 uppercase tracking-tighter">TOPLAM TUTAR</div>
                    <div class="grand-total-price text-brand-600" id="summary-total">0,00 ₺</div>
                </div>
            </div>
            
            <button onclick="submitOrder()" id="btn-submit" class="w-full text-white font-black py-6 rounded-full text-xl shadow-xl transition-all active:scale-95 disabled:opacity-50" style="background-color: var(--primary); box-shadow: 0 10px 30px -10px var(--primary);">
                Siparişi Tamamla
            </button>
            
            <div class="mt-8 text-center">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-center gap-2">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    256-BIT SSL GÜVENLİ ALTYAPI
                </p>
            </div>
        </div>
    </div>

    <!-- Sabit Alt Bar -->
    <div id="floating-cta" class="cta-fixed md:max-w-[640px] md:mx-auto">
        <div class="hidden md:block flex-1">
             <div class="text-[10px] text-slate-400 font-bold uppercase">Seçili Paket Toplamı</div>
             <div id="display-price" class="text-2xl font-black">--- ₺</div>
        </div>
        <a href="#order-form" class="btn-order flex-1 animate-pulse-slow text-center flex items-center justify-center">SİPARİŞİ TAMAMLA</a>
    </div>

    <!-- WhatsApp Floating -->
    @if($config->whatsapp_number)
    <a href="https://wa.me/{{ $config->whatsapp_number }}" target="_blank" class="fixed bottom-24 right-6 bg-[#25d366] text-white p-4 rounded-full shadow-2xl z-[80] hover:scale-110 transition">
        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.438 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
    </a>
    @endif

    <!-- Video Modal -->
    <div id="vModal" class="fixed inset-0 bg-black/95 z-[200] hidden items-center justify-center p-4" onclick="closeGlobalVideo()">
        <button onclick="closeGlobalVideo()" class="absolute top-6 right-6 text-white hover:text-slate-300 transition-colors z-[210]">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <div class="relative w-full max-w-4xl aspect-video rounded-3xl overflow-hidden bg-black shadow-2xl" onclick="event.stopPropagation()">
            <iframe id="vIframe" src="" class="w-full h-full" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
        </div>
    </div>

    <script>
        // Funnel Tracking Logic
        (function() {
            const domainId = {{ $domain->id }};
            let sessionId = localStorage.getItem('funnel_session_id');
            if (!sessionId) {
                sessionId = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                localStorage.setItem('funnel_session_id', sessionId);
            }

            const track = (eventType, eventValue = null) => {
                fetch('/api/funnel/track', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        domain_id: domainId,
                        session_id: sessionId,
                        event_type: eventType,
                        event_value: eventValue
                    })
                });
            };

            // Page View
            track('page_view');

            // Scroll Tracking
            let scrollPoints = { 25: false, 50: false, 75: false };
            window.addEventListener('scroll', () => {
                const h = document.documentElement, 
                      b = document.body,
                      st = 'scrollTop',
                      sh = 'scrollHeight';
                const percent = (h[st]||b[st]) / ((h[sh]||b[sh]) - h.clientHeight) * 100;

                [25, 50, 75].forEach(point => {
                    if (percent >= point && !scrollPoints[point]) {
                        scrollPoints[point] = true;
                        track('scroll_' + point);
                    }
                });
            });

            // CTA Clicks
            document.addEventListener('click', (e) => {
                const cta = e.target.closest('.btn-mini-order, .btn-order, #btn-submit');
                if (cta) {
                    track('cta_click', cta.innerText.trim());
                }
            });

            // Form Interactions
            document.addEventListener('focusin', (e) => {
                if (e.target.classList.contains('form-input')) {
                    if (!window.formOpened) {
                        window.formOpened = true;
                        track('form_open');
                        if (selectedOfferId && selectedOfferPrice !== null) {
                            trackAddToCart(selectedOfferId, selectedOfferPrice);
                        }
                        if (window.fbq) {
                            fbq('track', 'InitiateCheckout');
                        }
                    }
                }
            });

            // Hook into submitOrder
            window.funnelTrack = track;
        })();

        let selectedOfferId = null;
        let selectedOfferPrice = null;
        let addToCartTracked = false;

        // Turkey City-District Data
        const turkeyData = {
            "Adana": ["Aladağ", "Ceyhan", "Çukurova", "Feke", "İmamoğlu", "Karaisalı", "Karataş", "Kozan", "Pozantı", "Saimbeyli", "Sarıçam", "Seyhan", "Tufanbeyli", "Yumurtalık", "Yüreğir"],
            "Adıyaman": ["Besni", "Çelikhan", "Gerger", "Gölbaşı", "Kahta", "Merkez", "Samsat", "Sincik", "Tut"],
            "Afyonkarahisar": ["Başmakçı", "Bayat", "Bolvadin", "Çay", "Çobanlar", "Dazkırı", "Dinar", "Emirdağ", "Evciler", "Hocalar", "İhsaniye", "İscehisar", "Kızılören", "Merkez", "Sandıklı", "Sinanpaşa", "Sultanhisar", "Şuhut"],
            "Ağrı": ["Diyadin", "Doğubayazıt", "Eleşkirt", "Hamur", "Merkez", "Patnos", "Taşlıçay", "Tutak"],
            "Amasya": ["Göynücek", "Gümüşhacıköy", "Hamamözü", "Merkez", "Merzifon", "Suluova", "Taşova"],
            "Ankara": ["Akyurt", "Altındağ", "Ayaş", "Bala", "Beypazarı", "Çamlıdere", "Çankaya", "Çubuk", "Elmadağ", "Etimesgut", "Evren", "Gölbaşı", "Güdül", "Haymana", "Kahramankazan", "Kalecik", "Keçiören", "Kızılcahamam", "Mamak", "Nallıhan", "Polatlı", "Pursaklar", "Sincan", "Şereflikoçhisar", "Yenimahalle"],
            "Antalya": ["Akseki", "Aksu", "Alanya", "Demre", "Döşemealtı", "Elmalı", "Finike", "Gazipaşa", "Gündoğmuş", "İbradı", "Kaş", "Kemer", "Kepez", "Konyaaltı", "Korkuteli", "Kumluca", "Manavgat", "Muratpaşa", "Serik"],
            "Artvin": ["Ardanuç", "Arhavi", "Borçka", "Hopa", "Kemalpaşa", "Merkez", "Murgul", "Şavşat", "Yusufeli"],
            "Aydın": ["Bozdoğan", "Buharkent", "Çine", "Didim", "Efeler", "Germencik", "İncirliova", "Karacasu", "Karpuzlu", "Koçarlı", "Köşk", "Kuşadası", "Kuyucak", "Nazilli", "Söke", "Sultanhisar", "Yenipazar"],
            "Balıkesir": ["Altıeylül", "Ayvalık", "Balya", "Bandırma", "Bigadiç", "Burhaniye", "Dursunbey", "Edremit", "Erdek", "Gömeç", "Gönen", "Havran", "İvrindi", "Karesi", "Kepsut", "Manyas", "Marmara", "Savaştepe", "Sındırgı", "Susurluk"],
            "Bilecik": ["Bozüyük", "Gölpazarı", "İnhisar", "Merkez", "Osmaneli", "Pazaryeri", "Söğüt", "Yenipazar"],
            "Bingöl": ["Adaklı", "Genç", "Karlıova", "Kiğı", "Merkez", "Solhan", "Yayladere", "Yedisu"],
            "Bitlis": ["Adilcevaz", "Ahlat", "Güroymak", "Hizan", "Merkez", "Mutki", "Tatvan"],
            "Bolu": ["Dörtdivan", "Gerede", "Göynük", "Kıbrıscık", "Mengen", "Merkez", "Mudurnu", "Seben", "Yeniçağa"],
            "Burdur": ["Ağlasun", "Altınyayla", "Bucak", "Çavdır", "Çeltikçi", "Gölhisar", "Karamanlı", "Kemer", "Merkez", "Tefenni", "Yeşilova"],
            "Bursa": ["Büyükorhan", "Gemlik", "Gürsu", "Harmancık", "İnegöl", "İznik", "Karacabey", "Keles", "Kestel", "Mudanya", "Mustafakemalpaşa", "Nilüfer", "Orhaneli", "Orhangazi", "Osmangazi", "Yenişehir", "Yıldırım"],
            "Çanakkale": ["Ayvacık", "Bayramiç", "Biga", "Bozcaada", "Çan", "Eceabat", "Ezine", "Gelibolu", "Gökçeada", "Lapseki", "Merkez", "Yenice"],
            "Çankırı": ["Atkaracalar", "Bayramören", "Çerkeş", "Eskipazar", "Ilgaz", "Kızılırmak", "Korgun", "Kurşunlu", "Merkez", "Orta", "Şabanözü", "Yapraklı"],
            "Çorum": ["Alaca", "Bayat", "Boğazkale", "Dodurga", "İskilip", "Kargı", "Laçin", "Mecitözü", "Merkez", "Oğuzlar", "Ortaköy", "Osmancık", "Sungurlu", "Uğurludağ"],
            "Denizli": ["Acıpayam", "Babadağ", "Baklan", "Bekilli", "Beyağaç", "Bozkurt", "Buldan", "Çal", "Çameli", "Çardak", "Çivril", "Güney", "Honaz", "Kale", "Merkezefendi", "Pamukkale", "Sarayköy", "Serinhisar", "Tavas"],
            "Diyarbakır": ["Bağlar", "Bismil", "Çermik", "Çınar", "Çüngüş", "Dicle", "Eğil", "Ergani", "Hani", "Hazro", "Kayapınar", "Kocaköy", "Kulp", "Lice", "Silvan", "Sur", "Yenişehir"],
            "Edirne": ["Enez", "Havsa", "İpsala", "Keşan", "Lalapaşa", "Meriç", "Merkez", "Süloğlu", "Uzunköprü"],
            "Elazığ": ["Ağın", "Alacakaya", "Arıcak", "Baskil", "Karakoçan", "Keban", "Kovancılar", "Maden", "Merkez", "Palu", "Sivrice"],
            "Erzincan": ["Çayırlı", "İliç", "Kemah", "Kemaliye", "Merkez", "Otlukbeli", "Refahiye", "Tercan", "Üzümlü"],
            "Erzurum": ["Aşkale", "Aziziye", "Çat", "Hınıs", "Horasan", "İspir", "Karaçoban", "Karayazı", "Köprüköy", "Narman", "Oltu", "Olur", "Palandöken", "Pasinline", "Pazaryolu", "Şenkaya", "Tekman", "Tortum", "Uzundere", "Yakutiye"],
            "Eskişehir": ["Alpu", "Beylikova", "Çifteler", "Günyüzü", "Han", "İnönü", "Mahmudiye", "Mihalgazi", "Mihalıççık", "Odunpazarı", "Sarıcakaya", "Seyitgazi", "Sivrihisar", "Tepebaşı"],
            "Gaziantep": ["Araban", "İslahiye", "Karkamış", "Nizip", "Nurdağı", "Oğuzeli", "Şahinbey", "Şehitkamil", "Yavuzeli"],
            "Giresun": ["Alucra", "Bulancak", "Çamoluk", "Çanakçı", "Dereli", "Doğankent", "Espiye", "Eynesil", "Görele", "Güce", "Keşap", "Merkez", "Piraziz", "Şebinkarahisar", "Tirebolu", "Yağlıdere"],
            "Gümüşhane": ["Kelkit", "Köse", "Kürtün", "Merkez", "Şiran", "Torul"],
            "Hakkari": ["Çukurca", "Derecik", "Merkez", "Şemdinli", "Yüksekova"],
            "Hatay": ["Altınözü", "Antakya", "Arsuz", "Belen", "Defne", "Dörtyol", "Erzin", "Hassa", "İskenderun", "Kırıkhan", "Kumlu", "Payas", "Reyhanlı", "Samandağ", "Yayladağı"],
            "Isparta": ["Aksu", "Atabey", "Eğirdir", "Gelendost", "Gönen", "Keçiborlu", "Merkez", "Senirkent", "Sütçüler", "Şarkikaraağaç", "Uluborlu", "Yalvaç", "Yenişarbademli"],
            "Mersin": ["Akdeniz", "Anamur", "Aydıncık", "Bozyazı", "Çamlıyayla", "Erdemli", "Gülnar", "Mezitli", "Mut", "Silifke", "Tarsus", "Toroslar", "Yenişehir"],
            "İstanbul": ["Adalar", "Arnavutköy", "Ataşehir", "Avcılar", "Bağcılar", "Bahçelievler", "Bakırköy", "Başakşehir", "Bayrampaşa", "Beşiktaş", "Beykoz", "Beylikdüzü", "Beyoğlu", "Büyükçekmece", "Çatalca", "Çekmeköy", "Esenler", "Esenyurt", "Eyüpsultan", "Fatih", "Gaziosmanpaşa", "Güngören", "Kadıköy", "Kağıthane", "Kartal", "Küçükçekmece", "Maltepe", "Pendik", "Sancaktepe", "Sarıyer", "Silivri", "Sultanbeyli", "Sultangazi", "Şile", "Şişli", "Tuzla", "Ümraniye", "Üsküdar", "Zeytinburnu"],
            "İzmir": ["Aliağa", "Balçova", "Bayındır", "Bayraklı", "Bergama", "Beydağ", "Bornova", "Buca", "Çeşme", "Çiğli", "Dikili", "Foça", "Gaziemir", "Güzelbahçe", "Karabağlar", "Karaburun", "Karşıyaka", "Kemalpaşa", "Kınık", "Kiraz", "Konak", "Menderes", "Menemen", "Narlıdere", "Ödemiş", "Seferihisar", "Selçuk", "Tire", "Torbalı", "Urla"],
            "Kars": ["Akyaka", "Arpaçay", "Digor", "Kağızman", "Merkez", "Sarıkamış", "Selim", "Susuz"],
            "Kastamonu": ["Abana", "Ağlı", "Araç", "Azdavay", "Bozkurt", "Cide", "Çatalzeytin", "Daday", "Devrekani", "Doğanyurt", "Hanönü", "İhsangazi", "İnebolu", "Küre", "Merkez", "Pınarbaşı", "Seydiler", "Şenpazar", "Taşköprü", "Tosya"],
            "Kayseri": ["Akkışla", "Bünyan", "Develi", "Felahiye", "Hacılar", "İncesu", "Kocasinan", "Melikgazi", "Özvatan", "Pınarbaşı", "Sarıoğlan", "Sarız", "Talas", "Tomarza", "Yahyalı", "Yeşilhisar"],
            "Kırklareli": ["Babaeski", "Demirköy", "Kofçaz", "Lüleburgaz", "Merkez", "Pehlivanköy", "Pınarhisar", "Vize"],
            "Kırşehir": ["Akçakent", "Akpınar", "Boztepe", "Çiçekdağı", "Kaman", "Merkez", "Mucur"],
            "Kocaeli": ["Başiskele", "Çayırova", "Darica", "Derince", "Dilovası", "Gebze", "Gölcük", "İzmit", "Kandıra", "Karamürsel", "Kartepe", "Körfez"],
            "Konya": ["Ahırlı", "Akören", "Akşehir", "Altınekin", "Beyşehir", "Bozkır", "Cihanbeyli", "Çeltik", "Çumra", "Derbent", "Derebucak", "Doğanhisar", "Emirgazi", "Ereğli", "Güneysınır", "Hadim", "Halkapınar", "Hüyük", "Ilgın", "Kadınhanı", "Karapınar", "Karatay", "Kulu", "Meram", "Sarayönü", "Selçuklu", "Seydişehir", "Taşkent", "Tuzlukçu", "Yalıhüyük", "Yunak"],
            "Kütahya": ["Altıntaş", "Aslanapa", "Çavdarhisar", "Domaniç", "Dumlupınar", "Emet", "Gediz", "Hisarcık", "Merkez", "Pazarlar", "Şaphane", "Simav", "Tavşanlı"],
            "Malatya": ["Akçadağ", "Arapgir", "Arguvan", "Battalgazi", "Darende", "Doğanşehir", "Doğanyol", "Hekimhan", "Kale", "Kuluncak", "Pütürge", "Yazıhan", "Yeşilyurt"],
            "Manisa": ["Ahmetli", "Akhisar", "Alaşehir", "Demirci", "Gölmarmara", "Gördes", "Kırkağaç", "Köprübaşı", "Kula", "Salihli", "Sarıgöl", "Saruhanlı", "Selendi", "Soma", "Şehzadeler", "Turgutlu", "Yunusemre"],
            "Kahramanmaraş": ["Afşin", "Andırın", "Çağlayancerit", "Dulkadiroğlu", "Ekinözü", "Elbistan", "Göksun", "Nurhak", "Onikişubat", "Pazarcık", "Türkoğlu"],
            "Mardin": ["Artuklu", "Dargeçit", "Derik", "Kızıltepe", "Mazıdağı", "Midyat", "Nusaybin", "Ömerli", "Savur", "Yeşilli"],
            "Muğla": ["Bodrum", "Dalaman", "Datça", "Fethiye", "Kavaklıdere", "Köyceğiz", "Marmaris", "Menteşe", "Milas", "Ortaca", "Seydikemer", "Ula", "Yatağan"],
            "Muş": ["Bulanık", "Hasköy", "Korkut", "Malazgirt", "Merkez", "Varto"],
            "Nevşehir": ["Acıgöl", "Avanos", "Derinkuyu", "Gülşehir", "Hacıbektaş", "Kozaklı", "Merkez", "Ürgüp"],
            "Niğde": ["Altunhisar", "Bor", "Çamardı", "Çiftlik", "Merkez", "Ulukışla"],
            "Ordu": ["Akkuş", "Altınordu", "Aybastı", "Çamaş", "Çatalpınar", "Çaybaşı", "Fatsa", "Gölköy", "Gülyalı", "Gürgentepe", "İkizce", "Kabadüz", "Kabataş", "Korgan", "Kumru", "Mesudiye", "Perşembe", "Ulubey", "Ünye"],
            "Rize": ["Ardeşen", "Çamlıhemşin", "Çayeli", "Derepazarı", "Fındıklı", "Güneysu", "Hemşin", "İkizdere", "İyidere", "Kalkandere", "Merkez", "Pazar"],
            "Sakarya": ["Adapazarı", "Akyazı", "Arifiye", "Erenler", "Ferizli", "Geyve", "Hendek", "Karapürçek", "Karasu", "Kaynarca", "Kocaali", "Pamukova", "Sapanca", "Serdivan", "Söğütlü", "Taraklı"],
            "Samsun": ["19 Mayıs", "Alaçam", "Asarcık", "Atakum", "Ayvacık", "Bafra", "Canik", "Çarşamba", "Havza", "İlkadım", "Kavak", "Ladik", "Salıpazarı", "Tekkeköy", "Terme", "Vezirköprü", "Yakakent"],
            "Siirt": ["Baykan", "Eruh", "Kurtalan", "Merkez", "Pervari", "Şirvan", "Tillo"],
            "Sinop": ["Ayancık", "Boyabat", "Dikmen", "Durağan", "Erfelek", "Gerze", "Merkez", "Saraydüzü", "Türkeli"],
            "Sivas": ["Akıncılar", "Altınyayla", "Divriği", "Doğanşar", "Gemerek", "Gölova", "Gürün", "Hafik", "İmranlı", "Kangal", "Koyulhisar", "Merkez", "Şarkışla", "Suşehri", "Ulaş", "Yıldızeli", "Zara"],
            "Tekirdağ": ["Çerkezköy", "Çorlu", "Ergene", "Hayrabolu", "Kapaklı", "Malkara", "Marmaraereğlisi", "Muratlı", "Saray", "Süleymanpaşa", "Şarköy"],
            "Tokat": ["Almus", "Artova", "Başçiftlik", "Erbaa", "Merkez", " Niksar", "Pazar", "Reşadiye", "Sulusaray", "Turhal", "Yeşilyurt", "Zile"],
            "Trabzon": ["Akçaabat", "Araklı", "Arsin", "Beşikdüzü", "Çarşıbaşı", "Çaykara", "Dernekpazarı", "Düzköy", "Hayrat", "Köprübaşı", "Maçka", "Of", "Ortahisar", "Sürmene", "Şalpazarı", "Tonya", "Vakfıkebir", "Yomra"],
            "Tunceli": ["Çemişgezek", "Hozat", "Mazgirt", "Merkez", "Nazımiye", "Ovacık", "Pertek", "Pülümür"],
            "Şanlıurfa": ["Akçakale", "Birecik", "Bozova", "Ceylanpınar", "Eyyübiye", "Halfeti", "Haliliye", "Harran", "Hilvan", "Karaköprü", "Siverek", "Suruç", "Viranşehir"],
            "Uşak": ["Banaz", "Eşme", "Karahallı", "Merkez", "Sivaslı", "Ulubey"],
            "Van": ["Bahçesaray", "Başkale", "Çaldıran", "Çatak", "Edremit", "Erciş", "Gevaş", "Gürpınar", "İpekyolu", "Muradiye", "Özalp", "Saray", "Tuşba"],
            "Yozgat": ["Akdağmadeni", "Aydıncık", "Boğazlıyan", "Çandır", "Çayıralan", "Çekerek", "Kadışehri", "Merkez", "Saraykent", "Sarıkaya", "Sorgun", "Şefaatli", "Yenifakılı", "Yerköy"],
            "Zonguldak": ["Alaplı", "Çaycuma", "Devrek", "Ereğli", "Gökçebey", "Kilimli", "Kozlu", "Merkez"],
            "Aksaray": ["Ağaçören", "Eskil", "Gülağaç", "Güzelyurt", "Merkez", "Sarıyahşi", "Sultanhanı", "Ortaköy"],
            "Bayburt": ["Aydıntepe", "Demirözü", "Merkez"],
            "Karaman": ["Ayrancı", "Başyayla", "Ermenek", "Kazımkarabekir", "Merkez", "Sarıveliler"],
            "Kırıkkale": ["Bahşili", "Balışeyh", "Çelebi", "Delice", "Karakeçili", "Keskin", "Merkez", "Sulakyurt", "Yahşihan"],
            "Batman": ["Beşiri", "Gercüş", "Hasankeyf", "Kozluk", "Merkez", "Sason"],
            "Şırnak": ["Beytüşşebap", "Cizre", "Güçlükonak", "İdil", "Merkez", "Silopi", "Uludere"],
            "Bartın": ["Amasra", "Kurucaşile", "Merkez", "Ulus"],
            "Ardahan": ["Çıldır", "Damal", "Göle", "Hanak", "Merkez", "Posof"],
            "Iğdır": ["Aralık", "Karakoyunlu", "Merkez", "Tuzluca"],
            "Yalova": ["Altınova", "Armutlu", "Çınarcık", "Çiftlikköy", "Merkez", "Termal"],
            "Karabük": ["Eflani", "Eskipazar", "Merkez", "Ovacık", "Safranbolu", "Yenice"],
            "Kilis": ["Elbeyli", "Merkez", "Musabeyli", "Polateli"],
            "Osmaniye": ["Bahçe", "Düziçi", "Hasanbeyli", "Kadirli", "Merkez", "Sumbas", "Toprakkale"],
            "Düzce": ["Akçakoca", "Cumayeri", "Çilimli", "Gölyaka", "Gümüşova", "Kaynaşlı", "Merkez", "Yığılca"]
        };

        function updateDistricts() {
            const city = document.getElementById('cust_city').value;
            const districtSelect = document.getElementById('cust_district');
            districtSelect.innerHTML = '<option value="">İlçe Seçin</option>';
            
            if (turkeyData[city]) {
                turkeyData[city].forEach(d => {
                    let opt = document.createElement('option');
                    opt.value = d;
                    opt.innerText = d;
                    districtSelect.appendChild(opt);
                });
            }
        }

        function initCities() {
            const citySelect = document.getElementById('cust_city');
            Object.keys(turkeyData).sort().forEach(c => {
                let opt = document.createElement('option');
                opt.value = c;
                opt.innerText = c;
                citySelect.appendChild(opt);
            });
        }

        function trackAddToCart(id, price, force = false) {
            if (addToCartTracked && !force) return;
            if (!window.fbq || typeof window.fbq !== 'function') return;

            fbq('track', 'AddToCart', {
                content_ids: [id],
                content_type: 'product',
                value: parseFloat(price),
                currency: 'TRY'
            });

            addToCartTracked = true;
        }

        function selectPackage(el, id, price, shouldTrack = false) {
            document.querySelectorAll('.package-card').forEach(card => {
                card.classList.remove('active');
                const img = card.querySelector('img');
                const normal = card.getAttribute('data-normal');
                if (img && normal) img.src = normal;
            });

            el.classList.add('active');
            const activeImg = el.getAttribute('data-active');
            const targetImg = el.querySelector('img');
            if (targetImg && activeImg) {
                targetImg.src = activeImg;
            }

            selectedOfferId = id;
            selectedOfferPrice = price;
            const formattedPrice = parseFloat(price).toFixed(2).replace('.', ',');
            document.getElementById('display-price').innerText = formattedPrice + ' ₺';
            document.getElementById('summary-subtotal').innerText = formattedPrice + ' ₺';
            document.getElementById('summary-total').innerText = formattedPrice + ' ₺';

            if (shouldTrack) {
                trackAddToCart(id, price);
            }
        }

        function openGlobalVideo(url) {
            const modal = document.getElementById('vModal');
            const iframe = document.getElementById('vIframe');
            let videoId = '';
            
            if (url.includes('youtube.com/watch?v=')) {
                videoId = url.split('v=')[1].split('&')[0];
            } else if (url.includes('youtu.be/')) {
                videoId = url.split('youtu.be/')[1].split('?')[0];
            } else if (url.includes('youtube.com/shorts/')) {
                videoId = url.split('shorts/')[1].split('?')[0];
            } else if (url.includes('youtube.com/embed/')) {
                videoId = url.split('embed/')[1].split('?')[0];
            }

            if (videoId) {
                iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            } else {
                // If it's already a clean embed URL or from another provider
                let finalUrl = url;
                if (!url.includes('?') && !url.includes('embed')) {
                    // Try to append autoplay if it's likely an embeddable link
                    finalUrl += (url.includes('?') ? '&' : '?') + 'autoplay=1';
                }
                iframe.src = finalUrl;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeGlobalVideo() {
            const modal = document.getElementById('vModal');
            document.getElementById('vIframe').src = '';
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Phone Masking & Validation
        const phoneInput = document.getElementById('cust_phone');
        const phoneError = document.getElementById('phone_error');
        const nameInput = document.getElementById('cust_name');

        // Name Validation (Only Letters & Spaces)
        nameInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^a-zA-ZçÇğĞıİöÖşŞüÜ\s]/g, '');
        });

        phoneInput.addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
            
            if (!x[2] && x[1] && x[1] !== '0') {
                e.target.value = '0 (' + x[1];
                return;
            }

            e.target.value = !x[2] ? x[1] : x[1] + ' (' + x[2] + ') ' + x[3] + (x[4] ? ' ' + x[4] : '') + (x[5] ? ' ' + x[5] : '');
            
            const digits = e.target.value.replace(/\D/g, '');
            if (digits.length > 0 && digits.length < 11) {
                phoneInput.style.borderColor = '#ef4444';
                phoneError.classList.remove('hidden');
            } else {
                phoneInput.style.borderColor = '';
                phoneError.classList.add('hidden');
            }
        });

        // Timer Logic
        const TIMER_DURATION = {{ ($config->countdown_minutes ?? 15) * 60 }};
        const STORAGE_KEY = 'cf_timer_{{ $domain->id }}';

        function initCountdown() {
            let start = parseInt(localStorage.getItem(STORAGE_KEY));
            if (!start || isNaN(start)) {
                start = Date.now();
                localStorage.setItem(STORAGE_KEY, start);
            }

            const displayMin = document.getElementById('master-min');
            const displaySec = document.getElementById('master-sec');
            
            function update() {
                const now = Date.now();
                let diffInSec = Math.floor((now - start) / 1000);
                let remaining = TIMER_DURATION - diffInSec;

                if (remaining <= 0) {
                    start = Date.now();
                    localStorage.setItem(STORAGE_KEY, start);
                    remaining = TIMER_DURATION;
                }

                if (remaining > 0) {
                    let m = Math.floor(remaining / 60).toString().padStart(2, '0');
                    let s = (remaining % 60).toString().padStart(2, '0');
                    displayMin.innerText = m;
                    displaySec.innerText = s;
                }

                setTimeout(update, 1000);
            }
            update();
        }

        async function submitOrder() {
            if (window.funnelTrack) window.funnelTrack('form_submit');
            if(!selectedOfferId) { alert('Lütfen bir paket seçin!'); return; }
            if (selectedOfferPrice !== null) {
                trackAddToCart(selectedOfferId, selectedOfferPrice, true);
            }
            
            const name = document.getElementById('cust_name').value.trim();
            const phone = document.getElementById('cust_phone').value;
            const email = document.getElementById('cust_email').value;
            const idNumber = "11111111111";
            const city = document.getElementById('cust_city').value;
            const district = document.getElementById('cust_district').value;
            const address = document.getElementById('cust_address').value;

            if(!name || !phone || !city || !district || !address) {
                alert('Lütfen tüm alanları eksiksiz doldurun!');
                return;
            }

            // Name Validation (At least 2 words)
            const nameParts = name.split(' ').filter(part => part.length >= 2);
            if (nameParts.length < 2) {
                alert('Lütfen Ad ve Soyadınızı tam olarak girin!');
                document.getElementById('cust_name').focus();
                return;
            }

            // Final Phone Validation
            const digits = phone.replace(/\D/g, '');
            if (digits.length < 11) {
                alert('Lütfen geçerli bir telefon numarası girin!');
                document.getElementById('cust_phone').focus();
                return;
            }

            const btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.innerText = 'Siparişiniz Alınıyor...';

            const paymentMethod = document.querySelector('.payment-method-card.active') ? 
                (document.querySelector('.payment-method-card.active').innerText.includes('KREDİ KARTI') ? 'credit_card' : 'cod') : 'cod';

            const ccData = {};
            if (paymentMethod === 'credit_card') {
                const cc_name = document.getElementById('cc_name').value.trim();
                const cc_number = document.getElementById('cc_number').value.replace(/\s/g, '');
                const cc_expiry = document.getElementById('cc_expiry').value.replace(/\s/g, '');
                const cc_cvc = document.getElementById('cc_cvc').value.trim();

                if (!cc_name || cc_number.length < 16 || cc_expiry.length < 5 || cc_cvc.length < 3) {
                    alert('Lütfen tüm kart bilgilerini eksiksiz ve doğru girin!');
                    return;
                }

                // Expiry Validation
                const parts = cc_expiry.split('/');
                if (parts.length !== 2) {
                    alert('Lütfen geçerli bir son kullanma tarihi girin! (AA/YY)');
                    return;
                }

                const expMonth = parseInt(parts[0], 10);
                const expYear = parseInt('20' + parts[1], 10);

                if (isNaN(expMonth) || expMonth < 1 || expMonth > 12) {
                    alert('Geçersiz ay! Lütfen 01-12 arasında bir ay girin.');
                    return;
                }

                const now = new Date();
                const currentMonth = now.getMonth() + 1;
                const currentYear = now.getFullYear();

                // Rule: Cannot be current or past month
                if (expYear < currentYear || (expYear === currentYear && expMonth <= currentMonth)) {
                    alert('Kartınızın süresi dolmuş veya içinde bulunulan ay seçilemez. Lütfen ileri tarihli bir kart girin.');
                    return;
                }

                // Rule: Not more than 15 years in the future
                if (expYear > currentYear + 15) {
                    alert('Son kullanma tarihi mevcut yıldan en fazla 15 yıl sonra olabilir.');
                    return;
                }

                ccData.cc_name = cc_name;
                ccData.cc_number = cc_number;
                ccData.cc_expiry = cc_expiry;
                ccData.cc_cvc = cc_cvc;
            }

            try {
                const response = await fetch('{{ route("funnel.order.submit") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ 
                        offer_id: selectedOfferId, 
                        name: name, 
                        phone: phone, 
                        city: city, 
                        district: district, 
                        address: address,
                        email: email,
                        id_number: idNumber,
                        payment_method: paymentMethod,
                        ...ccData
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Sunucu hatası ' + response.status);
                }

                const result = await response.json();
                if(result.success) {
                    if (result.is_redirect) {
                        // Banka yönlendirme formunu daha güvenli çalıştır
                        const formDiv = document.createElement('div');
                        formDiv.innerHTML = result.redirect_html;
                        document.body.appendChild(formDiv);
                        const form = formDiv.querySelector('form');
                        if (form) form.submit();
                        return;
                    }
                    window.location.href = result.redirect;
                } else {
                    alert('Hata: ' + (result.message || 'Sipariş oluşturulamadı.'));
                    btn.disabled = false;
                    btn.innerText = 'Siparişi Tamamla';
                }
            } catch (err) {
                console.error(err);
                alert('Sipariş İşleme Hatası: ' + err.message);
                btn.disabled = false;
                btn.innerText = 'Siparişi Tamamla';
            }
        }

        window.onload = function() {
            initCountdown();
            initCities();
            const firstPackage = document.querySelector('.package-card.active') || document.querySelector('.package-card');
            if (firstPackage) {
                selectPackage(
                    firstPackage,
                    firstPackage.dataset.offerId,
                    firstPackage.dataset.offerPrice,
                    false
                );
            }

            const orderFormSection = document.getElementById('order-form');
            const floating = document.getElementById('floating-cta');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if(entry.isIntersecting) {
                        floating.classList.add('cta-hidden');
                    } else {
                        floating.classList.remove('cta-hidden');
                    }
                });
            }, { 
                threshold: 0,
                rootMargin: '0px 0px -100px 0px' // Formun altına 100px kala gizle
            });
            
            if(orderFormSection) observer.observe(orderFormSection);
        }

        let currentPaymentMethod = 'cod';
        function selectPaymentMethod(method) {
            currentPaymentMethod = method;
            document.querySelectorAll('.payment-method-card').forEach(card => {
                const isSelected = (method === 'cod' && card.innerText.includes('KAPIDA')) || 
                                 (method === 'credit_card' && card.innerText.includes('KREDİ KARTI'));
                card.classList.toggle('active', isSelected);
            });

            const cardForm = document.getElementById('credit-card-form');
            if (cardForm) {
                if (method === 'credit_card') {
                    cardForm.classList.remove('hidden');
                } else {
                    cardForm.classList.add('hidden');
                }
            }
        }

        // Card Formatting
        const ccNumber = document.getElementById('cc_number');
        if (ccNumber) {
            ccNumber.addEventListener('input', function (e) {
                let v = e.target.value.replace(/\D/g, '');
                let formatted = v.match(/.{1,4}/g)?.join(' ') || v;
                e.target.value = formatted.substring(0, 19);
            });
        }

        const ccExpiry = document.getElementById('cc_expiry');
        if (ccExpiry) {
            ccExpiry.addEventListener('input', function (e) {
                let v = e.target.value.replace(/\D/g, '');
                
                // Month validation (First 2 digits)
                if (v.length >= 1) {
                    if (v.length === 1 && parseInt(v) > 1) {
                        v = '0' + v;
                    }
                    if (v.length >= 2) {
                        let month = parseInt(v.substring(0, 2));
                        if (month > 12) v = '12' + v.substring(2);
                        if (month === 0) v = '01' + v.substring(2);
                    }
                }

                // Year validation (Last 2 digits)
                if (v.length === 4) {
                    let yearSuffix = parseInt(v.substring(2, 4));
                    let currentYearSuffix = new Date().getFullYear() % 100;
                    if (yearSuffix > currentYearSuffix + 15) {
                        v = v.substring(0, 2) + (currentYearSuffix + 15).toString();
                    }
                }

                if (v.length > 2) v = v.substring(0, 2) + ' / ' + v.substring(2, 4);
                e.target.value = v.substring(0, 7);
                updateCardVisual();
            });
        }

        const ccCvc = document.getElementById('cc_cvc');
        if (ccCvc) {
            ccCvc.addEventListener('input', function (e) {
                e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
            });
        }

        function updateCardVisual() {
            const name = document.getElementById('cc_name').value || 'AD SOYAD';
            const number = document.getElementById('cc_number').value || '**** **** **** ****';
            const expiry = document.getElementById('cc_expiry').value || 'AA / YY';

            document.getElementById('v-cc-name').innerText = name.toUpperCase();
            document.getElementById('v-cc-number').innerText = number;
            document.getElementById('v-cc-expiry').innerText = expiry;
        }
    </script>
    {!! $config->footer_scripts !!}
</body>
</html>
