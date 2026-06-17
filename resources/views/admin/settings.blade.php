@extends('layouts.admin')

@section('title', 'Sistem & API Ayarları')

@section('content')
<div class="max-w-5xl mx-auto flex flex-col gap-8 fade-in-up">
    <!-- Page Header -->
    <div class="mb-2">
        <div class="flex items-center gap-3">
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Sistem Ayarları</h2>
            <button 
                x-data="favoriteManager" 
                @click="toggle('admin.settings.index', 'Sistem Ayarları', '{{ route('admin.settings.index') }}', 'gear')"
                class="favorite-btn"
                style="display: inline-flex !important; min-width: 32px; min-height: 32px;"
                title="Hızlı İşlemlere Ekle/Kaldır"
            >
                <svg data-favorite-id="admin.settings.index" class="w-6 h-6 {{ (auth()->user()->favorites && collect(auth()->user()->favorites)->contains('id', 'admin.settings.index')) ? 'text-amber-400' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
            </button>
        </div>
        <p class="text-slate-500 font-bold mb-0">Platform genelindeki kurumsal kimlik ve entegrasyon ayarları.</p>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- Command Execution Area -->
    <div class="premium-card p-0 border-none overflow-hidden shadow-xl ring-1 ring-slate-200">
        <div class="bg-slate-900 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center border border-slate-700 shadow-inner">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest">Sistem & Veritabanı Servisleri</h3>
                    <p class="text-[10px] text-slate-400 font-bold">Sunucu terminaline erişmeden sistemi güncelleyin</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-[10px] font-black text-green-500 uppercase tracking-tighter">Sunucu Aktif</span>
            </div>
        </div>
        
        <div class="p-6 bg-white grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Veritabanı Güncelleme --}}
            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/50 flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-black text-slate-900 uppercase mb-1">Veritabanını Güncelle (Migrate)</h4>
                    <p class="text-[10px] text-slate-500 font-medium mb-4">Yeni tabloları veya sütunları (Örn: TikTok Pixel) sisteme tanımlar.</p>
                </div>
                @if(auth()->user()->hasPermission('settings.edit'))
                <form action="{{ route('admin.settings.migrate') }}" method="POST" onsubmit="return confirmAction(event, 'Veritabanı güncellemelerini (migrate) çalıştırmak istediğinize emin misiniz?', 'Veritabanı Güncelle');">
                    @csrf
                    <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-black py-2.5 px-4 rounded-xl shadow-md transition-all flex items-center justify-center text-[11px] uppercase tracking-wide group">
                        <svg class="w-4 h-4 mr-2 text-brand-400 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        Migration Çalıştır
                    </button>
                </form>
                @else
                <div class="text-[10px] font-black text-slate-400 uppercase text-center border-t border-slate-200 pt-4">YETKİNİZ BULUNMUYOR</div>
                @endif
            </div>

            {{-- Önbellek Temizleme --}}
            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/50 flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-black text-slate-900 uppercase mb-1">Önbelleği Boşalt (Clear Cache)</h4>
                    <p class="text-[10px] text-slate-500 font-medium mb-4">Dosya değişikliklerinin veya bekleyen ayarların hemen etkili olmasını sağlar.</p>
                </div>
                @if(auth()->user()->hasPermission('settings.edit'))
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST" onsubmit="return confirmAction(event, 'Sistem önbelleğini temizlemek istediğinize emin misiniz?', 'Önbellek Temizle');">
                    @csrf
                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-black py-2.5 px-4 rounded-xl shadow-md transition-all flex items-center justify-center text-[11px] uppercase tracking-wide group">
                        <svg class="w-4 h-4 mr-2 text-white group-hover:animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Cache Temizle
                    </button>
                </form>
                @else
                <div class="text-[10px] font-black text-slate-400 uppercase text-center border-t border-slate-200 pt-4">YETKİNİZ BULUNMUYOR</div>
                @endif
            </div>

        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-8">
        @csrf
        
        <!-- Branding Identity -->
        <div class="premium-card p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Kurumsal Kimlik (Logolar)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Sistem Ana Logosu (Dark/Light Uyumlu PNG)</label>
                    @if(!empty($settings['site_logo']))
                        <div class="mb-3"><img src="{{ \App\Models\Setting::mediaUrl($settings['site_logo']) }}" class="h-16 object-contain bg-slate-50 p-2 rounded-lg border shadow-sm"></div>
                    @endif
                    <input type="file" name="logo" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition cursor-pointer">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tarayıcı Simgesi (Favicon - ICO/PNG)</label>
                    @if(!empty($settings['site_favicon']))
                        <div class="mb-3"><img src="{{ \App\Models\Setting::mediaUrl($settings['site_favicon']) }}" class="h-8 w-8 object-contain bg-slate-50 p-1 rounded border shadow-sm"></div>
                    @endif
                    <input type="file" name="favicon" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition cursor-pointer">
                </div>
            </div>
        </div>

        <!-- SEO & Tracking -->
        <div class="premium-card p-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">SEO & Analitik Doğrulamaları</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Google Site Verification (Meta content değeri)</label>
                    <input type="text" name="google_site_verification" value="{{ $settings['google_site_verification'] ?? '' }}" placeholder="Örn: XyZ123_aBc-456_defGHI..." class="w-full px-4 py-2 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                    <p class="text-[11px] font-medium text-slate-400 mt-1">Sitenizin header kısmına meta etiketi olarak Search Console onayı için eklenir.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Google Analytics İzleme Kimliği</label>
                    <input type="text" name="google_analytics_id" value="{{ $settings['google_analytics_id'] ?? '' }}" placeholder="G-XXXXXXXXXX" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Google Tag Manager Kimliği</label>
                    <input type="text" name="google_tag_manager_id" value="{{ $settings['google_tag_manager_id'] ?? '' }}" placeholder="GTM-XXXXXXX" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                </div>
            </div>
        </div>



        <!-- Submit Button -->
        <div class="flex justify-end pb-8">
            @if(auth()->user()->hasPermission('settings.edit'))
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 px-10 rounded-xl shadow-[0_8px_20px_rgba(22,163,74,0.25)] transition-transform hover:-translate-y-1" style="color:#ffffff !important;">
                <span style="color:#ffffff;">Tüm Ayarları Kaydet</span>
            </button>
            @else
            <div class="bg-slate-100 text-slate-400 font-black py-3.5 px-10 rounded-xl uppercase tracking-widest text-xs border border-slate-200">
                AYARLARI DÜZENLEME YETKİNİZ BULUNMUYOR
            </div>
            @endif
        </div>
    </form>
</div>
@endsection
