@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="flex flex-col gap-8 pb-10">
    
    <!-- DASHBOARD HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Genel Bakış</h1>
                <button 
                    x-data="favoriteManager" 
                    @click="toggle('admin.dashboard', 'Genel Bakış', '{{ route('admin.dashboard') }}', 'house')"
                    class="favorite-btn"
                    style="display: inline-flex !important; min-width: 32px; min-height: 32px;"
                    title="Hızlı İşlemlere Ekle/Kaldır"
                >
                    <svg data-favorite-id="admin.dashboard" class="w-6 h-6 {{ (auth()->user()->favorites && collect(auth()->user()->favorites)->contains('id', 'admin.dashboard')) ? 'text-amber-400' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                </button>
            </div>
            <p class="text-slate-500 font-medium mt-1">Sistem genelindeki satış operasyonlarını ve domain verimliliğini izleyin.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Range Selector -->
            @php 
                $currentRange = request('range', 'today'); 
                $startDate = request('start_date', ''); 
                $endDate = request('end_date', ''); 
            @endphp
            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-sm" x-data="{ showDatePicker: {{ $currentRange === 'custom' ? 'true' : 'false' }} }">
                <a href="{{ route('admin.dashboard', ['range' => 'today']) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'today' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Günlük</a>
                <a href="{{ route('admin.dashboard', ['range' => 'this_week']) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'this_week' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Haftalık</a>
                <a href="{{ route('admin.dashboard', ['range' => 'this_month']) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'this_month' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Aylık</a>
                <a href="{{ route('admin.dashboard', ['range' => 'this_year']) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'this_year' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Yıllık</a>
                <a href="{{ route('admin.dashboard', ['range' => 'all']) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'all' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Tümü</a>
                <button type="button" @click="showDatePicker = !showDatePicker" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'custom' ? 'bg-amber-500 text-white' : 'text-amber-600 hover:bg-amber-50' }}">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Özel{{ $currentRange === 'custom' && ($startDate || $endDate) ? ': ' . ($startDate ? \Carbon\Carbon::parse($startDate)->format('d.m.Y') : '') . ($startDate && $endDate ? ' - ' : '') . ($endDate ? \Carbon\Carbon::parse($endDate)->format('d.m.Y') : '') : '' }}
                    </span>
                </button>

                <!-- Inline custom date form (shown when showDatePicker is true) -->
                <form action="{{ route('admin.dashboard') }}" method="GET" x-show="showDatePicker" x-transition class="flex items-center gap-1 ml-2">
                    <input type="hidden" name="range" value="custom">
                    <input 
                        type="date" 
                        name="start_date" 
                        value="{{ $startDate ?: now()->format('Y-m-d') }}" 
                        class="bg-slate-50 border border-amber-300 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-400 transition outline-none"
                    >
                    <span class="text-slate-500 font-bold">-</span>
                    <input 
                        type="date" 
                        name="end_date" 
                        value="{{ $endDate ?: now()->format('Y-m-d') }}" 
                        class="bg-slate-50 border border-amber-300 rounded-lg px-3 py-1.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-400 transition outline-none"
                    >
                    <button type="submit" class="p-1.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition" title="Yenile">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </form>
            </div>
            <button onclick="location.reload()" class="p-2.5 bg-white border border-slate-200 rounded-xl text-slate-500 hover:text-brand-600 shadow-sm hover:shadow transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </button>
        </div>
    </div>

    <!-- 1. ÜST KPI ALANI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
        <!-- Revenue Card -->
        <div class="premium-card p-6 border-l-4 border-l-emerald-500 group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $rangeLabel }} Ciro</span>
            </div>
            <div class="text-3xl font-black text-slate-900">₺{{ number_format($todayRevenue, 2) }}</div>
            <div class="text-[11px] font-black text-emerald-600 mb-2">Onaylı: ₺{{ number_format($approvedRevenue, 2) }}</div>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-xs font-bold text-emerald-600">{{ $todayOrdersCount }} Sipariş</span>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <span class="text-xs font-bold text-slate-400">{{ $approvedOrdersCount }} Onaylı</span>
            </div>
        </div>

        <!-- Net Profit Card -->
        <div class="premium-card p-6 border-l-4 border-l-indigo-600 group bg-gradient-to-br from-white to-indigo-50/30">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-indigo-100 rounded-2xl text-indigo-600 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-colors cursor-help has-tooltip" data-tooltip="Net Kâr Hesabı: Ciro - (Ürün Maliyeti + Kargo Masrafı + Reklam Giderleri)">{{ $rangeLabel }} Net Kâr ⓘ</span>
            </div>
            <div class="text-3xl font-black text-indigo-700">₺{{ number_format($netProfit, 2) }}</div>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-[10px] font-bold text-slate-500 uppercase">Gider: ₺{{ number_format($marketingExpenses, 2) }}</span>
                <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                <span class="text-[10px] font-bold text-slate-500 uppercase">Maliyet: ₺{{ number_format($todayRevenue - $netProfit - $marketingExpenses, 2) }}</span>
            </div>
        </div>

        <!-- COD Collection -->
        <div class="premium-card p-6 border-l-4 border-l-amber-500 group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-amber-50 rounded-2xl text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $rangeLabel }} KÖ Tahsilat</span>
            </div>
            <div class="text-3xl font-black text-slate-900">₺{{ number_format($codPaidTotal, 2) }}</div>
            <div class="mt-2 flex items-center justify-between text-[11px] font-bold text-slate-500">
                <span>Bekleyen: ₺{{ number_format($codPendingTotal, 2) }}</span>
            </div>
        </div>

        <!-- Risk / Fraud -->
        <div class="premium-card p-6 border-l-4 border-l-rose-500 group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-rose-50 rounded-2xl text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Risk Durumu</span>
            </div>
            <div class="text-3xl font-black text-rose-600">{{ $fraudOrdersCount }}</div>
            <div class="text-xs font-bold text-slate-400 mt-2">Şüpheli Sipariş</div>
        </div>

        <!-- Upsell Performance -->
        <div class="premium-card p-6 border-l-4 border-l-orange-500 group bg-gradient-to-br from-white to-orange-50/20">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-orange-100 rounded-2xl text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Upsell Performansı</span>
            </div>
            <div class="text-3xl font-black text-orange-700">₺{{ number_format($upsellRevenue, 2) }}</div>
            <div class="flex items-center gap-2 mt-2">
                <span class="text-xs font-bold text-orange-600">{{ $upsellOrderCount }} Dönüşüm</span>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <span class="text-xs font-bold text-slate-400">%{{ number_format($upsellConversionRate, 1) }} CR</span>
            </div>
        </div>
    </div>

    <!-- 2 & 3. MAIN CONTENT AREA -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- LARGE LEFT COLUMN -->
        <div class="lg:col-span-3 space-y-8">
            
            <!-- 2. SİPARİŞ AKIŞ BLOĞU -->
            <div class="premium-card p-6 overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">SİPARİŞ ({{ $rangeLabel }})</h2>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4 relative">
                    <!-- Ziyaretçi -->
                    <div class="flex flex-col items-center text-center p-4 bg-slate-50 rounded-2xl border border-slate-100 has-tooltip" data-tooltip="Tekil Ziyaretçi / Toplam Hit">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Ziyaretçi ⓘ</span>
                        <div class="text-xl font-black text-slate-900">{{ number_format($funnel['visitor']) }}</div>
                        <div class="text-[10px] font-bold text-slate-400 mt-1">{{ number_format($funnel['total_hits']) }} Hit</div>
                    </div>

                    <!-- Sipariş -->
                    @php $cr = $funnel['visitor'] > 0 ? ($funnel['orders'] / $funnel['visitor']) * 100 : 0; @endphp
                    <div class="flex flex-col items-center text-center p-4 bg-brand-50 rounded-2xl border border-brand-100 has-tooltip" data-tooltip="Dönüşüm Oranı (CR): Sipariş / Tekil Ziyaretçi">
                        <span class="text-[9px] font-black text-brand-500 uppercase tracking-widest mb-1">Sipariş ⓘ</span>
                        <div class="text-xl font-black text-brand-900">{{ number_format($funnel['orders']) }}</div>
                        <div class="text-[10px] font-bold text-brand-600 mt-1">%{{ number_format($cr, 2) }} CR</div>
                    </div>

                    <!-- Onaylandı -->
                    @php $approveRate = $funnel['orders'] > 0 ? ($funnel['approved'] / $funnel['orders']) * 100 : 0; @endphp
                    <div class="flex flex-col items-center text-center p-4 {{ $approveRate < 40 ? 'bg-rose-50 border-rose-100' : 'bg-emerald-50 border-emerald-100' }} rounded-2xl border has-tooltip" data-tooltip="Onay Oranı: Onaylanan Sipariş / Toplam Sipariş">
                        <span class="text-[9px] font-black {{ $approveRate < 40 ? 'text-rose-500' : 'text-emerald-500' }} uppercase tracking-widest mb-1">Onaylandı ⓘ</span>
                        <div class="text-xl font-black {{ $approveRate < 40 ? 'text-rose-900' : 'text-emerald-900' }}">{{ number_format($funnel['approved']) }}</div>
                        <div class="text-[10px] font-bold {{ $approveRate < 40 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">%{{ number_format($approveRate, 1) }}</div>
                    </div>

                    <!-- Kargolandı -->
                    @php $shipRate = $funnel['approved'] > 0 ? ($funnel['shipped'] / $funnel['approved']) * 100 : 0; @endphp
                    <div class="flex flex-col items-center text-center p-4 bg-indigo-50 rounded-2xl border border-indigo-100 has-tooltip" data-tooltip="Kargolanma Oranı: Kargolanan / Onaylanan Sipariş">
                        <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest mb-1">Kargolandı ⓘ</span>
                        <div class="text-xl font-black text-indigo-900">{{ number_format($funnel['shipped']) }}</div>
                        <div class="text-[10px] font-bold text-indigo-600 mt-1">%{{ number_format($shipRate, 1) }}</div>
                    </div>

                    <!-- Teslim -->
                    @php $deliveryRate = $funnel['approved'] > 0 ? ($funnel['delivered'] / $funnel['approved']) * 100 : 0; @endphp
                    <div class="flex flex-col items-center text-center p-4 {{ $deliveryRate < 70 ? 'bg-amber-50 border-amber-100' : 'bg-emerald-50 border-emerald-100' }} rounded-2xl border has-tooltip" data-tooltip="Teslimat (Kargo) Başarısı: Teslim Edilen / Onaylanan Sipariş (Tr)">
                        <span class="text-[9px] font-black {{ $deliveryRate < 70 ? 'text-amber-600' : 'text-emerald-600' }} uppercase tracking-widest mb-1">Teslim ⓘ</span>
                        <div class="text-xl font-black {{ $deliveryRate < 70 ? 'text-amber-900' : 'text-emerald-900' }}">{{ number_format($funnel['delivered']) }}</div>
                        <div class="text-[10px] font-bold {{ $deliveryRate < 70 ? 'text-amber-600' : 'text-emerald-600' }} mt-1">%{{ number_format($deliveryRate, 1) }}</div>
                    </div>

                    <!-- Tahsilat -->
                    @php $collectionRate = $funnel['delivered'] > 0 ? ($funnel['paid'] / $funnel['delivered']) * 100 : 0; @endphp
                    <div class="flex flex-col items-center text-center p-4 {{ $collectionRate < 90 ? 'bg-rose-50 border-rose-100' : 'bg-brand-600 border-brand-700' }} rounded-2xl border {{ $collectionRate >= 90 ? 'text-white' : '' }} has-tooltip" data-tooltip="Tahsilat Başarısı: Tahsil Edilen para / Teslim edilen sipariş sayısı (Th)">
                        <span class="text-[9px] font-black {{ $collectionRate < 90 ? 'text-rose-500' : 'text-brand-100' }} uppercase tracking-widest mb-1">KÖ Tahsilat ⓘ</span>
                        <div class="text-xl font-black">{{ number_format($funnel['paid']) }}</div>
                        <div class="text-[10px] font-bold {{ $collectionRate < 90 ? 'text-rose-600' : 'text-brand-200' }} mt-1">%{{ number_format($collectionRate, 1) }}</div>
                    </div>
                </div>
            </div>

            <!-- GELİŞMİŞ FUNNEL BLOĞU -->
            <div class="premium-card p-6 overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">Gelişmiş Funnel Detayı ({{ $rangeLabel }})</h2>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Etkileşim Bazlı Veriler</span>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 relative">
                    @php
                        $steps = [
                            ['label' => 'Ziyaret', 'val' => $advancedFunnel['page_view'] ?? 0, 'color' => 'bg-slate-50 text-slate-400', 'desc' => 'Siteye giren tekil oturum sayısı.'],
                            ['label' => 'Scroll %50', 'val' => $advancedFunnel['scroll_50'] ?? 0, 'color' => 'bg-blue-50 text-blue-500', 'desc' => 'Sayfanın yarısına kadar inen kullanıcılar.'],
                            ['label' => 'CTA Tıklama', 'val' => $advancedFunnel['cta_click'] ?? 0, 'color' => 'bg-amber-50 text-amber-600', 'desc' => 'Sipariş butonlarına tıklayan kullanıcılar.'],
                            ['label' => 'Form Açma', 'val' => $advancedFunnel['form_open'] ?? 0, 'color' => 'bg-indigo-50 text-indigo-600', 'desc' => 'Sipariş formunu doldurmaya başlayanlar.'],
                            ['label' => 'Sipariş', 'val' => $advancedFunnel['order'] ?? 0, 'color' => 'bg-emerald-50 text-emerald-600', 'desc' => 'Siparişi başarıyla tamamlayanlar.'],
                        ];
                    @endphp

                    @foreach($steps as $index => $step)
                        <div class="flex flex-col items-center text-center p-4 {{ $step['color'] }} rounded-2xl border border-slate-100 has-tooltip" data-tooltip="{{ $step['desc'] }}">
                            <span class="text-[9px] font-black uppercase tracking-widest mb-1">{{ $step['label'] }} ⓘ</span>
                            <div class="text-xl font-black text-slate-900">{{ number_format($step['val']) }}</div>
                            @if($index > 0)
                                @php 
                                    $totalBase = $steps[0]['val'] > 0 ? $steps[0]['val'] : 1;
                                    $percent = ($step['val'] / $totalBase) * 100; 
                                @endphp
                                <div class="text-[10px] font-bold text-slate-400 mt-1">%{{ number_format($percent, 1) }}</div>
                            @else
                                <div class="text-[10px] font-bold text-slate-400 mt-1">{{ number_format($advancedFunnel['total_hits'] ?? 0) }} Hit</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. DOMAIN PERFORMANS TABLOSU -->
            <div class="premium-card">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">Alan Adı Performansı ({{ $rangeLabel }})</h2>
                </div>
                <div class="overflow-x-auto lg:overflow-visible">
                    <table class="w-full text-left text-sm whitespace-nowrap lg:table-fixed">
                        <thead class="bg-white text-[10px] uppercase font-black text-slate-400 tracking-widest leading-tight">
                            <tr>
                                <th class="px-4 py-4 border-b border-slate-100" style="width: 140px;">Domain /<br>Ürün</th>
                                <th class="px-2 py-4 border-b border-slate-100 text-center" style="width: 85px;">
                                    <div class="cursor-help has-tooltip inline-block" data-tooltip="Tekil Ziyaretçi / Toplam Hit">Ziyaret /<br>Hit ⓘ</div>
                                </th>
                                <th class="px-2 py-4 border-b border-slate-100 text-center" style="width: 85px;">
                                    <div class="cursor-help has-tooltip inline-block" data-tooltip="Toplam Sipariş / Onaylanan Sipariş Sayısı">Sipariş /<br>Onay ⓘ</div>
                                </th>
                                <th class="px-2 py-4 border-b border-slate-100 text-center" style="width: 90px;">
                                    <div class="cursor-help has-tooltip inline-block" data-tooltip="Sipariş Formunu doldurmaya başlayan Tekil Ziyaretçi / Toplam Etkileşim">Form<br>Başlama ⓘ</div>
                                </th>
                                <th class="px-2 py-4 border-b border-slate-100 text-center" style="width: 80px;">
                                    <div class="cursor-help has-tooltip inline-block" data-tooltip="Sayfanın en az %50'sine kadar inen tekil ziyaretçi sayısı">Scroll<br>%50 ⓘ</div>
                                </th>
                                <th class="px-2 py-4 border-b border-slate-100 text-center" style="width: 65px;">
                                    <div class="cursor-help has-tooltip inline-block" data-tooltip="Conversion Rate: Sipariş / Tekil Ziyaretçi">CR % ⓘ</div>
                                </th>
                                <th class="px-2 py-4 border-b border-slate-100 text-center" style="width: 110px;">
                                    <div class="cursor-help has-tooltip inline-block" data-tooltip="Tr: Teslimat Oranı (%), Th: Tahsilat Oranı (%)">Kargo /<br>Tahsilat ⓘ</div>
                                </th>
                                <th class="px-2 py-4 border-b border-slate-100 text-right" style="width: 120px;">
                                    <div class="cursor-help has-tooltip inline-block" data-tooltip="Toplam Ciro / Onaylanan Sipariş Cirosu">Ciro /<br>Onaylı ⓘ</div>
                                </th>
                                <th class="px-2 py-4 border-b border-slate-100 text-center" style="width: 90px;">
                                    <div class="cursor-help has-tooltip inline-block" data-tooltip="Birim: Ürün başı düşen ciro, Sepet: Sipariş başı düşen ciro">Birim /<br>Sepet ⓘ</div>
                                </th>
                                <th class="px-4 py-4 border-b border-slate-100 text-center" style="width: 80px;">Durum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($domainStats as $ds)
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="relative group cursor-help flex-grow min-w-0">
                                            <div class="font-bold text-slate-900 truncate max-w-[140px] group-hover:hidden transition-all duration-200">
                                                {{ $ds['name'] }}
                                            </div>
                                            <div class="hidden group-hover:block font-extrabold text-brand-600 truncate max-w-[140px] transition-all duration-200" title="Ürün: {{ $ds['product'] }}">
                                                {{ $ds['product'] }}
                                            </div>
                                        </div>
                                        <a href="https://{{ $ds['name'] }}" target="_blank" class="text-slate-400 hover:text-brand-600 transition shrink-0" title="Siteyi Yeni Sekmede Aç">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm font-black text-slate-900">{{ number_format($ds['visitors']) }}</div>
                                    <div class="text-[10px] font-bold text-slate-400">{{ number_format($ds['hits']) }} Hit</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm font-black text-slate-900">{{ number_format($ds['orders']) }}</div>
                                    <div class="text-[10px] font-bold text-emerald-600">{{ number_format($ds['approved_count']) }} Onay</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm font-black text-indigo-600">{{ number_format($ds['form_open_unique']) }}</div>
                                    <div class="text-[10px] font-bold text-slate-400">{{ number_format($ds['form_open_total']) }} Başlama</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm font-black text-blue-600">{{ number_format($ds['scroll_50']) }}</div>
                                    @php $sRate = $ds['visitors'] > 0 ? ($ds['scroll_50'] / $ds['visitors']) * 100 : 0; @endphp
                                    <div class="text-[10px] font-bold text-slate-400">%{{ number_format($sRate, 1) }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex flex-col items-center px-2 py-1 rounded-lg {{ $ds['cr'] > 3 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-500' }}">
                                        <span class="font-black">%{{ number_format($ds['cr'], 1) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col gap-1 items-center">
                                        <div class="text-[11px] font-bold text-slate-600">Tr: %{{ number_format($ds['delivery_rate'], 0) }}</div>
                                        <div class="text-[11px] font-bold text-brand-600">Th: %{{ number_format($ds['collection_rate'], 0) }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="font-black text-slate-900">₺{{ number_format($ds['revenue'], 0) }}</div>
                                    <div class="text-[11px] font-black text-emerald-600">
                                        Onay: ₺{{ number_format($ds['approved_revenue'], 0) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col gap-0.5 items-center">
                                        <div class="text-xs font-black text-slate-700">₺{{ number_format($ds['avg_price'], 1) }}</div>
                                        <div class="text-xs font-black text-indigo-700">₺{{ number_format($ds['aov'], 1) }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if(count($ds['alarms']) > 0)
                                        <div class="inline-flex p-1.5 bg-rose-50 text-rose-600 rounded-lg animate-pulse has-tooltip" data-tooltip="{{ implode(', ', $ds['alarms']) }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            AKTİF
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- RIGHT SIDEBAR COLUMN -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- ALARM PANELİ -->
            <div class="premium-card overflow-hidden bg-white border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 bg-rose-50/50 flex items-center justify-between">
                    <h3 class="text-sm font-black text-rose-800 uppercase tracking-widest flex items-center gap-2">
                        KRİTİK UYARILAR
                    </h3>
                    <span id="alert-count" class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-white text-[10px] font-black">{{ count($criticalAlerts) }}</span>
                </div>
                <div class="p-2 pr-2 space-y-1 overflow-y-auto custom-scrollbar" style="max-height: 560px;">
                    @forelse($criticalAlerts as $alert)
                    <div data-alert-key="{{ $alert['key'] }}" class="flex items-start justify-between gap-2 p-3 rounded-xl hover:bg-slate-50 transition-colors group alert-item">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 w-1.5 h-1.5 rounded-full {{ ($alert['type'] ?? '') === 'danger' ? 'bg-rose-500' : 'bg-amber-500' }} shrink-0"></div>
                            <p class="text-[12px] font-bold text-slate-700 leading-tight">{!! $alert['message'] !!}</p>
                        </div>
                        <button onclick="hideAlert('{{ $alert['key'] }}', {{ $alert['is_system'] ?? 'false' ? ($alert['id'] ?? 'null') : 'null' }}, this)" class="text-slate-300 hover:text-rose-500 transition p-0.5" title="Gizle">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 italic text-sm">
                        Kritik uyarı bulunmuyor.
                    </div>
                    @endforelse
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const hiddenAlerts = JSON.parse(localStorage.getItem('hidden_alerts') || '[]');
                        document.querySelectorAll('.alert-item').forEach(item => {
                            if (hiddenAlerts.includes(item.dataset.alertKey)) {
                                item.remove();
                            }
                        });
                        updateAlertCount();
                    });

                    function hideAlert(key, systemId, btn) {
                        const alertItem = btn.closest('.alert-item');
                        
                        // If it's a system alert, mark as read in DB
                        if (systemId) {
                            fetch(`/admin/alerts/${systemId}/mark-as-read`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            });
                        }

                        // Always hide on client side (save to localStorage)
                        const hiddenAlerts = JSON.parse(localStorage.getItem('hidden_alerts') || '[]');
                        if (!hiddenAlerts.includes(key)) {
                            hiddenAlerts.push(key);
                            localStorage.setItem('hidden_alerts', JSON.stringify(hiddenAlerts));
                        }

                        alertItem.style.opacity = '0';
                        alertItem.style.transform = 'translateX(20px)';
                        setTimeout(() => {
                            alertItem.remove();
                            updateAlertCount();
                        }, 300);
                    }

                    function updateAlertCount() {
                        const count = document.querySelectorAll('.alert-item').length;
                        const countEl = document.getElementById('alert-count');
                        if (countEl) {
                            countEl.textContent = count;
                            if (count === 0) {
                                countEl.closest('.premium-card').querySelector('.p-2').innerHTML = '<div class="p-8 text-center text-slate-400 italic text-sm">Kritik uyarı bulunmuyor.</div>';
                            }
                        }
                    }
                </script>
            </div>

            <!-- AKILLI YORUM & ÖNERİLER -->
            <div class="premium-card p-6 border border-brand-100 bg-brand-50/20">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-2 h-2 rounded-full bg-brand-500 animate-ping"></div>
                    <h3 class="text-[10px] font-black text-brand-700 uppercase tracking-widest">AI ÖNERİLERİ & ANALİZ <span class="text-slate-400 opacity-60 ml-1">(Sistem Geneli)</span></h3>
                </div>
                <div class="space-y-4">
                    @forelse($aiSuggestions as $suggestion)
                        <div class="p-3 rounded-xl {{ $suggestion['type'] === 'danger' ? 'bg-rose-100/50' : 'bg-amber-100/50' }} border {{ $suggestion['type'] === 'danger' ? 'border-rose-200' : 'border-amber-200' }}">
                            <div class="flex items-center gap-2 mb-1">
                                @if($suggestion['type'] === 'danger')
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                @else
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @endif
                                <h4 class="text-[11px] font-black {{ $suggestion['type'] === 'danger' ? 'text-rose-800' : 'text-amber-800' }} uppercase">{{ $suggestion['title'] }}</h4>
                            </div>
                            <p class="text-[11px] font-bold text-slate-700 leading-relaxed italic">{{ $suggestion['message'] }}</p>
                        </div>
                    @empty
                        <div class="text-xs font-bold text-slate-500 leading-relaxed italic text-center py-4">
                            "Şu an için sistem verileri normal görünüyor. Dönüşüm oranlarınız hedeflenen aralıkta."
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- RISK & SAĞLIK -->
            <div class="premium-card p-6">
                <h3 class="text-sm font-black text-rose-900 uppercase tracking-wider mb-6">Sistem Sağlık Paneli</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between cursor-help has-tooltip" data-tooltip="Sistem Sağlık Skoru: Tüm sitelerin ortalama risk puanına göre hesaplanan genel sağlık durumudur. 100 Tam sağlıklıdır.">
                         <span class="text-xs font-bold text-slate-500 tracking-tight">Sistem Sağlığı ⓘ</span>
                         <span class="text-sm font-black {{ $systemHealth > 80 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($systemHealth, 0) }}%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full {{ $systemHealth > 80 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width: {{ $systemHealth }}%"></div>
                    </div>
                    
                    <div class="pt-4 space-y-3">
                         <div class="flex items-center justify-between">
                             <div class="flex items-center gap-3">
                                 <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                 <span class="text-[11px] font-bold text-slate-700">Veritabanı Durumu</span>
                             </div>
                             <span class="text-[10px] font-black text-emerald-600">AKTİF</span>
                         </div>
                         <div class="flex items-center justify-between">
                             <div class="flex items-center gap-3">
                                 <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                 <span class="text-[11px] font-bold text-slate-700">Cloudflare API</span>
                             </div>
                             <span class="text-[10px] font-black text-emerald-600">BAĞLI</span>
                         </div>
                         <div class="flex items-center justify-between">
                             <div class="flex items-center gap-3">
                                 <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                                 <span class="text-[11px] font-bold text-slate-700">Sipariş Akışı</span>
                             </div>
                             <span class="text-[10px] font-black text-amber-600">NORMAL</span>
                         </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- BOTTOM PANELS -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        
        <!-- BEKLEYEN SİPARİŞ TABLOSU -->
        <div class="premium-card overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">İşlem Bekleyen Son Siparişler</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                            <th class="px-6 py-4">Müşteri</th>
                            <th class="px-6 py-4">Paket</th>
                            <th class="px-6 py-4 text-center cursor-help has-tooltip" data-tooltip="Sistem tarafından müşteri verileriyle hesaplanan şüpheli işlem ihtimali puanı.">Risk ⓘ</th>
                            <th class="px-6 py-4 text-right">Tutar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                         @foreach($recentOrders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $order->customer->full_name }}</div>
                                <div class="text-[10px] font-medium text-slate-500 mt-0.5">{{ $order->domain->domain_name }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-600">
                                {{ $order->offer?->offer_name ?? ($order->is_api ? 'API (paket yok)' : '—') }}
                            </td>
                             <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black {{ $order->fraud_score > 60 ? 'bg-rose-100 text-rose-700' : ($order->fraud_score > 30 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    {{ $order->fraud_score }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-black text-slate-900">₺{{ number_format($order->grand_total, 2) }}</div>
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-[10px] font-black text-brand-600 uppercase hover:underline">Detay</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-50 bg-slate-50/30">
                {{ $recentOrders->appends(request()->all())->links() }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 h-full">
            <!-- KÖ & KARGO ÖZET -->
            <div class="premium-card p-6 h-full flex flex-col">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-6">Kargo & {{ $rangeLabel }} Tahsilat</h3>
                <div class="space-y-4 flex-grow">
                    <div class="flex justify-between items-center p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-xs font-bold text-slate-500">Teslim Edilen</span>
                        <span class="text-sm font-black text-slate-900">{{ $funnel['delivered'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3.5 bg-emerald-50 rounded-2xl border border-emerald-100">
                        <span class="text-xs font-bold text-emerald-600">Tahsil Edilen (KÖ)</span>
                        <span class="text-sm font-black text-emerald-700">₺{{ number_format($codPaidTotal, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3.5 bg-rose-50 rounded-2xl border border-rose-100">
                        <span class="text-xs font-bold text-rose-600">Bekleyen Tahsilat</span>
                        <span class="text-sm font-black text-rose-900">₺{{ number_format($codPendingTotal, 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- HIZLI İŞLEMLER -->
            <div class="premium-card p-6" x-data="{ favorites: {{ json_encode(auth()->user()->favorites ?? []) }} }" @favorite-updated.window="favorites = $event.detail.favorite_status === 'added' ? [...favorites, $event.detail.data] : favorites.filter(f => f.id !== $event.detail.id)">
                <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6">HIZLI İŞLEMLER</h3>
                <div class="grid grid-cols-1 gap-3">
                    <template x-for="fav in favorites" :key="fav.id">
                        <a :href="fav.url" class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-900 border border-slate-900 text-white hover:bg-black transition shadow-lg shadow-slate-900/10 group">
                            <div class="p-2 bg-white/10 rounded-xl group-hover:scale-110 transition-transform">
                                <svg x-show="fav.icon === 'shopping-bag'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <svg x-show="fav.icon === 'globe'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                <svg x-show="!['shopping-bag', 'globe'].includes(fav.icon)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                            </div>
                            <span class="text-xs font-black uppercase tracking-wide" x-text="fav.title"></span>
                        </a>
                    </template>
                    
                    <div x-show="favorites.length === 0" class="text-center py-6">
                        <p class="text-[10px] font-bold text-slate-400 uppercase italic">Favori işlem bulunmuyor.<br>Modül başlıklarındaki yıldızı kullanarak ekleyebilirsiniz.</p>
                    </div>

                    <a href="{{ route('admin.domains.create') }}" class="flex items-center gap-3 p-3.5 rounded-2xl bg-white border border-slate-200 text-slate-900 hover:bg-slate-50 transition group mt-2">
                        <div class="p-2 bg-slate-100 rounded-xl group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <span class="text-xs font-black uppercase tracking-wide">Yeni Domain Ekle</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

<style>
    .premium-card {
        @apply bg-white rounded-[28px] shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300;
    }

    .premium-card:not(.overflow-hidden) {
        overflow: visible;
    }

    /* CUSTOM TOOLTIP CSS */
    .has-tooltip {
        position: relative !important;
        display: inline-block !important;
    }

    .has-tooltip::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(0);
        padding: 10px 14px;
        background: #0f172a;
        color: #ffffff !important;
        font-size: 11px;
        font-weight: 700;
        border-radius: 10px;
        white-space: normal;
        min-width: 170px;
        max-width: 240px;
        z-index: 999999999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.6);
        text-align: center;
        line-height: 1.5;
        pointer-events: none;
    }

    .has-tooltip:hover::after {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(-10px);
    }

    .has-tooltip::before {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(5px);
        border: 6px solid transparent;
        border-top-color: #0f172a;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 999999999;
        pointer-events: none;
    }

    .has-tooltip:hover::before {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(-5px);
    }

    /* Table Fixes */
    .premium-card {
        @apply bg-white rounded-[28px] shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300;
    }
    
    table {
        border-collapse: collapse;
        width: 100%;
    }
    
    th, td {
        vertical-align: middle;
    }
</style>
@endsection