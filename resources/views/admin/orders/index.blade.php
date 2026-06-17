@extends('layouts.admin')

@inject('auth', 'Illuminate\Support\Facades\Auth')
@inject('request', 'Illuminate\Support\Facades\Request')
@inject('url', 'Illuminate\Support\Facades\URL')
@inject('collection', 'Illuminate\Support\Collection')

@section('title', 'Sipariş Yönetimi')

@section('content')
@php 
    $user = $auth::user();
    $request = $request::instance();
@endphp
<div class="flex flex-col gap-6 fade-in-up" x-data="{ 
    selectedOrders: [],
    toggleAll(e) {
        if (e.target.checked) {
            this.selectedOrders = Array.from(document.querySelectorAll('.order-checkbox')).map(el => el.value);
        } else {
            this.selectedOrders = [];
        }
    },
    bulkPrint() {
        if (this.selectedOrders.length === 0) {
            Swal.fire('Uyarı', 'Lütfen en az bir sipariş seçin.', 'warning');
            return;
        }
        window.open('{{ $url::route('admin.orders.bulk-print') }}?ids=' + this.selectedOrders.join(','), '_blank');
    }
}">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Sipariş Yönetimi</h2>
                <button 
                    x-data="favoriteManager" 
                    @click="toggle('admin.orders', 'Sipariş Yönetimi', '{{ $url::route('admin.orders') }}', 'shopping-bag')"
                    class="favorite-btn"
                    style="display: inline-flex !important; min-width: 32px; min-height: 32px;"
                    title="Hızlı İşlemlere Ekle/Kaldır"
                >
                    <svg data-favorite-id="admin.orders" class="w-6 h-6 {{ ($user->favorites && $collection::make($user->favorites)->contains('id', 'admin.orders')) ? 'text-amber-400' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-slate-500 font-medium">Tüm funnel sayfalardan gelen siparişler ({{ $orders->total() ?? 0 }} toplam)</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3 no-print">
            <!-- Bulk Print Button -->
            @if($user->hasPermission('orders.print'))
            <template x-if="selectedOrders.length > 0">
                <button @click="bulkPrint()" class="bg-brand-600 text-white text-[11px] font-black uppercase tracking-widest px-4 py-2.5 rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-600/20 flex items-center animate-in zoom-in duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Seçilileri Yazdır (<span x-text="selectedOrders.length"></span>)
                </button>
            </template>
            @endif
            <!-- Range Selector + Custom Date -->
            @php $currentRange = $request->query('range', 'all'); $startDate = $request->query('start_date', ''); $endDate = $request->query('end_date', ''); @endphp
            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-sm" x-data="{ showDatePicker: {{ $currentRange === 'custom' ? 'true' : 'false' }} }">
                <a href="{{ $url::current() }}?{{ http_build_query(array_merge($request->except(['range','date','start_date','end_date']), ['range' => 'today'])) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'today' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Günlük</a>
                <a href="{{ $url::current() }}?{{ http_build_query(array_merge($request->except(['range','date','start_date','end_date']), ['range' => 'this_week'])) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'this_week' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Haftalık</a>
                <a href="{{ $url::current() }}?{{ http_build_query(array_merge($request->except(['range','date','start_date','end_date']), ['range' => 'this_month'])) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'this_month' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Aylık</a>
                <a href="{{ $url::current() }}?{{ http_build_query(array_merge($request->except(['range','date','start_date','end_date']), ['range' => 'this_year'])) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'this_year' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Yıllık</a>
                <a href="{{ $url::current() }}?{{ http_build_query(array_merge($request->except(['range','date','start_date','end_date']), ['range' => 'all'])) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'all' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Tümü</a>
                <button type="button" @click="showDatePicker = !showDatePicker" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'custom' ? 'bg-amber-500 text-white' : 'text-amber-600 hover:bg-amber-50' }}">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Özel{{ $currentRange === 'custom' && ($startDate || $endDate) ? ': ' . ($startDate ? \Carbon\Carbon::parse($startDate)->format('d.m.Y') : '') . ($startDate && $endDate ? ' - ' : '') . ($endDate ? \Carbon\Carbon::parse($endDate)->format('d.m.Y') : '') : '' }}
                    </span>
                </button>

                <!-- Inline custom date form (shown when showDatePicker is true) -->
                <form action="{{ $url::current() }}" method="GET" x-show="showDatePicker" x-transition class="flex items-center gap-1 ml-2">
                    @foreach($request->except(['range', 'date', 'start_date', 'end_date']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
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

            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
                <a href="{{ $url::current() }}?{{ http_build_query(array_merge($request->all(), ['status' => 'pending'])) }}" class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg transition {{ $request->query('status') === 'pending' ? 'bg-amber-500 text-white shadow-lg shadow-amber-200' : 'text-amber-600 hover:bg-amber-50' }}">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                        Pending
                    </span>
                </a>
            </div>
            
            <button onclick="location.reload()" class="p-2.5 bg-white border border-slate-200 rounded-xl text-slate-500 hover:text-brand-600 shadow-sm hover:shadow transition" title="Sayfayı Yenile">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </button>

            @if($user->hasPermission('orders.export'))
            <a href="{{ $url::route('admin.orders.export', $request->all()) }}" class="bg-white border text-[11px] font-black uppercase tracking-widest border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 transition shadow-sm flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Dışa Aktar
            </a>
            @endif
        </div>
    </div>

    <!-- Filters Area -->
    <div class="premium-card p-4">
        <form action="{{ $url::route('admin.orders') }}" method="GET" class="flex gap-4 items-center">
            <input type="hidden" name="filter" value="1">
            <input type="text" name="search" value="{{ $request->query('search') }}" placeholder="Müşteri Adı veya Telefon..." class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">Tüm Durumlar</option>
                <option value="pending" {{ $request->query('status') == 'pending' ? 'selected' : '' }}>Pending (Beklemede)</option>
                <option value="yeni" {{ $request->query('status') == 'yeni' ? 'selected' : '' }}>Yeni</option>
                <option value="aranacak" {{ $request->query('status') == 'aranacak' ? 'selected' : '' }}>Aranacak</option>
                <option value="onaylandı" {{ $request->query('status') == 'onaylandı' ? 'selected' : '' }}>Onaylandı</option>
                <option value="kargoya_verildi" {{ $request->query('status') == 'kargoya_verildi' ? 'selected' : '' }}>Kargoya Verildi</option>
                <option value="teslim_edildi" {{ $request->query('status') == 'teslim_edildi' ? 'selected' : '' }}>Teslim Edildi</option>
                <option value="iptal" {{ $request->query('status') == 'iptal' ? 'selected' : '' }}>İptal Edildi</option>
                <option value="iade" {{ $request->query('status') == 'iade' ? 'selected' : '' }}>İade</option>
            </select>
            <select name="brand_id" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">Tüm Markalar</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ $request->query('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                @endforeach
            </select>
            <select name="domain_id" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">Tüm Domainler</option>
                @foreach($domains as $domain)
                    <option value="{{ $domain->id }}" {{ $request->query('domain_id') == $domain->id ? 'selected' : '' }}>{{ $domain->domain_name }}</option>
                @endforeach
            </select>
            <select name="is_printed" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">Yazdırılma Durumu</option>
                <option value="1" {{ $request->query('is_printed') == '1' ? 'selected' : '' }}>Yazdırıldı</option>
                <option value="0" {{ $request->query('is_printed') === '0' ? 'selected' : '' }}>Yazdırılmadı</option>
            </select>
            <select name="cargo_firm" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">Kargo Firması</option>
                @foreach($cargoSettings as $cs)
                    <option value="{{ $cs->carrier_name }}" {{ $request->query('cargo_firm') == $cs->carrier_name ? 'selected' : '' }}>{{ $cs->display_name }}</option>
                @endforeach
            </select>
            <select name="api_filter" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">API filtresi</option>
                <option value="api_pending" {{ $request->query('api_filter') === 'api_pending' ? 'selected' : '' }}>API — onay bekliyor</option>
                <option value="api_all" {{ $request->query('api_filter') === 'api_all' ? 'selected' : '' }}>Tüm API siparişleri</option>
            </select>
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="hide_cancelled" value="1" {{ (!$request->has('filter') || $request->has('hide_cancelled')) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500">
                <span class="text-xs font-bold text-slate-600">İptalleri Gizle</span>
            </label>
            <button type="submit" class="bg-slate-800 text-white font-bold px-6 py-2 rounded-xl text-sm hover:bg-black transition" style="color:white!important;">Filtrele</button>
            @if($request->filled('search') || $request->filled('status') || $request->filled('domain_id') || $request->filled('brand_id') || $request->filled('is_printed') || $request->filled('cargo_firm') || $request->filled('api_filter'))
                <a href="{{ $url::route('admin.orders') }}" class="text-slate-400 hover:text-red-500 transition" title="Filtreleri Temizle">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500">
                    @php
                        $sort = $request->query('sort', 'created_at');
                        $direction = $request->query('direction', 'desc');
                        
                        $getSortUrl = function($field) use ($sort, $direction, $request, $url) {
                            $newDirection = ($field === $sort && $direction === 'asc') ? 'desc' : 'asc';
                            $params = array_merge($request->all(), ['sort' => $field, 'direction' => $newDirection]);
                            return $url::current() . '?' . http_build_query($params);
                        };
                        
                        $renderSort = function($field, $label) use ($sort, $direction, $getSortUrl) {
                            $icon = '';
                            if ($sort === $field) {
                                $icon = $direction === 'asc' 
                                    ? '<svg class="w-3 h-3 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>'
                                    : '<svg class="w-3 h-3 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>';
                            } else {
                                $icon = '<svg class="w-3 h-3 text-slate-300 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>';
                            }
                            
                            return '<a href="'.$getSortUrl($field).'" class="group flex items-center gap-1.5 hover:text-brand-600 transition">'.$label.$icon.'</a>';
                        };
                    @endphp
                    <tr>
                        <th class="px-6 py-4 rounded-tl-xl border-b border-slate-200 w-10">
                            <input type="checkbox" @change="toggleAll($event)" class="w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500">
                        </th>
                        <th class="px-6 py-4 border-b border-slate-200">{!! $renderSort('created_at', 'Sipariş Tarihi') !!}</th>
                        <th class="px-6 py-4 border-b border-slate-200">{!! $renderSort('order_number', 'Sipariş No') !!}</th>
                        <th class="px-6 py-4 border-b border-slate-200">{!! $renderSort('customer', 'Müşteri') !!}</th>
                        <th class="px-6 py-4 border-b border-slate-200">Telefon</th>
                        <th class="px-6 py-4 border-b border-slate-200">{!! $renderSort('brand', 'Marka') !!}</th>
                        <th class="px-6 py-4 border-b border-slate-200">{!! $renderSort('domain', 'Domain Kaynağı') !!}</th>
                        <th class="px-6 py-4 border-b border-slate-200">{!! $renderSort('grand_total', 'Tutar') !!}</th>
                        <th class="px-6 py-4 border-b border-slate-200">{!! $renderSort('status', 'Durum') !!}</th>
                        <th class="px-6 py-4 border-b border-slate-200">Kargo</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 transition" :class="selectedOrders.includes('{{ $order->id }}') ? 'bg-brand-50/30' : ''">
                            <td class="px-6 py-4">
                                <input type="checkbox" value="{{ $order->id }}" x-model="selectedOrders" class="order-checkbox w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-bold text-slate-900">{{ $order->created_at->format('d.m.Y') }}</div>
                                <div class="text-[10px] text-slate-500">{{ $order->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-slate-900">{{ $order->order_number }}</span>
                                    @if($order->is_printed)
                                        <div class="text-green-500" title="Yazdırıldı">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </div>
                                    @endif
                                    @if($order->is_api)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wide {{ $order->api_approved ? 'bg-violet-100 text-violet-800 border border-violet-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}" title="Harici API">
                                            API{{ $order->api_approved ? '✓' : '?' }}
                                        </span>
                                    @endif
                                    @if($order->tracking_url)
                                        <a href="{{ $order->tracking_url }}" target="_blank" title="Kargom Nerede? ({{ $order->cargo_firm }})" class="text-brand-600 hover:text-brand-800 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $order->customer->full_name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-600">{{ $order->customer->phone ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($order->domain && $order->domain->brand)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-black uppercase bg-brand-50 text-brand-700 border border-brand-100">
                                        {{ $order->domain->brand->name }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-700">{{ $order->domain->domain_name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $order->offer->offer_name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ number_format($order->grand_total, 2) }} ₺</div>
                                @if($order->payment_method === 'credit_card')
                                    <div class="inline-flex items-center gap-1 text-[10px] font-black text-blue-600 uppercase">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                        K.Kartı
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-1 text-[10px] font-black text-slate-400 uppercase">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        Kapıda
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest 
                                    {{ $order->status == 'yeni' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $order->status == 'aranacak' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->status == 'onaylandı' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                    {{ $order->status == 'kargoya_verildi' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $order->status == 'teslim_edildi' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $order->status == 'iptal' ? 'bg-rose-100 text-rose-700' : '' }}
                                    {{ $order->status == 'iade' ? 'bg-orange-100 text-orange-700' : '' }}">
                                    {{ $order->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($order->cargo_firm)
                                    @php
                                        $cs = $cargoSettings->where('carrier_name', $order->cargo_firm)->first();
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                        {{ $cs ? $cs->display_name : ucfirst($order->cargo_firm) }}
                                    </span>
                                @else
                                    <span class="text-slate-300 italic text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ $url::route('admin.orders.show', $order) }}" class="p-2 bg-slate-50 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="Detay">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-500 font-medium">
                                Henüz herhangi bir sipariş bulunamadı.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

