@extends('layouts.admin')

@section('title', 'Domain Ağ Yönetimi')

@section('content')
<div class="flex flex-col gap-6 fade-in-up">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Domain (Ağ)</h2>
                <button 
                    x-data="favoriteManager" 
                    @click="toggle('admin.domains', 'Domain Yönetimi', '{{ route('admin.domains') }}', 'globe')"
                    class="favorite-btn"
                    style="display: inline-flex !important; min-width: 32px; min-height: 32px;"
                    title="Hızlı İşlemlere Ekle/Kaldır"
                >
                    <svg data-favorite-id="admin.domains" class="w-6 h-6 {{ (auth()->user()->favorites && collect(auth()->user()->favorites)->contains('id', 'admin.domains')) ? 'text-amber-400' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-slate-500 font-medium">Aktif satış hunisi (funnel) alan adları ve verim yönetimi</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('domains.edit'))
            <a href="{{ route('admin.cdn-providers.index') }}" class="bg-indigo-600 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-indigo-700 transition shadow-md flex items-center" style="color:white!important;">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span style="color:white;">CDN Yönetimi</span>
            </a>
            <a href="{{ route('admin.cloudflare-accounts.index') }}" class="bg-orange-600 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-orange-700 transition shadow-md flex items-center" style="color:white!important;">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                <span style="color:white;">Cloudflare</span>
            </a>
            <a href="{{ route('admin.domains.create') }}" class="bg-brand-600 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-brand-700 transition shadow-md flex items-center" style="color:white!important;">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span style="color:white;">Domain Ekle</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Search Area -->
    <div class="premium-card p-4">
        <form action="{{ route('admin.domains') }}" method="GET" class="flex gap-4 items-center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Domain adı veya API domain kimliği ara..." class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <button type="submit" class="bg-slate-800 text-white font-bold px-6 py-2 rounded-xl text-sm hover:bg-black transition" style="color:white!important;">Ara</button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.domains') }}" class="text-slate-400 hover:text-red-500 transition" title="Aramayı Temizle">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg font-medium shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 mb-4 rounded-lg font-medium shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-900 p-4 mb-4 rounded-lg font-medium shadow-sm">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Data Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500">
                    <tr>
                        <th class="px-6 py-4 border-b border-slate-200 has-tooltip" data-tooltip="Alan adı ve bağlı olduğu ürün">Alan Adı (Domain)</th>
                        <th class="px-6 py-4 border-b border-slate-200 has-tooltip" data-tooltip="Siteye giren tekil ziyaretçi ve toplam hit sayısı">Ziyaretçi</th>
                        <th class="px-6 py-4 border-b border-slate-200 has-tooltip" data-tooltip="Sayfanın yarısına (%50) kadar inen kullanıcılar">Scroll %50</th>
                        <th class="px-6 py-4 border-b border-slate-200 has-tooltip" data-tooltip="Sipariş formunu açan / doldurmaya başlayanlar">Form Açma</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-center has-tooltip" data-tooltip="Sipariş / Tekil Ziyaretçi oranı">Dönüşüm (CR)</th>
                        <th class="px-6 py-4 border-b border-slate-200 has-tooltip" data-tooltip="Toplam ve Onaylı Sipariş / Ciro bilgileri">Satış & Ciro</th>
                        <th class="px-6 py-4 border-b border-slate-200 has-tooltip" data-tooltip="Reklam gideri ve Net Kâr">Gider / Net</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-center has-tooltip" data-tooltip="Net Kâr üzerinden Birim ve Sepet ortalaması">Birim / Sepet</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-right">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($domains as $domain)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 flex items-center">
                                    <div class="w-2 h-2 rounded-full {{ $domain->is_active ? 'bg-green-500' : 'bg-slate-300' }} mr-2"></div>
                                    {{ $domain->domain_name }}
                                    <a href="https://{{ $domain->domain_name }}" target="_blank" class="ml-2 text-slate-400 hover:text-brand-600 transition" title="Siteyi Yeni Sekmede Aç">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                </div>
                                <div class="text-[10px] text-brand-600 font-bold mt-1 uppercase tracking-wider">
                                    {{ $domain->brand->name ?? 'Marka Atanmadı' }}
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">API domain: {{ $domain->api_domain_id ?: '—' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-base font-black text-slate-900">{{ number_format($domain->unique_visitor_count ?? 0) }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold">TEKİL</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400">Toplam: {{ number_format($domain->visitor_count ?? 0) }} hit</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="font-black text-slate-900 text-[13px]">{{ number_format($domain->scroll_50_unique ?? 0) }} <span class="text-[9px] text-slate-400 font-bold uppercase">Tekil</span></div>
                                    <div class="text-[10px] text-slate-400 font-medium">{{ number_format($domain->scroll_50_total ?? 0) }} hit</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="font-black text-slate-900 text-[13px]">{{ number_format($domain->form_open_unique ?? 0) }} <span class="text-[9px] text-slate-400 font-bold uppercase">Tekil</span></div>
                                    <div class="text-[10px] text-slate-400 font-medium">{{ number_format($domain->form_open_total ?? 0) }} hit</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $unique = $domain->unique_visitor_count ?? 0;
                                    $ordersCount = $domain->orders_count ?? 0;
                                    $cr = $unique > 0 ? ($ordersCount / $unique) * 100 : 0;
                                @endphp
                                <div class="inline-flex flex-col items-center px-3 py-1 rounded-lg {{ $cr > 3 ? 'bg-green-50 text-green-700' : ($cr > 1 ? 'bg-blue-50 text-blue-700' : 'bg-slate-50 text-slate-500') }}">
                                    <span class="text-sm font-black">%{{ number_format($cr, 2) }}</span>
                                    <span class="text-[8px] font-bold uppercase">Dönüşüm</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div class="text-[13px] font-black text-emerald-600">₺{{ number_format($domain->orders_sum_grand_total ?? 0, 2) }} <span class="text-[9px] text-slate-400 font-bold uppercase">(Top)</span></div>
                                    <div class="text-[13px] font-black text-emerald-700">₺{{ number_format($domain->approved_orders_sum_grand_total ?? 0, 2) }} <span class="text-[9px] text-slate-400 font-bold uppercase">(Ony)</span></div>
                                    <div class="text-[10px] text-slate-500 font-bold italic mt-1">{{ $domain->orders_count }} / {{ $domain->approved_orders_count }} Sipariş</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    @php
                                        $totalSales = $domain->orders_sum_grand_total ?? 0;
                                        $totalExpenses = $domain->expenses_sum_amount ?? 0;
                                        $netProfit = $totalSales - $totalExpenses;
                                    @endphp
                                    <div class="text-[13px] font-bold text-rose-500">-₺{{ number_format($totalExpenses, 2) }}</div>
                                    <div class="text-[12px] font-black {{ $netProfit >= 0 ? 'text-indigo-600' : 'text-red-600' }}">
                                        {{ $netProfit >= 0 ? 'NET:' : 'ZARAR:' }} ₺{{ number_format($netProfit, 2) }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col gap-1 items-center">
                                    @php 
                                        $totalQty = $domain->total_quantity ?? 0;
                                        $totalSalesVal = $domain->orders_sum_grand_total ?? 0;
                                        $totalExp = $domain->expenses_sum_amount ?? 0;
                                        $netTotal = $totalSalesVal - $totalExp;
                                        
                                        $avgPrice = $totalQty > 0 ? ($netTotal / $totalQty) : 0;
                                        $aov = ($domain->orders_count ?? 0) > 0 ? ($netTotal / $domain->orders_count) : 0;
                                    @endphp
                                    <div class="text-xs font-black text-slate-700">₺{{ number_format($avgPrice, 2) }}</div>
                                    <div class="text-xs font-black text-indigo-700">₺{{ number_format($aov, 2) }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    @if(auth()->user()->hasPermission('domains.edit'))
                                    <form action="{{ route('admin.domains.clone', $domain) }}" method="POST" class="inline" onsubmit="return confirmAction(event, 'Bu siteyi tüm içeriği ile kopyalamak istediğinize emin misiniz?', 'Siteyi Kopyala')">
                                        @csrf
                                        <button type="submit" class="p-2 bg-indigo-50 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-100 rounded-lg transition" title="Kopyala (Çoğalt)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.domains.edit', $domain) }}" class="p-2 bg-slate-50 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="Düzenle">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('domains.delete'))
                                    <form action="{{ route('admin.domains.destroy', $domain) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, 'DİKKAT: [target] sitesini sildiğinizde; bağlı tüm ciro verileri, sipariş geçmişi, istatistikler ve görseller KALICI OLARAK silinecektir. Bu işlem geri alınamaz!', '{{ $domain->domain_name }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-red-50 text-red-400 hover:text-red-600 hover:bg-red-100 rounded-lg transition" title="Tamamen Sil">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 font-medium">
                                Sistemde takip edilen bir domain yok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
