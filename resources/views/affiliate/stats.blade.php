@extends('layouts.affiliate')

@section('title', 'İstatistikler')

@section('content')
<div class="mb-8">
    <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight font-display">Detaylı Performans & Raporlar</h2>
    <p class="text-slate-400 text-xs md:text-sm mt-1">Affiliate ortaklığı altındaki tüm tıklama, trafik ve komisyon kazanım detaylarınızı filtreleyerek inceleyin.</p>
</div>

<!-- Filters section -->
<div class="glassmorphic-card p-6 md:p-8 mb-8">
    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 font-display">Filtreleme Seçenekleri</h3>
    <form method="GET" action="{{ route('affiliate.stats') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <label for="start_date" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Başlangıç Tarihi</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}"
                   class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
        </div>

        <div>
            <label for="end_date" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Bitiş Tarihi</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}"
                   class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
        </div>

        <div>
            <label for="domain_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Satış Sitesi (Domain)</label>
            <select name="domain_id" id="domain_id"
                    class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
                <option value="">Tüm Sitelere Göre</option>
                @foreach($domains as $dom)
                    <option value="{{ $dom->id }}" {{ $domainId == $dom->id ? 'selected' : '' }}>{{ $dom->domain_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="channel" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Kanal</label>
            <select name="channel" id="channel"
                    class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
                <option value="">Tüm Kanallar</option>
                @foreach($channels as $chan)
                    <option value="{{ $chan }}" {{ $channel == $chan ? 'selected' : '' }}>{{ ucfirst($chan) }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-4 flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('affiliate.stats') }}" class="py-2.5 px-6 rounded-xl bg-slate-950/60 hover:bg-slate-950 border border-white/10 text-white font-bold text-xs transition-colors">Temizle</a>
            <button type="submit" class="py-2.5 px-6 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs tracking-wide transition-all hover:shadow-lg hover:shadow-teal-500/10">Sorgula</button>
        </div>
    </form>
</div>

<!-- Grid Metrics -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6 mb-8">
    <div class="glassmorphic-card p-5">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Tıklamalar</p>
        <h3 class="text-2xl font-extrabold text-white font-display">{{ number_format($clicksCount) }}</h3>
    </div>

    <div class="glassmorphic-card p-5">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Sipariş Sayısı</p>
        <h3 class="text-2xl font-extrabold text-white font-display">{{ number_format($commissionsCount) }}</h3>
    </div>

    <div class="glassmorphic-card p-5">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Dönüşüm Oranı</p>
        <h3 class="text-2xl font-extrabold text-white font-display">%{{ $clicksCount > 0 ? round(($commissionsCount / $clicksCount) * 100, 2) : 0 }}</h3>
    </div>

    <div class="glassmorphic-card p-5">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Brüt Hak Ediş</p>
        <h3 class="text-2xl font-extrabold text-white font-display">{{ number_format($totalGrossEarnings, 2) }} TL</h3>
    </div>

    <div class="glassmorphic-card p-5">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Net Hak Ediş</p>
        <h3 class="text-2xl font-extrabold text-teal-400 font-display">{{ number_format($totalNetEarnings, 2) }} TL</h3>
    </div>
</div>

<!-- Tabs for Click log / Commissions details -->
<div x-data="{ activeTab: 'commissions' }" class="space-y-6">
    <div class="flex border-b border-white/5 pb-0.5 gap-6">
        <button @click="activeTab = 'commissions'"
                :class="activeTab === 'commissions' ? 'border-teal-500 text-teal-400 font-bold' : 'border-transparent text-slate-400 hover:text-white'"
                class="pb-3 border-b-2 text-sm font-display tracking-wide transition-all focus:outline-none">
            Satış & Komisyon Kayıtları
        </button>
        <button @click="activeTab = 'clicks'"
                :class="activeTab === 'clicks' ? 'border-teal-500 text-teal-400 font-bold' : 'border-transparent text-slate-400 hover:text-white'"
                class="pb-3 border-b-2 text-sm font-display tracking-wide transition-all focus:outline-none">
            Trafik / Tıklama Kayıtları
        </button>
    </div>

    <!-- Commissions Table -->
    <div x-show="activeTab === 'commissions'" class="glassmorphic-card p-6 md:p-8 animate-[fadeIn_0.3s_ease]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <th class="pb-3">Sipariş ID</th>
                        <th class="pb-3">Domain</th>
                        <th class="pb-3">Paket</th>
                        <th class="pb-3">Kanal/Keyword</th>
                        <th class="pb-3 text-right">Net Hak Ediş</th>
                        <th class="pb-3 text-right">Vergi Tipi</th>
                        <th class="pb-3 text-center">Durum</th>
                        <th class="pb-3 text-right">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($commissions as $comm)
                        <tr class="group hover:bg-white/[0.01] transition-colors">
                            <td class="py-4">
                                <span class="font-bold text-white font-display text-xs">#{{ $comm->order_id }}</span>
                                <span class="block text-[9px] text-slate-500">Tutar: {{ number_format($comm->order_total, 2) }} TL</span>
                            </td>
                            <td class="py-4 text-xs font-semibold text-slate-300">
                                {{ $comm->domain ? $comm->domain->domain_name : '-' }}
                            </td>
                            <td class="py-4 text-xs text-slate-400">
                                {{ $comm->purchasedPackage ? $comm->purchasedPackage->name : 'Bilinmeyen Paket' }}
                            </td>
                            <td class="py-4 text-xs">
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-950/60 border border-white/5 text-[10px] font-bold text-slate-300 capitalize">
                                    {{ $comm->channel ?: 'Doğrudan' }}
                                </span>
                                @if($comm->keyword)
                                    <span class="block text-[9px] text-slate-500 mt-1">#{{ $comm->keyword }}</span>
                                @endif
                            </td>
                            <td class="py-4 text-right font-display font-bold text-xs text-teal-400">
                                {{ number_format($comm->net_amount, 2) }} TL
                            </td>
                            <td class="py-4 text-right text-[10px] font-bold uppercase text-slate-400">
                                @if($comm->tax_type === 'individual')
                                    Bireysel (%{{ \App\Models\Setting::val('affiliate_withholding_rate', 20) }} Stopaj)
                                @elseif($comm->tax_type === 'company')
                                    Kurumsal (+%{{ \App\Models\Setting::val('affiliate_vat_rate', 20) }} KDV)
                                @else
                                    Muaf
                                @endif
                            </td>
                            <td class="py-4 text-center">
                                @if($comm->status === 'pending')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-500/10 border border-amber-500/20 text-amber-400 uppercase">Bekliyor</span>
                                @elseif($comm->status === 'approved')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-teal-500/10 border border-teal-500/20 text-teal-400 uppercase">Onaylandı</span>
                                @elseif($comm->status === 'withdrawing')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-sky-500/10 border border-sky-500/20 text-sky-400 uppercase">Çekim Sırasında</span>
                                @elseif($comm->status === 'paid')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 uppercase">Ödendi</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-500/10 border border-rose-500/20 text-rose-400 uppercase">İptal/Red</span>
                                @endif
                            </td>
                            <td class="py-4 text-right text-xs text-slate-500">
                                {{ $comm->created_at->format('d.m.Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500 text-xs font-medium">Filtre kriterlerinize uyan hak ediş bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions->hasPages())
            <div class="mt-6 pt-4 border-t border-white/5">
                {{ $commissions->appends(request()->except('commissions_page'))->links() }}
            </div>
        @endif
    </div>

    <!-- Clicks Table -->
    <div x-show="activeTab === 'clicks'" class="glassmorphic-card p-6 md:p-8 animate-[fadeIn_0.3s_ease]" x-cloak>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <th class="pb-3">Tıklama ID</th>
                        <th class="pb-3">Site (Domain)</th>
                        <th class="pb-3">Kanal / Etiket</th>
                        <th class="pb-3">IP / Cihaz</th>
                        <th class="pb-3">Referer / Kaynak</th>
                        <th class="pb-3 text-right">Zaman</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($clicks as $click)
                        <tr class="group hover:bg-white/[0.01] transition-colors">
                            <td class="py-4">
                                <span class="font-bold text-white font-display text-xs truncate block max-w-[150px]" title="{{ $click->click_id }}">{{ $click->click_id }}</span>
                            </td>
                            <td class="py-4 text-xs font-semibold text-slate-300">
                                {{ $click->link && $click->link->domain ? $click->link->domain->domain_name : '-' }}
                            </td>
                            <td class="py-4 text-xs">
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-950/60 border border-white/5 text-[10px] font-bold text-slate-300 capitalize">
                                    {{ $click->channel ?: 'Doğrudan' }}
                                </span>
                                @if($click->keyword)
                                    <span class="block text-[9px] text-slate-500 mt-1">#{{ $click->keyword }}</span>
                                @endif
                            </td>
                            <td class="py-4 text-xs">
                                <span class="font-semibold text-slate-400 block">{{ $click->ip_address }}</span>
                                <span class="text-[9px] text-slate-500 capitalize">{{ $click->device ?: 'Masaüstü' }}</span>
                            </td>
                            <td class="py-4 text-xs text-slate-500 truncate max-w-[200px]" title="{{ $click->referer }}">
                                {{ $click->referer ?: 'Doğrudan Ziyaret' }}
                            </td>
                            <td class="py-4 text-right text-xs text-slate-500">
                                {{ $click->created_at->format('d.m.Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500 text-xs font-medium">Filtre kriterlerinize uyan tıklama trafiği bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clicks->hasPages())
            <div class="mt-6 pt-4 border-t border-white/5">
                {{ $clicks->appends(request()->except('clicks_page'))->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
