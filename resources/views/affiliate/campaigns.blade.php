@extends('layouts.affiliate')

@section('title', 'Satış Siteleri')

@section('content')
<div class="mb-8">
    <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight font-display">Aktif Satış Siteleri & Kampanyalar</h2>
    <p class="text-slate-400 text-xs md:text-sm mt-1">Hemen satış ortağı olabileceğiniz ve tanıtım yaparak komisyon kazanabileceğiniz aktif satış sitelerimiz.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8" x-data="{ openModal: false, selectedDomainId: null, selectedDomainName: '' }">
    @forelse($domains as $domain)
        <div class="glassmorphic-card p-6 md:p-8 flex flex-col justify-between h-full relative overflow-hidden group">
            <!-- Glow background overlay on hover -->
            <div class="absolute inset-0 bg-gradient-to-br from-teal-500/0 via-teal-500/0 to-teal-500/0 group-hover:to-teal-500/[0.02] pointer-events-none transition-all duration-500"></div>

            <div>
                <!-- Domain Header info -->
                <div class="flex items-start justify-between border-b border-white/5 pb-4 mb-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-500/10 to-emerald-500/10 flex items-center justify-center border border-teal-500/20 text-teal-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white font-display tracking-wide">{{ $domain->domain_name }}</h3>
                            <a href="https://{{ $domain->domain_name }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-teal-400 hover:underline font-semibold mt-0.5">
                                Siteyi Ziyaret Et
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    </div>
                    <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-extrabold bg-teal-500/10 border border-teal-500/20 text-teal-400 uppercase font-display">AKTİF</span>
                </div>

                <!-- Domain description/settings if exists -->
                @if($domain->affiliateSetting && $domain->affiliateSetting->affiliate_description)
                    <p class="text-slate-400 text-xs leading-relaxed mb-4 bg-slate-950/20 p-4 border border-white/5 rounded-2xl">
                        {{ $domain->affiliateSetting->affiliate_description }}
                    </p>
                @endif

                <!-- Warning Text and Forbidden Terms -->
                @if($domain->affiliateSetting && ($domain->affiliateSetting->warning_text || $domain->affiliateSetting->forbidden_terms))
                    <div class="mb-6 p-4 rounded-2xl bg-amber-500/[0.03] border border-amber-500/10 space-y-3">
                        @if($domain->affiliateSetting->warning_text)
                            <div>
                                <span class="flex items-center gap-1.5 text-[9px] font-extrabold text-amber-400 uppercase tracking-widest mb-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    Önemli Uyarı / Kurallar
                                </span>
                                <p class="text-slate-300 text-xs leading-relaxed mb-0">{{ $domain->affiliateSetting->warning_text }}</p>
                            </div>
                        @endif

                        @if($domain->affiliateSetting->forbidden_terms)
                            <div class="{{ $domain->affiliateSetting->warning_text ? 'border-t border-white/5 pt-3' : '' }}">
                                <span class="flex items-center gap-1.5 text-[9px] font-extrabold text-rose-400 uppercase tracking-widest mb-1.5">
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    Yasaklı Kelimeler / Reklamlar
                                </span>
                                <p class="text-slate-300 text-xs leading-relaxed mb-0">{{ $domain->affiliateSetting->forbidden_terms }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Package list and commissions -->
                <div class="space-y-3.5 mb-8">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">PAKET KOMİSYON ORANLARI</h4>
                    
                    @php
                        $commissions = $domain->affiliatePackageCommissions->where('is_affiliate_active', true);
                    @endphp

                    @forelse($commissions as $comm)
                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-950/40 border border-white/5 group-hover:border-white/10 transition-colors">
                            <div class="overflow-hidden pr-3">
                                <span class="font-bold text-white text-xs font-display truncate block">{{ $comm->package ? $comm->package->offer_name : 'Tanımsız Paket' }}</span>
                                @if($comm->affiliate_description)
                                    <span class="text-[10px] text-slate-500 truncate block mt-0.5">{{ $comm->affiliate_description }}</span>
                                @else
                                    <span class="text-[10px] text-slate-500 block mt-0.5">Fiyat: {{ number_format($comm->package ? $comm->package->price : 0, 2) }} TL</span>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0">
                                @if($comm->commission_type === 'fixed')
                                    <span class="text-sm font-extrabold text-teal-400 font-display">+{{ number_format($comm->commission_amount, 2) }} TL</span>
                                @else
                                    <span class="text-sm font-extrabold text-teal-400 font-display">+%{{ number_format($comm->commission_rate, 2) }}</span>
                                @endif
                                <span class="block text-[8px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">NET KAZANÇ</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-xs italic">Bu site için henüz aktif komisyon tanımlanmamıştır.</p>
                    @endforelse
                </div>
            </div>

            <!-- Campaign Action Buttons -->
            <div class="flex items-center gap-3 pt-4 border-t border-white/5 flex-shrink-0">
                <button @click="selectedDomainId = '{{ $domain->id }}'; selectedDomainName = '{{ $domain->domain_name }}'; openModal = true;"
                        class="flex-1 py-3 px-4 rounded-2xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs flex items-center justify-center gap-2 tracking-wide transition-all hover:shadow-lg hover:shadow-teal-500/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    Link Üret
                </button>
                <a href="{{ route('affiliate.media', ['domain_id' => $domain->id]) }}"
                   class="py-3 px-4 rounded-2xl bg-slate-950/60 hover:bg-slate-950 border border-white/10 text-white font-bold text-xs flex items-center justify-center gap-2 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Medyalar
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full glassmorphic-card p-12 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            <h3 class="text-base font-bold text-white font-display">Henüz Aktif Kampanya Bulunmuyor</h3>
            <p class="text-xs text-slate-500 mt-1">Sisteme bağlı aktif satış siteleri tanımlandığında burada listelenecektir.</p>
        </div>
    @endforelse

    <!-- Link Generation Dialog/Modal -->
    <div x-show="openModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="w-full max-w-lg glass rounded-[32px] p-6 md:p-8 relative border border-white/10"
             @click.away="openModal = false">
            <div class="flex items-center justify-between border-b border-white/5 pb-4 mb-6">
                <div>
                    <h3 class="text-base font-bold text-white font-display">Affiliate Linki Oluştur</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5" x-text="'Seçilen Domain: ' + selectedDomainName"></p>
                </div>
                <button @click="openModal = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('affiliate.links.generate') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="domain_id" :value="selectedDomainId">

                <!-- Channel selection -->
                <div>
                    <label for="channel" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">Kanal / Kaynak (Örn: Instagram, Tiktok, Bio)</label>
                    <select name="channel" id="channel"
                            class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all">
                        <option value="">Doğrudan / Genel</option>
                        <option value="instagram">Instagram</option>
                        <option value="tiktok">TikTok</option>
                        <option value="facebook">Facebook</option>
                        <option value="youtube">YouTube</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="website">Kişisel Web Sitesi</option>
                    </select>
                </div>

                <!-- Campaign Keyword tag -->
                <div>
                    <label for="keyword" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">Kampanya Etiketi / Keyword (İsteğe Bağlı)</label>
                    <input type="text" name="keyword" id="keyword"
                           class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                           placeholder="indirim20, story_nisan vb.">
                </div>

                <!-- Media asset selector -->
                <div>
                    <label for="media_id" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">İlişkili Görsel/Medya (İsteğe Bağlı)</label>
                    <select name="media_id" id="media_id"
                            class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all">
                        <option value="">İlişkili Medya Yok</option>
                        @foreach($mediaAssets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->title }} ({{ $asset->media_type }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Target path (landing page defaults) -->
                <div>
                    <label for="target_path" class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">Hedef Alt Sayfa Yolu (İsteğe Bağlı)</label>
                    <input type="text" name="target_path" id="target_path"
                           class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                           placeholder="/ (Varsayılan Ana Sayfa)">
                </div>

                <div class="pt-4 flex items-center gap-3">
                    <button type="button" @click="openModal = false"
                            class="flex-1 py-3 px-4 rounded-2xl bg-slate-950/60 hover:bg-slate-950 border border-white/10 text-white font-bold text-xs flex items-center justify-center transition-colors">
                        Vazgeç
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 px-4 rounded-2xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs flex items-center justify-center transition-all hover:shadow-lg hover:shadow-teal-500/10">
                        Oluştur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
