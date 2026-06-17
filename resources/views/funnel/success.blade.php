<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişiniz Alındı | {{ $order->domain->domain_name }}</title>

    @php $config = $order->domain->config; @endphp

    <!-- Base Marketing Scripts -->
    @if($config->google_analytics_id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $config->google_analytics_id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $config->google_analytics_id }}');
        // Purchase Event
        gtag('event', 'purchase', {
            "transaction_id": "{{ $order->id }}",
            "value": {{ $order->grand_total }},
            "currency": "TRY",
            "items": [{ "item_id": "{{ $order->offer_id }}", "item_name": "Paket" }]
        });
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
    fbq('track', 'Purchase', { 
        value: {{ $order->grand_total }}, 
        currency: 'TRY',
        content_ids: ['{{ $order->offer_id }}'],
        content_type: 'product'
    });
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $config->facebook_pixel_id }}&ev=PageView&noscript=1" /></noscript>
    @endif

    @if($config->tiktok_pixel_id)
    <script>
    !function (w, d, t) {
      w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","detach","update","setRealtimeFilteringConfigs","setAccount","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=d.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
      ttq.load('{{ $config->tiktok_pixel_id }}');
      ttq.page();
      ttq.track('CompletePayment', {
        content_id: '{{ $order->offer_id }}',
        quantity: 1,
        price: {{ $order->grand_total }},
        value: {{ $order->grand_total }},
        currency: 'TRY',
      });
    }(window, document, 'ttq');
    </script>
    @endif

    {!! $config->header_scripts !!}
    {!! $config->success_scripts !!}

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .gradient-bg { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
        .confetti-icon { animation: bounce 2s infinite; }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes bounce-slow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
        .animate-bounce-slow { animation: bounce-slow 3s infinite ease-in-out; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 bg-slate-50">
    {!! $config->body_scripts !!}

    <div class="max-w-xl w-full bg-white rounded-[40px] shadow-2xl overflow-hidden text-center p-10 md:p-16 relative">
        <!-- Confetti Animation Placeholder Effect -->
        <div class="absolute top-0 left-0 w-full h-2 gradient-bg"></div>
        
        <div class="mb-8 flex justify-center">
            <div class="w-24 h-24 gradient-bg rounded-full flex items-center justify-center shadow-xl shadow-green-100 confetti-icon">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
        </div>

        <h1 class="text-4xl font-black text-slate-900 tracking-tighter mb-4 uppercase">TEŞEKKÜRLER!</h1>
        <p class="text-xl text-slate-500 font-bold mb-10 leading-snug">Siparişiniz başarıyla alındı. Müşteri temsilcimiz en kısa sürede sizi arayacaktır.</p>

        <div class="bg-slate-50 rounded-3xl p-8 mb-10 text-left border border-slate-100">
            <div class="flex justify-between items-center mb-4 border-b border-slate-200 pb-4">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Sipariş No</span>
                <span class="font-black text-slate-900">{{ $order->order_number }}</span>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between font-bold text-sm">
                    <span class="text-slate-400">Ürün</span>
                    <span class="text-slate-700">{{ $order->offer ? $order->offer->offer_name : 'Ürün' }}</span>
                </div>
                <div class="flex justify-between font-bold text-sm">
                    <span class="text-slate-400">Ad Soyad</span>
                    <span class="text-slate-700">{{ $order->customer->full_name }}</span>
                </div>
                <div class="flex justify-between font-bold text-sm text-brand-600">
                    <span class="text-slate-400">Ödeme</span>
                    <span class="font-black italic">
                        @if($order->payment_method === 'credit_card')
                            KREDİ KARTI
                        @else
                            KAPIDA NAKİT
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center border-t-2 border-slate-200 pt-5 mt-5">
                    <span class="text-lg font-black text-slate-900">Toplam</span>
                    <span class="text-2xl font-black text-brand-600 tracking-tighter whitespace-nowrap">{{ number_format($order->grand_total, 2, ',', '.') }} ₺</span>
                </div>
            </div>
        </div>

        {{-- Upsell Section --}}
        @php
            $upsellOffers = $order->domain->upsellOffers()
                ->where('is_active', true)
                ->whereIn('display_timing', ['thank_you', 'both'])
                ->get();
        @endphp

        @if($upsellOffers->isNotEmpty() && !$order->has_upsell)
        <div id="upsell-container" class="mb-10 animate-bounce-slow">
            <div class="relative bg-orange-50 rounded-[40px] p-8 border-4 border-dashed border-orange-200 overflow-hidden text-center">
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-orange-100 rounded-full opacity-50 blur-2xl"></div>
                
                <div class="relative z-10">
                    <span class="inline-block bg-orange-600 text-white text-[10px] font-black px-4 py-1.5 rounded-full mb-4 shadow-sm animate-pulse">SADECE ŞİMDİ GEÇERLİ!</span>
                    <h2 class="text-2xl font-black text-orange-950 tracking-tighter leading-none mb-2 uppercase">Bu Fırsatı Kaçırma!</h2>
                    
                    @foreach($upsellOffers as $up)
                    <div class="upsell-item mt-6 p-6 bg-white rounded-3xl shadow-xl shadow-orange-900/5 border border-orange-100">
                        <h3 class="text-xl font-black text-slate-900 leading-tight mb-2 uppercase">{{ $up->title }}</h3>
                        <p class="text-sm text-slate-500 font-bold mb-6">{{ $up->description }}</p>
                        
                        <div class="flex items-center justify-center gap-4 mb-6">
                            @if($up->original_price)
                            <span class="text-lg font-bold text-slate-300 line-through decoration-red-400 decoration-2">{{ number_format($up->original_price, 2, ',', '.') }} ₺</span>
                            @endif
                            <span class="text-4xl font-black text-orange-600 tracking-tighter">{{ number_format($up->discount_price, 2, ',', '.') }} ₺</span>
                        </div>

                        <button type="button" onclick="acceptUpsell({{ $up->id }})" class="w-full bg-orange-600 text-white font-black py-5 rounded-2xl hover:bg-orange-700 transition-all active:scale-95 shadow-lg shadow-orange-600/20 text-lg flex items-center justify-center gap-3">
                            <span>SİPARİŞİME EKLE</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                        
                        <button type="button" onclick="rejectUpsell({{ $up->id }})" class="mt-4 text-[10px] font-black text-slate-400 hover:text-slate-600 transition tracking-widest uppercase underline underline-offset-4">HAYIR, İSTEMİYORUM</button>
                    </div>
                    @endforeach
                </div>
            </div>
            <p class="mt-4 text-[10px] font-black text-orange-400 uppercase tracking-widest text-center">SADECE 2 DAKİKA İÇİNDE GEÇERLİDİR</p>
        </div>

        <script>
            function acceptUpsell(offerId) {
                const btn = event.currentTarget;
                const originalContent = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<svg class="animate-spin h-6 w-6 text-white mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

                fetch('{{ route("funnel.upsell.accept", $order->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ upsell_offer_id: offerId })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Bir hata oluştu.');
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    }
                })
                .catch(() => {
                    alert('Bağlantı hatası.');
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                });
            }

            function rejectUpsell(offerId) {
                if(!confirm('Bu özel indirimi reddetmek istediğinize emin misiniz?')) return;
                
                fetch('{{ route("funnel.upsell.reject", $order->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ upsell_offer_id: offerId })
                })
                .then(() => {
                    document.getElementById('upsell-container').style.display = 'none';
                });
            }
        </script>
        @endif

        <a href="{{ route('funnel.landing') }}" class="inline-block w-full bg-slate-900 text-white font-black py-5 rounded-2xl hover:bg-black transition-all active:scale-95 shadow-lg">
            ALIŞVERİŞE DEVAM ET
        </a>
        
        <p class="mt-8 text-xs text-slate-400 font-medium italic">
            Bir sorunuz mu var? <a href="https://wa.me/{{ $order->domain->config->whatsapp_number ?? '' }}" class="text-green-600 font-bold underline">WhatsApp'tan yazın</a>
        </p>
    </div>

    {!! $config->footer_scripts !!}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 🎉 Celebration!
            confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#f27a1a', '#10b981', '#ffffff']
            });
        });

        (function() {
            const domainId = {{ $order->domain_id }};
            let sessionId = localStorage.getItem('funnel_session_id');
            if (domainId && sessionId) {
                fetch('{{ route("api.funnel.track") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ domain_id: domainId, session_id: sessionId, event_type: 'order_complete', event_value: '{{ $order->order_number }}' })
                });
            }
        })();
    </script>
</body>
</html>
