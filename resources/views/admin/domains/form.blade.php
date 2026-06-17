@extends('layouts.admin')

@php
    $isCreate = ! $domain->exists;
    $hasAddress = !empty($domain->domain_name) && !empty($domain->cloudflare_zone_id);
    $showPicker = $isCreate || !$hasAddress;
    $pageTitle = $isCreate ? 'Yeni domain & funnel' : 'Domain düzenle';
    $domainFormTabDefault = $errors->any() ? 'infra' : null;
    $domainBunnyBase = !empty($domain->bunny_hostname) ? 'https://'.$domain->bunny_hostname : null;
@endphp

@section('title', $pageTitle)

@section('content')
<style>
    .domain-form-page { --df-ring: rgba(22, 163, 74, 0.25); }
    /* background-color only — shorthand `background` breaks select chevron (repeating SVG) */
    .domain-input {
        width: 100%;
        border-radius: 1rem;
        border: 1px solid rgb(226 232 240);
        background-color: rgb(255 255 255);
        padding: 0.875rem 1rem;
        color: rgb(15 23 42);
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .domain-input:focus {
        border-color: rgb(34 197 94);
        box-shadow: 0 0 0 3px var(--df-ring);
    }
    .domain-select-chevron {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-right: 2.75rem;
        background-color: rgb(255 255 255);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23ea580c' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.875rem center;
        background-size: 1.125rem 1.125rem;
        cursor: pointer;
    }
    .domain-select-chevron:focus {
        border-color: rgb(251 146 60);
        box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.25);
    }
    .domain-cf-account-box {
        border-radius: 1rem;
        border: 1px solid rgba(251, 146, 60, 0.35);
        background: linear-gradient(145deg, rgba(255, 247, 237, 0.95) 0%, rgb(255 255 255) 45%, rgba(255, 251, 235, 0.6) 100%);
        box-shadow: 0 4px 20px -8px rgba(234, 88, 12, 0.18);
    }
    .domain-label {
        display: block;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgb(100 116 139);
        margin-bottom: 0.375rem;
    }
    .domain-section-head { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; }
    .domain-step-num {
        display: flex;
        height: 2.5rem;
        width: 2.5rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 900;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.12);
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    }
    .domain-toggle-focus:focus-within {
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.35);
    }
    .domain-tab-shell {
        border-radius: 1rem;
        background: rgba(241, 245, 249, 0.85);
        border: 1px solid rgb(226 232 240);
        padding: 0.35rem;
    }
    .domain-tab-btn {
        flex: 1 1 auto;
        min-width: max-content;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.65rem 0.85rem;
        border-radius: 0.75rem;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: rgb(100 116 139);
        background: transparent;
        border: none;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        white-space: nowrap;
    }
    .domain-tab-btn:hover { color: rgb(51 65 85); background: rgba(255,255,255,0.5); }
    .domain-tab-btn-active {
        background: rgb(255 255 255) !important;
        color: rgb(22 101 52) !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .offer-pack-card {
        border-radius: 1rem;
        border: 1px solid rgb(226 232 240);
        background: rgb(255 255 255);
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .offer-file-input::-webkit-file-upload-button {
        margin-right: 0.75rem;
        padding: 0.4rem 0.75rem;
        border-radius: 0.5rem;
        border: 0;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: rgb(241 245 249);
        color: rgb(51 65 85);
        cursor: pointer;
    }
    .offer-file-input::file-selector-button {
        margin-right: 0.75rem;
        padding: 0.4rem 0.75rem;
        border-radius: 0.5rem;
        border: 0;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: rgb(241 245 249);
        color: rgb(51 65 85);
        cursor: pointer;
    }
    .offer-item-input {
        padding: 0.5rem 0.75rem !important;
        border-radius: 0.75rem !important;
        font-size: 0.75rem !important;
    }
    .compact-select {
        padding-right: 2rem !important;
        background-position: right 0.5rem center !important;
    }
</style>

<div class="domain-form-page flex flex-col gap-6 fade-in-up max-w-7xl mx-auto pb-32 -mt-2">
    {{-- Page header --}}
    <div class="-mx-4 px-4 py-4 md:mx-0 md:px-6 md:rounded-2xl md:border md:border-slate-200/80 md:shadow-sm bg-white">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <a href="{{ route('admin.domains') }}" class="text-[11px] font-bold text-slate-400 hover:text-brand-600 transition">Domain listesi</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-[11px] font-black uppercase tracking-wider text-brand-700">{{ $isCreate ? 'Yeni kayıt' : 'Düzenle' }}</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight truncate">{{ $pageTitle }}</h1>
                <p class="text-sm text-slate-500 font-medium mt-1 line-clamp-2">
                    @if($isCreate)
                        Alan adı, ürün ve paketleri tanımla; galeri görselleriyle funnel sayfanı oluştur.
                    @else
                        <span class="font-mono text-slate-700">{{ $domain->domain_name }}</span> — Teknik, pazarlama ve satış paketlerini güncelle.
                    @endif
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center shrink-0">
                @if(!$isCreate)
                    {{-- Hidden Forms for Action Buttons to avoid nested form flex collapse --}}
                    <form id="reset-stats-form" action="{{ route('admin.domains.reset-stats', $domain) }}" method="POST" class="hidden" onsubmit="return confirmAction(event, '[target] sitesine ait tüm ziyaretçi ve trafik verilerini sıfırlamak istediğinize emin misiniz?', 'İstatistikleri Sıfırla', '{{ $domain->domain_name }}')">
                        @csrf
                    </form>
                    <form id="delete-domain-form" action="{{ route('admin.domains.destroy', $domain) }}" method="POST" class="hidden" onsubmit="return confirmDelete(event, 'DİKKAT: [target] sitesini sildiğinizde; bağlı tüm ciro verileri, sipariş geçmişi ve istatistikler KALICI OLARAK silinecektir. Bu işlem geri alınamaz!', '{{ $domain->domain_name }}');">
                        @csrf
                        @method('DELETE')
                    </form>

                    {{-- Action buttons targeting forms via HTML5 'form' attribute --}}
                    <button type="submit" form="reset-stats-form" class="inline-flex justify-center items-center px-4 py-3 rounded-2xl font-bold text-amber-600 bg-amber-50 hover:bg-amber-100 transition text-sm" style="height: 42px !important; padding: 12px 16px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; white-space: nowrap !important;">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        İstatistikleri Sıfırla
                    </button>
                    <button type="submit" form="delete-domain-form" class="inline-flex justify-center items-center px-4 py-3 rounded-2xl font-bold text-red-600 bg-red-50 hover:bg-red-100 transition text-sm order-3 sm:order-0" style="height: 42px !important; padding: 12px 16px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; white-space: nowrap !important;">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Siteyi Sil
                    </button>
                @endif
                <a href="{{ route('admin.domains') }}" class="inline-flex justify-center items-center px-5 py-3 rounded-2xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition text-sm order-2 sm:order-1" style="height: 42px !important; padding: 12px 20px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; white-space: nowrap !important; text-decoration: none !important;">
                    İptal
                </a>
                <button type="button" onclick="document.getElementById('master-submit-btn').click()" class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-2xl font-black text-sm text-white bg-slate-900 hover:bg-black shadow-lg shadow-slate-900/20 transition order-1 sm:order-2" style="height: 42px !important; padding: 12px 24px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; white-space: nowrap !important;">
                    <svg class="w-5 h-5 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ $isCreate ? 'Funnel\'ı oluştur' : 'Değişiklikleri kaydet' }}
                </button>
            </div>
        </div>
        <div class="domain-tab-shell mt-4">
            <div class="flex gap-0.5 overflow-x-auto pb-0.5 -mx-0.5 px-0.5" role="tablist" aria-label="Form sekmeleri">
                <button type="button" role="tab" id="domain-tab-btn-infra" data-domain-tab="infra" aria-selected="true" onclick="domainFormSwitchTab('infra')" class="domain-tab-btn domain-tab-btn-active">
                    <svg class="w-3.5 h-3.5 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
                    Altyapı
                </button>
                <button type="button" role="tab" id="domain-tab-btn-marketing" data-domain-tab="marketing" aria-selected="false" onclick="domainFormSwitchTab('marketing')" class="domain-tab-btn">
                    <svg class="w-3.5 h-3.5 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    Pazarlama
                </button>
                <button type="button" role="tab" id="domain-tab-btn-payment" data-domain-tab="payment" aria-selected="false" onclick="domainFormSwitchTab('payment')" class="domain-tab-btn">
                    <svg class="w-3.5 h-3.5 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Ödeme
                </button>
                <button type="button" role="tab" id="domain-tab-btn-offers" data-domain-tab="offers" aria-selected="false" onclick="domainFormSwitchTab('offers')" class="domain-tab-btn">
                    <svg class="w-3.5 h-3.5 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Paketler
                </button>
                <button type="button" role="tab" id="domain-tab-btn-gallery" data-domain-tab="gallery" aria-selected="false" onclick="domainFormSwitchTab('gallery')" class="domain-tab-btn">
                    <svg class="w-3.5 h-3.5 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Galeri
                </button>
                <button type="button" role="tab" id="domain-tab-btn-upsell" data-domain-tab="upsell" aria-selected="false" onclick="domainFormSwitchTab('upsell')" class="domain-tab-btn">
                    <svg class="w-3.5 h-3.5 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    Upsell
                </button>
                <button type="button" role="tab" id="domain-tab-btn-affiliate" data-domain-tab="affiliate" aria-selected="false" onclick="domainFormSwitchTab('affiliate')" class="domain-tab-btn text-emerald-600">
                    <svg class="w-3.5 h-3.5 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Affiliate
                </button>
                @if(!$isCreate)
                <button type="button" role="tab" id="domain-tab-btn-expenses" data-domain-tab="expenses" aria-selected="false" onclick="domainFormSwitchTab('expenses')" class="domain-tab-btn text-indigo-600">
                    <svg class="w-3.5 h-3.5 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Masraflar
                </button>
                @endif
            </div>
        </div>
        <p class="text-[10px] text-slate-400 font-semibold mt-3 text-center sm:text-left px-0.5">Sekmeler sadece düzeni böler; <strong class="text-slate-600">Kaydet</strong> tüm alanları birlikte gönderir.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 text-sm font-semibold shadow-sm" role="alert">
            <p class="font-black uppercase text-xs tracking-wide text-red-900 mb-2">Kayıt öncesi düzelt</p>
            <ul class="list-disc list-inside space-y-1 text-red-800/90">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="hidden fixed inset-0 bg-slate-900/90 backdrop-blur-xl z-[9999] flex flex-col items-center justify-center text-white">
        <div class="w-24 h-24 border-8 border-brand-500 border-t-white rounded-full animate-spin mb-8"></div>
        <div class="text-2xl md:text-3xl font-black tracking-tighter text-center uppercase px-4">Veriler işleniyor<br><span class="text-brand-400">& fotoğraflar yükleniyor</span></div>
        <div class="text-slate-400 mt-4 font-bold text-center text-sm max-w-sm px-6">Tarayıcıyı kapatmayın; büyük dosyalar yüklenirken birkaç saniye sürebilir.</div>
    </div>

    <form id="main-domain-form" action="{{ $domain->exists ? route('admin.domains.update', $domain) : route('admin.domains.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6 lg:gap-8">
        @csrf
        @if($domain->exists) @method('PUT') @endif

        <div class="domain-tab-panels space-y-0">
            {{-- Tab: Altyapı --}}
            <div id="domain-tab-panel-infra" class="domain-tab-panel" role="tabpanel" aria-labelledby="domain-tab-btn-infra">
            <div class="premium-card p-6 sm:p-8 border border-slate-100 shadow-soft ring-1 ring-slate-100/80 max-w-4xl mx-auto xl:mx-0">
                <div class="domain-section-head">
                    <span class="domain-step-num">1</span>
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">Cloudflare & altyapı</h2>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            @if($showPicker)
                                Hesap → API ile zone listesi → alan adı seçimi (zone arka planda bağlanır)
                            @else
                                Alan adı ve Cloudflare zone bu kayıtta değiştirilemez. Ürün, SSL ve funnel ayarlarını güncelleyebilirsiniz.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="space-y-5">
                    @if($showPicker)
                    <div class="domain-cf-account-box p-4 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                            <div class="flex items-start gap-3 min-w-0">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-orange-400 to-orange-600 text-white shadow-md shadow-orange-200/80" aria-hidden="true">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                                </span>
                                <div class="min-w-0">
                                    <label for="cloudflare_account_id" class="block text-[11px] font-black text-orange-950 uppercase tracking-wider cursor-pointer">Cloudflare hesabı</label>
                                    <p class="text-[10px] text-orange-900/70 font-medium mt-1 leading-snug">Bu domain için hangi CF hesabı kullanılacak? Boş bırakırsan sistemdeki varsayılan token kullanılır.</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.cloudflare-accounts.index') }}" class="shrink-0 inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wide text-orange-700 hover:text-orange-900 bg-white/80 hover:bg-white border border-orange-200/80 px-3 py-2 rounded-xl transition shadow-sm">
                                Hesapları yönet
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                        <select name="cloudflare_account_id" id="cloudflare_account_id" class="domain-input domain-select-chevron font-bold text-sm border-orange-100/80">
                            <option value="">Varsayılan (sistem ayarı — CLOUDFLARE_TOKEN)</option>
                            @foreach($cloudflareAccounts as $cfAcc)
                                <option value="{{ $cfAcc->id }}" {{ (string) old('cloudflare_account_id', $domain->cloudflare_account_id) === (string) $cfAcc->id ? 'selected' : '' }}>
                                    {{ $cfAcc->name }}@if(! $cfAcc->is_active) (pasif)@endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-orange-900/55 font-medium mt-2.5">Token’ın erişebildiği tüm zone’lar listelenir. Zone listeden seçilir; site adresi olarak <strong class="text-orange-900/80">kök (apex)</strong> veya <strong class="text-orange-900/80">subdomain</strong> ayrıca tanımlanır.</p>
                    </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-3">
                            <div>
                                <label for="cf_zone_trigger" class="domain-label">Cloudflare’deki alan adı</label>
                                <p class="text-[11px] text-slate-500 font-medium mt-1">Önce Cloudflare <strong>zone</strong> kökünü seçin; ardından siteyi apex mi yoksa subdomain mi olacağını belirleyin. Kayda giden tam hostname gizli alanda tutulur.</p>
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <button type="button" id="cf-onboarding-draft-btn" class="shrink-0 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wide border border-orange-300 text-orange-800 bg-white hover:bg-orange-50 transition">
                                    Cloudflare'de yeni eklemek istiyorum
                                </button>
                                <button type="button" id="cf-load-zones-btn" class="shrink-0 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wide bg-slate-900 text-white hover:bg-black transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Listeyi getir
                                </button>
                            </div>
                        </div>
                        <div id="cf-zone-picker" class="relative z-30">
                            <button type="button" id="cf_zone_trigger" class="domain-input cf-zone-trigger w-full flex items-center justify-between gap-3 text-left font-bold text-sm min-h-[2.75rem] disabled:opacity-55 disabled:cursor-not-allowed" aria-haspopup="listbox" aria-expanded="false" aria-controls="cf_zone_dropdown" disabled>
                                <span id="cf_zone_trigger_label" class="truncate min-w-0 text-slate-400 font-bold">Önce listeyi getirin…</span>
                                <svg id="cf_zone_chevron" class="w-5 h-5 shrink-0 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="cf_zone_dropdown" class="hidden absolute left-0 right-0 top-full mt-1.5 rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 overflow-hidden flex flex-col max-h-[min(22rem,calc(100vh-12rem))]">
                                <div class="relative p-2 border-b border-slate-100 bg-slate-50/80">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" aria-hidden="true">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </span>
                                    <input type="search" id="cf_zone_search" class="domain-input pl-10 w-full text-sm font-bold" placeholder="Zone ara…" autocomplete="off" disabled aria-autocomplete="list" aria-label="Zone ara">
                                </div>
                                <ul id="cf_zone_list" role="listbox" aria-label="Cloudflare zone listesi" class="list-none m-0 p-0 overflow-y-auto min-h-0 flex-1 bg-slate-50/40"></ul>
                            </div>
                        </div>
                        <p id="cf-zones-status" class="text-[10px] text-slate-400 font-semibold mt-2 min-h-[1rem]"></p>
                        <div id="cf-debug-box" class="hidden mt-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-[10px] text-red-900">
                            <p class="font-black uppercase tracking-wide">Cloudflare Request Debug</p>
                            <pre id="cf-debug-pre" class="mt-1 whitespace-pre-wrap break-all font-mono text-[10px] leading-relaxed"></pre>
                        </div>
                        <div id="cf-onboarding-result" class="hidden mt-3 rounded-xl border border-orange-200 bg-orange-50 px-3 py-2.5 text-[11px] text-orange-900">
                            <p id="cf-onboarding-result-title" class="font-black text-[10px] uppercase tracking-wide text-orange-800">Cloudflare nameserver'lar</p>
                            <p id="cf-onboarding-result-domain" class="font-mono font-bold mt-1 break-all"></p>
                            <p id="cf-onboarding-result-ns" class="font-mono mt-1 break-all"></p>
                            <p class="mt-1 text-[10px] text-orange-800/80 font-semibold">Bu NS değerlerini domain kayıtçısında tanımlayın, sonra "DNS durumu" ile kontrol edin.</p>
                        </div>
                        <input type="hidden" id="cf_zone_apex" value="" autocomplete="off">
                        <input type="hidden" name="domain_name" id="domain_name" value="{{ old('domain_name', $domain->domain_name) }}" required>
                        <input type="hidden" name="cloudflare_zone_id" id="cloudflare_zone_id" value="{{ old('cloudflare_zone_id', $domain->cloudflare_zone_id) }}" required>

                        <div id="cf-hostname-mode-wrap" class="mt-4 hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50/95 to-white p-4 sm:p-5 space-y-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white shadow-md" aria-hidden="true">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest">Site adresi</h3>
                                    <p class="text-[11px] text-slate-500 font-medium mt-0.5 leading-snug">Zone kökü DNS yönetimi içindir; ziyaretçilerin göreceği adres apex veya bu zone altında bir subdomain olabilir.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="cf-host-mode-label flex gap-3 cursor-pointer rounded-xl border-2 border-slate-200 bg-white p-4 hover:border-slate-300 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/40 has-[:checked]:shadow-sm">
                                    <input type="radio" name="cf_host_mode" id="cf_host_mode_apex" value="apex" checked class="mt-0.5 h-4 w-4 shrink-0 border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-black text-slate-900">Kök alan adı (apex)</span>
                                        <span class="block text-[11px] text-slate-500 font-medium mt-1 leading-snug">Tam olarak zone adı: <span class="font-mono text-slate-800 cf-preview-apex-inline">—</span></span>
                                    </span>
                                </label>
                                <label class="cf-host-mode-label flex gap-3 cursor-pointer rounded-xl border-2 border-slate-200 bg-white p-4 hover:border-slate-300 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/40 has-[:checked]:shadow-sm">
                                    <input type="radio" name="cf_host_mode" id="cf_host_mode_sub" value="subdomain" class="mt-0.5 h-4 w-4 shrink-0 border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-black text-slate-900">Alt alan adı (subdomain)</span>
                                        <span class="block text-[11px] text-slate-500 font-medium mt-1 leading-snug">Örn. <span class="font-mono text-slate-700">shop</span> → <span class="font-mono text-slate-700">shop.<span class="cf-preview-apex-suffix-inline">—</span></span></span>
                                    </span>
                                </label>
                            </div>

                            <div id="cf-subdomain-row" class="hidden space-y-2 rounded-xl border border-dashed border-slate-200 bg-white/80 p-4">
                                <label for="cf_subdomain_label" class="block text-[11px] font-black text-slate-600 uppercase tracking-wide">Subdomain etiketi</label>
                                <div class="flex flex-wrap items-center gap-2">
                                    <input type="text" id="cf_subdomain_label" class="domain-input font-mono font-bold text-sm flex-1 min-w-[8rem] max-w-sm" placeholder="shop, app, tr …" autocomplete="off" spellcheck="false" inputmode="latin">
                                    <span class="text-slate-400 font-bold">.</span>
                                    <span id="cf-subdomain-apex-suffix" class="inline-flex items-center font-mono text-sm font-bold text-slate-800 px-3 py-2 rounded-lg bg-slate-100 border border-slate-200">—</span>
                                </div>
                                <p class="text-[10px] text-slate-500 font-medium leading-relaxed">Küçük harf, rakam ve tire; çok seviye için nokta kullanın (<span class="font-mono">app.tr</span>). Başta/sonda tire olmamalı.</p>
                            </div>

                            <div class="rounded-xl bg-white border border-slate-200 px-3 py-2.5">
                                <span class="text-[9px] font-black uppercase tracking-wide text-slate-400">Kayda giden hostname</span>
                                <div id="cf_hostname_preview" class="mt-1 text-sm font-mono font-black text-brand-800 break-all">—</div>
                            </div>
                        </div>

                        <div id="cf-selection-summary" class="mt-3 rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5 text-[11px] text-slate-700 hidden space-y-2">
                            <div>
                                <span class="text-slate-500 font-sans font-bold text-[9px] uppercase tracking-wide">Site adresi</span>
                                <div id="cf-summary-host" class="font-mono font-bold text-slate-900 mt-0.5 break-all"></div>
                            </div>
                            <div>
                                <span class="text-slate-500 font-sans font-bold text-[9px] uppercase tracking-wide">Zone kökü</span>
                                <div id="cf-summary-zone-line" class="font-mono text-xs mt-0.5 text-slate-600 break-all"></div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5 sm:p-6 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="min-w-0">
                                <p class="domain-label mb-1">Site adresi</p>
                                <p class="font-mono text-base sm:text-lg font-black text-slate-900 break-all">{{ $domain->domain_name }}</p>
                            </div>
                            <div class="shrink-0 flex items-center gap-2">
                                <button type="button" @if(!$isCreate) onclick="confirmDomainReset()" @endif class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-wide text-red-700 bg-white border border-red-200 hover:bg-red-50 transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Adresi Sil
                                </button>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium mt-3 leading-relaxed">Bu domain için Cloudflare hesabı ve zone yalnızca yeni kayıtta tanımlanır; düzenlemede değiştirilemez. Eğer alanı değiştirmek isterseniz yukarıdaki butonla altyapıyı sıfırlayabilirsiniz.</p>
                        <input type="hidden" name="domain_name" value="{{ old('domain_name', $domain->domain_name) }}">
                        <input type="hidden" name="cloudflare_zone_id" value="{{ old('cloudflare_zone_id', $domain->cloudflare_zone_id) }}">
                    </div>

                    <!-- Marka Seçimi Kutusu -->
                    <div class="premium-card p-6 bg-white border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600 border border-brand-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-900 tracking-tight">MARKA SEÇİMİ</h4>
                                <p class="text-[10px] text-slate-500 font-medium italic">Bu alan adının bağlı olduğu ana markayı seçiniz.</p>
                            </div>
                        </div>
                        <select id="brand_id" name="brand_id" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition shadow-sm cursor-pointer appearance-none">
                            <option value="">Marka Seçilmedi</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ $domain->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @endif



                    <div class="premium-card p-6 bg-white border border-slate-200 shadow-sm">
                        <label class="domain-label" for="api_domain_id">API domain kimliği</label>
                        <input type="text" name="api_domain_id" id="api_domain_id" value="{{ old('api_domain_id', $domain->api_domain_id) }}" class="domain-input font-mono text-sm" placeholder="Örn. FUNNEL-TR-01" maxlength="255" autocomplete="off">
                        <p class="text-[10px] text-slate-500 font-medium mt-2 leading-relaxed">Harici entegrasyonda sipariş ve ürün listelerinde bu değerle eşleşir. Boş bırakılabilir; doluysa tüm sistemde benzersiz olmalıdır.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="domain-toggle-focus flex items-center gap-3 p-4 rounded-2xl border border-green-100 bg-green-50/60 cursor-pointer hover:border-green-200 transition">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $domain->is_active ?? 1) ? 'checked' : '' }} class="w-5 h-5 rounded border-green-200 text-green-600 focus:ring-green-500">
                            <span class="font-bold text-slate-800 text-xs">Site yayında</span>
                        </label>
                    </div>

                    <div class="rounded-2xl border border-orange-100 bg-gradient-to-br from-orange-50/90 to-amber-50/50 p-5 shadow-sm">
                        <div class="flex items-start gap-3 mb-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-orange-400 to-orange-600 text-white shadow-md shadow-orange-200">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                            </span>
                            <div>
                                <h3 class="text-xs font-black text-orange-900 uppercase tracking-widest">Cloudflare sihirbazı</h3>
                                <p class="text-[11px] text-orange-800/80 font-medium mt-0.5">Zone oluşturma, NS kontrolü ve DNS + SSL tamamlama.</p>
                            </div>
                        </div>
                        @if($domain->exists && $domain->cloudflare_zone_id)
                            <div class="flex flex-col sm:flex-row flex-wrap items-start gap-2">
                                <button type="button" onclick="checkCloudflareStatus()" class="inline-flex justify-center px-5 py-3 rounded-xl text-xs font-black border border-orange-200 bg-white text-orange-800 hover:bg-orange-50 transition">DNS durumu</button>
                                <button type="button" onclick="finalizeCloudflareDNS()" class="inline-flex justify-center px-5 py-3 rounded-xl text-xs font-black bg-slate-900 text-white hover:bg-black transition">Kayıtları oluştur & SSL</button>
                                <button type="button" onclick="createCloudflareDNS()" class="inline-flex justify-center px-5 py-3 rounded-xl text-[10px] font-black uppercase tracking-wide border border-orange-300 text-orange-800 bg-white hover:bg-orange-50 transition">DNS (A) kaydı</button>
                            </div>
                            <p id="cf-status-label" class="mt-3 text-[11px] font-semibold text-slate-600">Zone kayıtlı — durum ve DNS için butonları kullanın.</p>
                            <p class="text-[10px] text-orange-900/75 font-medium mt-2 leading-relaxed">Kayıt veya güncelleme kaydettiğinizde (form gönderimi) Cloudflare’de eksik A kayıtları ve SSL Full <strong>otomatik</strong> denenir — subdomain seçtiyseniz ilgili A kaydı da buna dahildir (wildcard aynı IP ise eklenmez). Aşağıdaki butonlar manuel tekrar için. DNS ve SSL zaten doğruysa API tekrar kayıt oluşturmaz.</p>
                        @else
                            <p class="text-[11px] text-orange-900/80 font-medium leading-relaxed">Alan adını yukarıdan <strong>Cloudflare zone listesinden</strong> seçin (zone Cloudflare’de önceden tanımlı olmalı). Kayıt sonrası bu bölümden DNS / SSL adımlarını kullanırsınız.</p>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50/90 to-blue-50/60 p-5 shadow-sm">
                        <div class="flex items-start gap-3 mb-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-md shadow-sky-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4M3 12h18"></path></svg>
                            </span>
                            <div>
                                <h3 class="text-xs font-black text-sky-900 uppercase tracking-widest">BunnyCDN sihirbazı</h3>
                                <p class="text-[11px] text-sky-900/75 font-medium mt-0.5">Aktif Bunny kaydını kullanarak Pull Zone ve domain eşleşmesini otomatik oluşturur.</p>
                            </div>
                        </div>
                        <input type="hidden" name="bunny_pullzone_id" id="bunny_pullzone_id" value="{{ old('bunny_pullzone_id', $domain->bunny_pullzone_id) }}">
                        <input type="hidden" name="bunny_hostname" id="bunny_hostname" value="{{ old('bunny_hostname', $domain->bunny_hostname) }}">
                        @if($domain->exists)
                            <div class="flex flex-col sm:flex-row flex-wrap items-start gap-2">
                                <button type="button" onclick="provisionBunnyCdn()" class="inline-flex justify-center px-5 py-3 rounded-xl text-xs font-black bg-slate-900 text-white hover:bg-black transition">Bunny otomatik kurulum</button>
                                <button type="button" onclick="checkBunnyStatus()" class="inline-flex justify-center px-5 py-3 rounded-xl text-xs font-black border border-sky-200 bg-white text-sky-800 hover:bg-sky-50 transition">Bunny durum</button>
                            </div>
                            <p id="bunny-status-label" class="mt-3 text-[11px] font-semibold text-slate-600">
                                @if($domain->bunny_pullzone_id)
                                    Pull Zone bağlı (ID: {{ $domain->bunny_pullzone_id }}).
                                @else
                                    Bu domain için Bunny kurulumu henüz yapılmamış.
                                @endif
                            </p>
                        @else
                            <div class="flex flex-col sm:flex-row flex-wrap items-start gap-2">
                                <button type="button" onclick="provisionBunnyCdnDraft()" class="inline-flex justify-center px-5 py-3 rounded-xl text-xs font-black bg-slate-900 text-white hover:bg-black transition">Bunny kurulumunu simdi yap</button>
                            </div>
                            <p class="text-[11px] text-sky-900/80 font-medium leading-relaxed mt-3">Alan adini secip bu butona bastiginizda Pull Zone olusturulur; sonra formu kaydedince domain kaydina otomatik yazilir.</p>
                        @endif
                    </div>
                </div>
            </div>
            </div>

            {{-- Tab: Pazarlama --}}
            <div id="domain-tab-panel-marketing" class="domain-tab-panel hidden" role="tabpanel" aria-labelledby="domain-tab-btn-marketing">
            <div class="premium-card p-6 sm:p-8 border border-slate-100 shadow-soft ring-1 ring-slate-100/80 max-w-4xl mx-auto xl:mx-0">
                <div class="domain-section-head">
                    <span class="domain-step-num" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">2</span>
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">Pazarlama & görünüm</h2>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Sayaç, WhatsApp, SEO ve tema rengi</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="domain-label">Site İkonu (Favicon)</label>
                            <div class="flex items-center gap-3">
                                @if($domain->config && $domain->config->favicon_path)
                                    <div class="w-12 h-12 rounded-lg border border-slate-200 bg-white p-1 flex items-center justify-center">
                                        <img src="{{ $domain->config->favicon_url }}" class="max-h-full max-w-full">
                                    </div>
                                @endif
                                <input type="file" name="favicon" accept="image/x-icon,image/png,image/gif" class="domain-input offer-file-input">
                            </div>
                            <p class="text-[9px] text-slate-400 mt-1">.ico veya .png önerilir (32x32px)</p>
                        </div>
                        <div>
                            <label class="domain-label">Önizleme Görseli (Facebook/WhatsApp)</label>
                            <div class="flex items-center gap-3">
                                @if($domain->config && $domain->config->og_image_path)
                                    <div class="w-12 h-12 rounded-lg border border-slate-200 bg-white p-1 flex items-center justify-center">
                                        <img src="{{ $domain->config->og_image_url }}" class="max-h-full max-w-full object-cover">
                                    </div>
                                @endif
                                <input type="file" name="og_image" accept="image/*" class="domain-input offer-file-input">
                            </div>
                            <p class="text-[9px] text-slate-400 mt-1">Paylaşımlarda gözükecek görsel (1200x630px önerilir)</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="domain-label" for="countdown_minutes">Sayaç (dakika)</label>
                            <input type="number" name="countdown_minutes" id="countdown_minutes" value="{{ old('countdown_minutes', $domain->config->countdown_minutes ?? 15) }}" min="1" class="domain-input font-black text-xl text-center tabular-nums">
                        </div>
                        <div>
                            <label class="domain-label" for="stock_countdown_start">Stok Geri Sayım Başlangıç</label>
                            <input type="number" name="stock_countdown_start" id="stock_countdown_start" value="{{ old('stock_countdown_start', $domain->config->stock_countdown_start ?? 719) }}" min="1" class="domain-input font-black text-xl text-center tabular-nums">
                        </div>
                        <div>
                            <label class="domain-label" for="whatsapp_number">WhatsApp</label>
                            <input type="text" name="whatsapp_number" id="whatsapp_number" value="{{ old('whatsapp_number', $domain->config->whatsapp_number ?? '') }}" placeholder="905xxxxxxxxx" class="domain-input font-bold placeholder:text-slate-400 placeholder:font-medium">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="domain-label">Marka Rengi (Ana)</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="primary_color" value="{{ old('primary_color', $domain->config->primary_color ?? '#16a34a') }}" class="h-12 w-16 rounded-xl cursor-pointer border-2 border-slate-200 p-1 bg-white">
                                <p class="text-[10px] text-slate-500 font-medium">Butonlar ve vurgular.</p>
                            </div>
                        </div>
                        <div>
                            <label class="domain-label">Marka Rengi (Koyu)</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="secondary_color" value="{{ old('secondary_color', $domain->config->secondary_color ?? '#14532d') }}" class="h-12 w-16 rounded-xl cursor-pointer border-2 border-slate-200 p-1 bg-white">
                                <p class="text-[10px] text-slate-500 font-medium">Hover ve derinlik.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="domain-label" for="seo_title">SEO Başlığı</label>
                        <input type="text" name="seo_title" id="seo_title" value="{{ old('seo_title', $domain->config->seo_title ?? '') }}" placeholder="Google'da görünecek başlık" class="domain-input">
                    </div>

                    <div>
                        <label class="domain-label" for="seo_description">SEO Açıklaması</label>
                        <textarea name="seo_description" id="seo_description" rows="2" class="domain-input" placeholder="Google arama sonuçlarındaki kısa açıklama">{{ old('seo_description', $domain->config->seo_description ?? '') }}</textarea>
                    </div>

                    <div class="pt-6 border-t border-slate-100">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Takip & Doğrulama Kodları</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="domain-label" for="facebook_pixel_id">Facebook Pixel ID</label>
                                <input type="text" name="facebook_pixel_id" id="facebook_pixel_id" value="{{ old('facebook_pixel_id', $domain->config->facebook_pixel_id ?? '') }}" placeholder="Örn: 1234567890" class="domain-input font-mono text-sm">
                                <p class="text-[10px] text-slate-500 font-bold mt-1">ID girdiğinizde <span class="text-brand-600">ViewContent, AddToCart, InitiateCheckout</span> ve <span class="text-brand-600">Purchase</span> (Tutar & TRY) eventleri otomatik takip edilir.</p>
                            </div>
                            <div>
                                <label class="domain-label" for="google_analytics_id">Google Analytics ID (G-XXXXX)</label>
                                <input type="text" name="google_analytics_id" id="google_analytics_id" value="{{ old('google_analytics_id', $domain->config->google_analytics_id ?? '') }}" placeholder="Örn: G-XXXXXXXXXX" class="domain-input font-mono text-sm">
                            </div>
                            <div>
                                <label class="domain-label" for="google_verification_code">Google Search Console Kodu</label>
                                <input type="text" name="google_verification_code" id="google_verification_code" value="{{ old('google_verification_code', $domain->config->google_verification_code ?? '') }}" placeholder="Verification content string" class="domain-input font-mono text-sm">
                            </div>
                            <div>
                                <label class="domain-label" for="tiktok_pixel_id">TikTok Pixel ID</label>
                                <input type="text" name="tiktok_pixel_id" id="tiktok_pixel_id" value="{{ old('tiktok_pixel_id', $domain->config->tiktok_pixel_id ?? '') }}" placeholder="Örn: C1234567890" class="domain-input font-mono text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100">
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Özel Scriptler (Advanced)</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="domain-label" for="header_scripts">Header Scriptleri (Head Sonu)</label>
                                <textarea name="header_scripts" id="header_scripts" rows="3" class="domain-input font-mono text-[10px]" placeholder="<script>...</script>">{{ old('header_scripts', $domain->config->header_scripts ?? '') }}</textarea>
                                <p class="text-[10px] text-slate-400 mt-1">Google Tag Manager, Pixel Base Code vb. buraya eklenir.</p>
                            </div>
                            <div>
                                <label class="domain-label" for="body_scripts">Body Scriptleri (Body Başı)</label>
                                <textarea name="body_scripts" id="body_scripts" rows="3" class="domain-input font-mono text-[10px]" placeholder="<noscript>...</noscript>">{{ old('body_scripts', $domain->config->body_scripts ?? '') }}</textarea>
                                <p class="text-[10px] text-slate-400 mt-1">GTM NoScript vb. buraya eklenir.</p>
                            </div>
                            <div>
                                <label class="domain-label" for="footer_scripts">Footer Scriptleri (Body Sonu)</label>
                                <textarea name="footer_scripts" id="footer_scripts" rows="3" class="domain-input font-mono text-[10px]" placeholder="Canlı destek, ısı haritası vb.">{{ old('footer_scripts', $domain->config->footer_scripts ?? '') }}</textarea>
                            </div>
                            <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100">
                                <label class="domain-label text-indigo-900" for="success_scripts">Teşekkür Sayfası (Dönüşüm) Scriptleri</label>
                                <textarea name="success_scripts" id="success_scripts" rows="4" class="domain-input border-indigo-200 font-mono text-[11px]" placeholder="Purchase event kodları buraya...">{{ old('success_scripts', $domain->config->success_scripts ?? '') }}</textarea>
                                <p class="text-[10px] text-indigo-600 font-bold mt-2">Bu kodlar sadece başarılı sipariş sonrası "Teşekkürler" sayfasında çalışır. Reklam dönüşümleri için burayı kullanın.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            {{-- Tab: Ödeme --}}
            <div id="domain-tab-panel-payment" class="domain-tab-panel hidden" role="tabpanel" aria-labelledby="domain-tab-btn-payment">
            <div class="premium-card p-6 sm:p-8 border border-slate-100 shadow-soft ring-1 ring-slate-100/80 max-w-4xl mx-auto xl:mx-0">
                <div class="domain-section-head">
                    <span class="domain-step-num" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">2.5</span>
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">Ödeme yöntemleri</h2>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Sitede hangi ödeme yöntemlerinin aktif olacağını belirleyin.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-blue-600 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-blue-900 uppercase tracking-tight">Kredi Kartı ile Tahsilat</h3>
                                <p class="text-[10px] text-blue-700 font-medium italic">Sanal POS üzerinden online ödeme almayı sağlar.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="domain-toggle-focus flex items-center gap-3 p-4 rounded-2xl border border-blue-200 bg-white cursor-pointer hover:border-blue-300 transition">
                                <input type="hidden" name="allow_credit_card" value="0">
                                <input type="checkbox" name="allow_credit_card" id="allow_credit_card" value="1" {{ old('allow_credit_card', $domain->config->allow_credit_card ?? 0) ? 'checked' : '' }} class="w-5 h-5 rounded border-blue-200 text-blue-600 focus:ring-blue-500" onchange="togglePaymentProviderSelect(this.checked)">
                                <span class="font-bold text-slate-800 text-xs">Kredi kartı ödeme seçeneğini aktifleştir</span>
                            </label>

                            <div id="payment-provider-wrapper" class="{{ old('allow_credit_card', $domain->config->allow_credit_card ?? 0) ? '' : 'hidden' }}">
                                <label class="domain-label" for="payment_provider_id">Kullanılacak Sanal POS / Banka</label>
                                <select name="payment_provider_id" id="payment_provider_id" class="domain-input domain-select-chevron font-bold text-sm">
                                    <option value="">Seçiniz...</option>
                                    @foreach($paymentProviders as $provider)
                                        <option value="{{ $provider->id }}" {{ (string) old('payment_provider_id', $domain->config->payment_provider_id ?? '') === (string) $provider->id ? 'selected' : '' }}>
                                            {{ $provider->name }} ({{ strtoupper($provider->provider_type) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-slate-500 font-medium mt-2">Ödeme aracısı kuruluşu veya bankayı seçin. Ayarlar global "Ödeme Ayarları" bölümünden yapılır.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 opacity-60">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Kapıda Ödeme</h3>
                                <p class="text-[10px] text-slate-500 font-medium italic">Şu anda varsayılan ve zorunludur.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <script>
                function togglePaymentProviderSelect(show) {
                    const wrapper = document.getElementById('payment-provider-wrapper');
                    if (show) {
                        wrapper.classList.remove('hidden');
                    } else {
                        wrapper.classList.add('hidden');
                    }
                }
            </script>

            {{-- Tab: Paketler --}}
            @php
                $offerRows = old('offers');
                if ($offerRows === null) {
                    $offerRows = $domain->offers->isNotEmpty()
                        ? $domain->offers->map(function($o) {
                            $data = $o->toArray();
                            $data['items'] = $o->items->toArray();
                            $data['affiliate_commission'] = $o->affiliateCommission ? $o->affiliateCommission->toArray() : null;
                            return $data;
                        })->toArray()
                        : [['offer_name' => '', 'quantity' => 1, 'price' => '', 'api_offer_id' => '', 'id' => '', 'is_popular' => false, 'offer_image' => null, 'items' => [], 'affiliate_commission' => null]];
                }
                if ($offerRows === []) {
                    $offerRows = [['offer_name' => '', 'quantity' => 1, 'price' => '', 'api_offer_id' => '', 'id' => '', 'is_popular' => false, 'offer_image' => null, 'items' => [], 'affiliate_commission' => null]];
                }
            @endphp
            <div id="domain-tab-panel-offers" class="domain-tab-panel hidden" role="tabpanel" aria-labelledby="domain-tab-btn-offers">
                <div class="premium-card p-6 sm:p-8 border border-slate-100 shadow-soft ring-1 ring-slate-100/80 w-full">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6 sm:mb-8">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="domain-step-num shrink-0">3</span>
                            <div>
                                <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Satış paketleri</h2>
                                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1 leading-relaxed">Kampanya metni, fiyat ve adet; isteğe bağlı küçük görsel. Öne çıkan paketi işaretleyebilirsiniz.</p>
                            </div>
                        </div>
                        <button type="button" onclick="addOfferRow()" class="inline-flex items-center justify-center gap-2 shrink-0 px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-wide bg-slate-900 text-white hover:bg-black shadow-md transition w-full sm:w-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Paket ekle
                        </button>
                    </div>
                    <div id="offers-container" class="space-y-4">
                        @foreach($offerRows as $i => $off)
                        <div class="offer-row offer-pack-card p-4 sm:p-5">
                            <input type="hidden" name="offers[{{ $i }}][id]" value="{{ $off['id'] ?? '' }}">
                            <input type="hidden" name="offers[{{ $i }}][offer_image]" value="{{ $off['offer_image'] ?? '' }}">
                            <input type="hidden" name="offers[{{ $i }}][active_image]" value="{{ $off['active_image'] ?? '' }}">
                            <div class="flex flex-wrap items-start justify-between gap-3 mb-4 pb-4 border-b border-slate-100">
                                <span class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-wide text-slate-400">
                                    <span class="offer-pack-index flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-700 text-xs font-black">{{ $i + 1 }}</span>
                                    Paket
                                </span>
                                <button type="button" onclick="removeOfferRow(this)" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-red-600 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 transition" title="Bu paketi listeden kaldır">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Kaldır
                                </button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="domain-label" for="offer_api_id_{{ $i }}">API paket kimliği (harici)</label>
                                    <input type="text" id="offer_api_id_{{ $i }}" name="offers[{{ $i }}][api_offer_id]" value="{{ old('offers.'.$i.'.api_offer_id', $off['api_offer_id'] ?? '') }}" class="domain-input font-mono text-xs" placeholder="örn. PAKET-TEKLI, PAKET-3LU" maxlength="255" autocomplete="off">
                                    <p class="text-[9px] text-slate-400 font-medium mt-1">Ortağın sipariş gövdesinde aynı değeri <code class="text-[10px]">api_offer_id</code> olarak yazması içindir; böylece doğrudan bu paket seçilir. Kaç adet (ör. 3’lü) ve tutar zaten paket içeriğinde tanımlıdır — kod boş bırakılırsa entegrasyon siparişteki tutar ve adet ile paketi bulur. İsteğe bağlı.</p>
                                </div>
                                <div>
                                    <label class="domain-label" for="offer_name_{{ $i }}">Paket adı</label>
                                    <input type="text" id="offer_name_{{ $i }}" name="offers[{{ $i }}][offer_name]" value="{{ $off['offer_name'] ?? '' }}" class="domain-input font-bold" placeholder="Örn. 3 Al 2 Öde, Premium paket">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                                    <div class="sm:col-span-12">
                                        <label class="domain-label">Paket Görselleri (Normal / Seçili)</label>
                                        <div class="flex flex-col gap-3">
                                            <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                                                 @if(! empty($off['offer_image']))
                                                     <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                                                         <img src="{{ \App\Models\Setting::mediaUrl('uploads/offers/'.$off['offer_image'], null, $domainBunnyBase) }}" alt="" class="max-h-full max-w-full object-contain">
                                                     </div>
                                                 @else
                                                     <div class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/80 text-slate-300">
                                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                     </div>
                                                 @endif
                                                 <input type="file" id="offer_img_{{ $i }}" name="offers[{{ $i }}][image]" accept="image/*" class="domain-input offer-file-input text-[10px] font-medium text-slate-600 w-full min-w-0 py-2" title="Normal Görsel">
                                             </div>
                                             <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                                                 @if(! empty($off['active_image']))
                                                     <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 p-1">
                                                         <img src="{{ \App\Models\Setting::mediaUrl('uploads/offers/'.$off['active_image'], null, $domainBunnyBase) }}" alt="" class="max-h-full max-w-full object-contain">
                                                     </div>
                                                 @else
                                                     <div class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-dashed border-blue-200 bg-blue-50/80 text-blue-300">
                                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                     </div>
                                                 @endif
                                                 <input type="file" id="offer_active_img_{{ $i }}" name="offers[{{ $i }}][active_image]" accept="image/*" class="domain-input offer-file-input text-[10px] font-medium text-blue-600 w-full min-w-0 py-2 border-blue-100" title="Seçili Durum Görseli">
                                             </div>
                                        </div>
                                        <p class="text-[9px] text-slate-400 font-medium mt-1.5">İsteğe bağlı: Paket seçildiğinde değişecek ikinci görseli yükleyin.</p>
                                    </div>
                                    </div>
                                </div>

                                {{-- Paket İçeriği (Yeni) --}}
                                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Paket İçeriği</h4>
                                        <button type="button" onclick="addOfferItemRow({{ $i }})" class="text-[10px] font-bold text-brand-600 hover:text-brand-700 bg-white border border-brand-200 px-2 py-1 rounded-lg transition">Ürün Ekle</button>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 mb-1 text-[9px] font-black uppercase tracking-widest text-slate-400">
                                        <div class="flex-1">Ürün Seçimi</div>
                                        <div class="w-20 text-center">Adet</div>
                                        <div class="w-32 text-center">Birim Fiyat (₺)</div>
                                        <div class="w-8"></div>
                                    </div>
                                    <div id="offer-items-container-{{ $i }}" class="space-y-2">
                                        @php $items = $off['items'] ?? []; @endphp
                                        @forelse($items as $j => $item)
                                            <div class="flex items-center gap-2 offer-item-row p-1 bg-white rounded-xl border border-slate-100 shadow-sm">
                                                <input type="hidden" name="offers[{{ $i }}][items][{{ $j }}][id]" value="{{ $item['id'] ?? '' }}">
                                                <select name="offers[{{ $i }}][items][{{ $j }}][product_id]" class="domain-input domain-select-chevron offer-item-input compact-select font-bold flex-1">
                                                    @foreach($products as $p)
                                                        <option value="{{ $p->id }}" {{ $item['product_id'] == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="w-20 shrink-0">
                                                    <input type="number" name="offers[{{ $i }}][items][{{ $j }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" min="1" class="domain-input text-xs font-black text-center py-2">
                                                </div>
                                                <div class="w-32 shrink-0">
                                                    <input type="number" step="0.01" name="offers[{{ $i }}][items][{{ $j }}][price]" value="{{ $item['price'] ?? 0 }}" class="domain-input text-xs font-black text-center py-2">
                                                </div>
                                                <button type="button" onclick="this.closest('.offer-item-row').remove()" class="text-red-400 hover:text-red-600 p-1.5 hover:bg-red-50 rounded-lg transition shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        @empty
                                            <p class="text-[10px] text-slate-400 italic text-center py-2 empty-msg">Paket içeriği tanımlanmadı. (Varsayılan ana ürün kullanılır)</p>
                                        @endforelse
                                    </div>
                                </div>

                                <label class="domain-toggle-focus flex items-center gap-3 p-4 rounded-2xl border border-amber-100 bg-amber-50/50 cursor-pointer hover:border-amber-200 transition max-w-md">
                                    <input type="checkbox" name="offers[{{ $i }}][is_popular]" value="1" {{ ! empty($off['is_popular']) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500 shrink-0">
                                    <span class="min-w-0">
                                        <span class="block font-black text-slate-900 text-xs uppercase tracking-wide">Popüler paket</span>
                                        <span class="block text-[11px] text-slate-600 font-medium mt-0.5">Bu paket funnel’da vurgulu gösterilir.</span>
                                    </span>
                                </label>

                                @php
                                    $comm = $off['affiliate_commission'] ?? null;
                                    $commActive = $comm ? (bool)$comm['is_affiliate_active'] : false;
                                    $commType = $comm['commission_type'] ?? 'fixed';
                                    $commAmount = $comm['commission_amount'] ?? 0.00;
                                    $commRate = $comm['commission_rate'] ?? 0.00;
                                    $commDesc = $comm['affiliate_description'] ?? '';
                                @endphp
                                <div class="affiliate-commission-container bg-emerald-50/30 rounded-xl p-4 border border-emerald-100/80 mt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-[10px] font-black uppercase tracking-widest text-emerald-800 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Paket Affiliate Komisyon Ayarı
                                        </h4>
                                        <label class="flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox" name="offers[{{ $i }}][affiliate_active]" value="1" {{ $commActive ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-[11px] font-black text-emerald-900 uppercase">Bu Pakete Komisyon Ver</span>
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Komisyon Türü</label>
                                            <select name="offers[{{ $i }}][commission_type]" onchange="toggleCommissionFields(this)" class="domain-input compact-select text-xs font-bold">
                                                <option value="fixed" {{ $commType === 'fixed' ? 'selected' : '' }}>Sabit Tutar (₺)</option>
                                                <option value="percentage" {{ $commType === 'percentage' ? 'selected' : '' }}>Yüzde (%)</option>
                                            </select>
                                        </div>
                                        <div class="comm-amount-wrapper {{ $commType === 'percentage' ? 'hidden' : '' }}">
                                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Sabit Komisyon Tutarı (₺)</label>
                                            <input type="number" step="0.01" name="offers[{{ $i }}][commission_amount]" value="{{ $commAmount }}" class="domain-input text-xs font-black py-2">
                                        </div>
                                        <div class="comm-rate-wrapper {{ $commType === 'fixed' ? 'hidden' : '' }}">
                                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Komisyon Oranı (%)</label>
                                            <input type="number" step="0.01" name="offers[{{ $i }}][commission_rate]" value="{{ $commRate }}" class="domain-input text-xs font-black py-2">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Özel Açıklama (Opsiyonel)</label>
                                            <input type="text" name="offers[{{ $i }}][affiliate_description]" value="{{ $commDesc }}" class="domain-input text-xs py-2" placeholder="Örn: 200 TL net kazanç!">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Tab: Galeri --}}
            <div id="domain-tab-panel-gallery" class="domain-tab-panel hidden" role="tabpanel" aria-labelledby="domain-tab-btn-gallery">
        <div class="premium-card p-6 sm:p-10 bg-gradient-to-b from-slate-50 to-white border border-slate-200/90 shadow-soft">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">
                <div class="flex items-start gap-3 max-w-2xl">
                    <span class="domain-step-num shrink-0" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">4</span>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Tanıtım galerisi</h2>
                        <p class="text-sm text-slate-500 font-medium mt-1 leading-relaxed">Görseller dikey akışta üst üste dizilir. Sürükleyerek sırala; görsele tıklayınca link veya video açılabilir.</p>
                    </div>
                </div>
                <div class="relative shrink-0 w-full lg:w-auto">
                    <button type="button" class="w-full lg:w-auto pointer-events-none inline-flex items-center justify-center gap-2 bg-brand-600 text-white font-black px-8 py-4 rounded-2xl shadow-lg shadow-brand-600/25 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Fotoğraf seç
                    </button>
                    <input type="file" name="gallery_files[]" multiple accept="image/*" onchange="previewImages(this)" class="absolute inset-0 cursor-pointer opacity-0 w-full h-full" aria-label="Galeri görselleri yükle">
                </div>
            </div>

            <div id="sortable-gallery" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4 min-h-[160px] rounded-[28px] border-2 border-dashed border-slate-200 bg-white p-4 sm:p-6">
                @forelse($domain->gallery as $img)
                    <div class="gallery-item group relative aspect-[3/4] rounded-xl border-2 border-slate-100 overflow-hidden shadow-sm hover:shadow-md hover:border-brand-200/80 transition-all" data-id="{{ $img->id }}">
                        <img src="{{ $img->image_url }}" alt="" class="w-full h-full object-cover cursor-move">
                        <div class="absolute top-0 left-0 right-0 bg-green-600/90 text-white text-[9px] p-2 truncate pointer-events-none font-black opacity-95 uppercase tracking-wide">{{ $img->original_name ?? $img->image_path }}</div>
                        <div class="absolute inset-x-0 bottom-0 bg-black/65 p-2 flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" onclick="editImageProps(this)" class="bg-blue-600 p-2 rounded-lg text-white hover:bg-blue-700 transition" title="Ayarlar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                            <button type="button" onclick="deleteGalleryItem(this)" class="bg-red-600 p-2 rounded-lg text-white hover:bg-red-700 transition" title="Sil"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        </div>
                        <div class="hidden-props hidden absolute inset-0 bg-white p-3 z-10 overflow-y-auto shadow-xl">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] font-black uppercase text-slate-400">Görsel ayarları</span>
                                <button type="button" onclick="editImageProps(this)" class="text-red-500 font-bold text-sm">✕</button>
                            </div>
                            <input type="text" name="existing_gallery[{{$img->id}}][link]" value="{{ $img->link_target }}" placeholder="Link (#siparis veya URL)" class="w-full mb-2 bg-slate-50 border border-slate-200 p-2 rounded-lg text-xs">
                            <input type="text" name="existing_gallery[{{$img->id}}][video]" value="{{ $img->video_url }}" placeholder="Video URL (YouTube vb.)" class="w-full bg-slate-50 border border-slate-200 p-2 rounded-lg text-xs">
                            <input type="hidden" name="existing_gallery[{{$img->id}}][sort]" value="{{ $img->sort_order }}" class="sort-order-val">
                            <input type="checkbox" name="existing_gallery[{{$img->id}}][delete]" value="1" class="delete-flag hidden">
                        </div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 px-4 text-center text-slate-400">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <p class="font-bold text-slate-600">Henüz görsel yok</p>
                        <p class="text-sm mt-1 max-w-sm">Yukarıdaki alana tıklayarak JPG/PNG yükleyin. Kaydettikten sonra sıralamayı sürükleyerek değiştirebilirsiniz.</p>
                    </div>
                @endforelse
                <div id="new-uploads-preview" class="contents"></div>
            </div>
        </div>
    </div>

        {{-- Tab: Upsell --}}
        @php
            $upsellRows = old('upsells');
            if ($upsellRows === null) {
                $upsellRows = $domain->upsellOffers->isNotEmpty()
                    ? $domain->upsellOffers->toArray()
                    : [];
            }
        @endphp
        <div id="domain-tab-panel-upsell" class="domain-tab-panel hidden" role="tabpanel" aria-labelledby="domain-tab-btn-upsell">
            <div class="premium-card p-6 sm:p-8 border border-slate-100 shadow-soft ring-1 ring-slate-100/80 max-w-4xl mx-auto xl:mx-0">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6 sm:mb-8">
                    <div class="flex items-start gap-3 min-w-0">
                        <span class="domain-step-num shrink-0" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">5</span>
                        <div>
                            <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Upsell teklifleri</h2>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1 leading-relaxed">Sipariş sonrası veya operatör ekranında gösterilecek ek teklifler. Ciro artışı için kullanın.</p>
                        </div>
                    </div>
                    <button type="button" onclick="addUpsellRow()" class="inline-flex items-center justify-center gap-2 shrink-0 px-5 py-3 rounded-2xl text-xs font-black uppercase tracking-wide bg-orange-600 text-white hover:bg-orange-700 shadow-md transition w-full sm:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Teklif ekle
                    </button>
                </div>

                <div id="upsells-container" class="space-y-4">
                    @foreach($upsellRows as $i => $up)
                    <div class="upsell-row offer-pack-card p-4 sm:p-5 border-orange-100 bg-orange-50/10">
                        <input type="hidden" name="upsells[{{ $i }}][id]" value="{{ $up['id'] ?? '' }}">
                        <div class="flex flex-wrap items-start justify-between gap-3 mb-4 pb-4 border-b border-orange-100">
                            <span class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-wide text-orange-600">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 text-orange-700 text-xs font-black">{{ $i + 1 }}</span>
                                Upsell Teklifi
                            </span>
                            <button type="button" onclick="removeUpsellRow(this)" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-red-600 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Kaldır
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="domain-label">Teklif Adı (Admin)</label>
                                    <input type="text" name="upsells[{{ $i }}][name]" value="{{ $up['name'] ?? '' }}" class="domain-input font-bold" placeholder="Örn. +1 Adet Daha">
                                </div>
                                <div>
                                    <label class="domain-label">Başlık (Müşteri)</label>
                                    <input type="text" name="upsells[{{ $i }}][title]" value="{{ $up['title'] ?? '' }}" class="domain-input font-black" placeholder="Örn. Bu Fırsatı Kaçırma!">
                                </div>
                            </div>
                            <div>
                                <label class="domain-label">Açıklama</label>
                                <textarea name="upsells[{{ $i }}][description]" rows="2" class="domain-input text-xs">{{ $up['description'] ?? '' }}</textarea>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="domain-label">Teklif Türü</label>
                                    <select name="upsells[{{ $i }}][offer_type]" class="domain-input font-bold text-xs">
                                        <option value="add_same_product" {{ ($up['offer_type'] ?? '') == 'add_same_product' ? 'selected' : '' }}>Aynı üründen ek adet</option>
                                        <option value="upgrade_package" {{ ($up['offer_type'] ?? '') == 'upgrade_package' ? 'selected' : '' }}>Paket yükseltme</option>
                                        <option value="complementary_product" {{ ($up['offer_type'] ?? '') == 'complementary_product' ? 'selected' : '' }}>Tamamlayıcı ürün</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="domain-label">Hedef Ürün</label>
                                    <select name="upsells[{{ $i }}][target_product_id]" class="domain-input font-bold text-xs">
                                        <option value="">— Seçin —</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" {{ ($up['target_product_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="domain-label">Hedef Paket (Opsiyonel)</label>
                                    <select name="upsells[{{ $i }}][target_package_id]" class="domain-input font-bold text-xs">
                                        <option value="">— Seçin —</option>
                                        @foreach($domain->offers as $doff)
                                            <option value="{{ $doff->id }}" {{ ($up['target_package_id'] ?? '') == $doff->id ? 'selected' : '' }}>{{ $doff->offer_name }} (₺{{ number_format($doff->price, 2, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="domain-label">Normal Fiyat</label>
                                    <input type="number" step="0.01" name="upsells[{{ $i }}][original_price]" value="{{ $up['original_price'] ?? '' }}" class="domain-input tabular-nums" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="domain-label">İndirimli Fiyat (Satış)</label>
                                    <input type="number" step="0.01" name="upsells[{{ $i }}][discount_price]" value="{{ $up['discount_price'] ?? '' }}" class="domain-input font-black text-orange-700 tabular-nums" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="domain-label">Gösterim Zamanı</label>
                                    <select name="upsells[{{ $i }}][display_timing]" class="domain-input font-bold text-xs">
                                        <option value="thank_you" {{ ($up['display_timing'] ?? '') == 'thank_you' ? 'selected' : '' }}>Sadece Teşekkür Sayfası</option>
                                        <option value="operator" {{ ($up['display_timing'] ?? '') == 'operator' ? 'selected' : '' }}>Sadece Operatör Ekranı</option>
                                        <option value="both" {{ ($up['display_timing'] ?? 'both') == 'both' ? 'selected' : '' }}>İkisi Birden</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="upsells[{{ $i }}][is_active]" value="1" {{ ($up['is_active'] ?? true) ? 'checked' : '' }} class="w-4 h-4 rounded text-orange-600 focus:ring-orange-500">
                                    <span class="text-xs font-bold text-slate-700">Aktif</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if(count($upsellRows) == 0)
                <div id="upsells-empty-state" class="flex flex-col items-center justify-center py-12 px-4 text-center border-2 border-dashed border-slate-100 rounded-2xl mt-4">
                    <div class="w-16 h-16 rounded-2xl bg-orange-50 flex items-center justify-center mb-4 text-orange-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <p class="font-bold text-slate-600">Henüz upsell teklifi yok</p>
                    <p class="text-xs text-slate-400 mt-1">"Teklif ekle" butonuna basarak ilk fırsatınızı oluşturun.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

            {{-- Tab: Affiliate --}}
            <div id="domain-tab-panel-affiliate" class="domain-tab-panel hidden" role="tabpanel" aria-labelledby="domain-tab-btn-affiliate">
                <div class="premium-card p-6 sm:p-8 border border-slate-100 shadow-soft ring-1 ring-slate-100/80 max-w-4xl mx-auto xl:mx-0">
                    <div class="domain-section-head">
                <span class="domain-step-num shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Affiliate (Ortaklık) Yönetimi</h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Bu satış domainine özel affiliate programı ayarları</p>
                </div>
            </div>

            <div class="space-y-6 mt-6">
                <!-- Aktiflik & Başlık -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="affiliate[is_affiliate_active]" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-5 h-5" {{ optional($domain->affiliateSetting)->is_affiliate_active ? 'checked' : '' }}>
                            <div>
                                <span class="text-sm font-black text-slate-900">Affiliate Satışlarını Etkinleştir</span>
                                <p class="text-[10px] text-slate-400 font-semibold mt-0.5">Bu domain için affiliate linkleri ve takipleri aktif edilir.</p>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="domain-label">Kampanya Başlığı / Görünür Adı</label>
                        <input type="text" name="affiliate[affiliate_title]" value="{{ optional($domain->affiliateSetting)->affiliate_title }}" class="domain-input" placeholder="Örn: Nippon Şampuan Affiliate Programı">
                        <p class="text-[9px] text-slate-400 mt-1">Affiliate panelinde kampanya listesinde görünecek olan başlık.</p>
                    </div>
                </div>

                <!-- Çerez Gün Süresi & İlişkilendirme Kuralı -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="domain-label">Çerez Geçerlilik Süresi (Gün)</label>
                        <input type="number" name="affiliate[cookie_days]" value="{{ optional($domain->affiliateSetting)->cookie_days ?? 30 }}" class="domain-input" min="1" max="365" required>
                        <p class="text-[9px] text-slate-400 mt-1">Ziyaretçinin tarayıcısında affiliate takibinin kaç gün tutulacağını belirler.</p>
                    </div>

                    <div>
                        <label class="domain-label">İlişkilendirme (Attribution) Kuralı</label>
                        <select name="affiliate[attribution_rule]" class="domain-input">
                            <option value="last_click" {{ optional($domain->affiliateSetting)->attribution_rule === 'last_click' ? 'selected' : '' }}>Son Tıklama (Son Tıklayan Kazanır)</option>
                            <option value="first_click" {{ optional($domain->affiliateSetting)->attribution_rule === 'first_click' ? 'selected' : '' }}>İlk Tıklama (İlk Yönlendiren Kazanır)</option>
                        </select>
                        <p class="text-[9px] text-slate-400 mt-1">Aynı müşterinin farklı affiliate linklerine tıklaması durumunda satışı kimin hak edeceğini belirler.</p>
                    </div>
                </div>

                <!-- Kampanya Açıklaması -->
                <div>
                    <label class="domain-label">Kampanya Detay Açıklaması</label>
                    <textarea name="affiliate[affiliate_description]" rows="3" class="domain-input text-xs" placeholder="Affiliate üyelerinin panelinde bu kampanya detaylarında görünecek açıklama ve kurallar...">{{ optional($domain->affiliateSetting)->affiliate_description }}</textarea>
                </div>

                <hr class="border-slate-100">

                <!-- Tanıtım Görselleri Aktifliği -->
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="affiliate[media_enabled]" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-5 h-5" {{ (optional($domain->affiliateSetting)->media_enabled ?? true) ? 'checked' : '' }}>
                        <div>
                            <span class="text-sm font-black text-slate-900">Tanıtım Medyalarını Göster</span>
                            <p class="text-[10px] text-slate-400 font-semibold mt-0.5">Üyeler kendi panellerinde bu domain için eklenmiş banner/görsel medyaları görebilir.</p>
                        </div>
                    </label>
                </div>

                <!-- Önemli Uyarı Metni & Yasaklı Terimler -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="domain-label">Affiliate Özel Uyarı / Bilgilendirme Metni</label>
                        <textarea name="affiliate[warning_text]" rows="3" class="domain-input text-xs text-orange-700 bg-orange-50/20 border-orange-100 placeholder-orange-300" placeholder="Affiliate üyelerine gösterilecek özel dikkat veya kural uyarısı...">{{ optional($domain->affiliateSetting)->warning_text }}</textarea>
                        <p class="text-[9px] text-slate-400 mt-1">Üye panelinde sarı uyarı kutusunda gösterilir.</p>
                    </div>

                    <div>
                        <label class="domain-label">Yasaklı Tanıtım Yöntemleri / Terimleri</label>
                        <textarea name="affiliate[forbidden_terms]" rows="3" class="domain-input text-xs" placeholder="Örn: Google Ads marka aramaları yasaktır. Spam mailler, yanıltıcı reklamlar vb...">{{ optional($domain->affiliateSetting)->forbidden_terms }}</textarea>
                        <p class="text-[9px] text-slate-400 mt-1">Üye panelinde yasaklı kurallar listesinde gösterilir.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <button type="submit" id="master-submit-btn" class="hidden" tabindex="-1"></button>
    </form>
    @if(!$isCreate)
        <form id="reset-domain-form" action="{{ route('admin.domains.reset-infrastructure', $domain) }}" method="POST" class="hidden">
            @csrf
        </form>
    @endif

            {{-- Tab: Expenses --}}
            @if(!$isCreate)
            <div id="domain-tab-panel-expenses" class="domain-tab-panel hidden" role="tabpanel" aria-labelledby="domain-tab-btn-expenses">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Masraf Listesi --}}
            <div class="lg:col-span-2">
                <div class="premium-card p-6 border border-slate-100 shadow-soft">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Kayıtlı Masraflar</h3>
                        <div class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-black rounded-lg">
                            TOPLAM: ₺{{ number_format($domain->total_expense, 2) }}
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                    <th class="pb-3">Tarih</th>
                                    <th class="pb-3">Platform</th>
                                    <th class="pb-3">Açıklama</th>
                                    <th class="pb-3 text-right">Tutar</th>
                                    <th class="pb-3 text-right">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($domain->expenses()->orderBy('spent_at', 'desc')->get() as $expense)
                                    <tr>
                                        <td class="py-4 text-slate-500 font-bold">{{ $expense->spent_at->format('d.m.Y') }}</td>
                                        <td class="py-4">
                                            <span class="px-2 py-1 bg-slate-100 text-slate-700 text-[10px] font-black rounded uppercase">
                                                {{ $expense->platform }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-slate-600 text-xs">{{ $expense->description }}</td>
                                        <td class="py-4 text-right font-black text-slate-900">₺{{ number_format($expense->amount, 2) }}</td>
                                        <td class="py-4 text-right">
                                            <form action="{{ route('admin.domains.expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('Bu masrafı silmek istediğinize emin misiniz?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-600 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-slate-400 font-medium italic">
                                            Henüz masraf kaydı bulunmuyor.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Masraf Ekleme Formu --}}
            <div>
                <div class="premium-card p-6 border border-indigo-100 bg-indigo-50/30 sticky top-6">
                    <h3 class="text-md font-black text-slate-900 tracking-tight mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Yeni Masraf Ekle
                    </h3>
                    
                    <form id="expense-form" action="{{ route('admin.domains.expenses.store', $domain) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1">Platform</label>
                            <select name="platform" class="domain-input" required>
                                <option value="Google Ads">Google Ads</option>
                                <option value="Facebook/Meta">Facebook/Meta</option>
                                <option value="Tiktok Ads">Tiktok Ads</option>
                                <option value="Taboola">Taboola</option>
                                <option value="BunnyCDN">BunnyCDN</option>
                                <option value="Cloudflare">Cloudflare</option>
                                <option value="Diğer">Diğer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1">Tarih</label>
                            <input type="date" name="spent_at" value="{{ date('Y-m-d') }}" class="domain-input" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1">Tutar (₺)</label>
                            <input type="number" step="0.01" name="amount" placeholder="0.00" class="domain-input font-black" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1">Açıklama</label>
                            <textarea name="description" rows="2" class="domain-input text-xs" placeholder="Reklam harcaması, yenileme vb."></textarea>
                        </div>
                        
                        <button type="submit" class="w-full bg-indigo-600 text-white font-black py-3 rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                            Kaydet
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const DOMAIN_FORM_TAB_DEFAULT = @json($domainFormTabDefault);
    const ALL_PRODUCTS = @json($products);
    const DOMAIN_FORM_IS_CREATE = @json($showPicker);
    const CLOUDFLARE_ZONES_URL = @json(route('admin.cloudflare.zones'));
    const CLOUDFLARE_ONBOARDING_DRAFT_URL = @json(route('admin.domains.cloudflare.onboarding-draft'));

    let cfZonesCache = [];

    function hasSwal() {
        return typeof window.Swal !== 'undefined';
    }

    function notifyInfo(message) {
        if (hasSwal()) {
            return Swal.fire({ icon: 'info', text: message, confirmButtonText: 'Tamam' });
        }
        alert(message);
        return Promise.resolve();
    }

    function notifyError(message) {
        if (hasSwal()) {
            return Swal.fire({ icon: 'error', text: message, confirmButtonText: 'Tamam' });
        }
        alert(message);
        return Promise.resolve();
    }

    function notifySuccess(message) {
        if (hasSwal()) {
            return Swal.fire({ icon: 'success', text: message, confirmButtonText: 'Tamam' });
        }
        alert(message);
        return Promise.resolve();
    }

    async function askConfirm(message, title = 'Emin misiniz?') {
        if (hasSwal()) {
            const res = await Swal.fire({
                title,
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal',
            });
            return !!res.isConfirmed;
        }
        return confirm(message);
    }

    function normalizeCfSearch(value) {
        return (value || '').trim().toLowerCase();
    }

    function isCfDropdownOpen() {
        const drop = document.getElementById('cf_zone_dropdown');

        return drop && ! drop.classList.contains('hidden');
    }

    function setCfDropdownOpen(open) {
        const drop = document.getElementById('cf_zone_dropdown');
        const trig = document.getElementById('cf_zone_trigger');
        const chev = document.getElementById('cf_zone_chevron');
        const search = document.getElementById('cf_zone_search');
        if (! drop || ! trig) {
            return;
        }
        drop.classList.toggle('hidden', ! open);
        trig.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (chev) {
            chev.classList.toggle('rotate-180', open);
        }
        if (open) {
            if (search) {
                search.value = '';
                search.disabled = cfZonesCache.length === 0;
            }
            renderCfZoneList();
            requestAnimationFrame(function () {
                if (search && ! search.disabled) {
                    search.focus();
                }
            });
        } else if (search) {
            search.value = '';
            search.disabled = true;
        }
    }

    function updateCfApexDisplayStrings(apex) {
        const a = apex || '—';
        document.querySelectorAll('.cf-preview-apex-inline').forEach(function (el) {
            el.textContent = a;
        });
        document.querySelectorAll('.cf-preview-apex-suffix-inline').forEach(function (el) {
            el.textContent = a;
        });
        const suf = document.getElementById('cf-subdomain-apex-suffix');
        if (suf) {
            suf.textContent = a;
        }
    }

    function hideCfHostnamePanel() {
        const wrap = document.getElementById('cf-hostname-mode-wrap');
        if (wrap) {
            wrap.classList.add('hidden');
        }
        const apexH = document.getElementById('cf_zone_apex');
        if (apexH) {
            apexH.value = '';
        }
        const sub = document.getElementById('cf_subdomain_label');
        if (sub) {
            sub.value = '';
        }
        const apexRadio = document.getElementById('cf_host_mode_apex');
        if (apexRadio) {
            apexRadio.checked = true;
        }
        const row = document.getElementById('cf-subdomain-row');
        if (row) {
            row.classList.add('hidden');
        }
        updateCfApexDisplayStrings('');
    }

    function getCfHostMode() {
        const sub = document.getElementById('cf_host_mode_sub');

        return sub && sub.checked ? 'subdomain' : 'apex';
    }

    function setCfHostMode(mode) {
        const apexR = document.getElementById('cf_host_mode_apex');
        const subR = document.getElementById('cf_host_mode_sub');
        if (mode === 'subdomain') {
            if (subR) {
                subR.checked = true;
            }
        } else if (apexR) {
            apexR.checked = true;
        }
    }

    function normalizeSubdomainLabel(raw) {
        let s = (raw || '').trim().toLowerCase().replace(/\.+$/, '').replace(/^\.+/, '');
        while (s.includes('..')) {
            s = s.replace(/\.\./g, '.');
        }

        return s;
    }

    function isValidSubdomainLabel(s) {
        if (! s) {
            return false;
        }
        const parts = s.split('.');
        for (let i = 0; i < parts.length; i++) {
            const p = parts[i];
            if (p.length < 1 || p.length > 63) {
                return false;
            }
            if (! /^[a-z0-9-]+$/.test(p)) {
                return false;
            }
            if (p.startsWith('-') || p.endsWith('-')) {
                return false;
            }
        }

        return true;
    }

    function updateCfHostnamePreview() {
        const el = document.getElementById('cf_hostname_preview');
        const host = document.getElementById('domain_name')?.value || '';
        if (el) {
            el.textContent = host || '—';
            el.classList.toggle('text-red-600', getCfHostMode() === 'subdomain' && ! host);
        }
    }

    function updateCfSelectionSummaryBlocks() {
        const summary = document.getElementById('cf-selection-summary');
        const hostEl = document.getElementById('cf-summary-host');
        const zoneLine = document.getElementById('cf-summary-zone-line');
        const zoneId = document.getElementById('cloudflare_zone_id')?.value || '';
        const apex = document.getElementById('cf_zone_apex')?.value || '';
        const host = document.getElementById('domain_name')?.value || '';
        if (! summary) {
            return;
        }
        if (! zoneId || ! apex) {
            summary.classList.add('hidden');

            return;
        }
        summary.classList.remove('hidden');
        if (hostEl) {
            hostEl.textContent = host || '(Subdomain etiketini tamamlayın)';
        }
        if (zoneLine) {
            zoneLine.textContent = apex;
        }
    }

    function applyCfHostname() {
        const apex = (document.getElementById('cf_zone_apex')?.value || '').trim().toLowerCase();
        const mode = getCfHostMode();
        const nameEl = document.getElementById('domain_name');
        const labelRaw = document.getElementById('cf_subdomain_label')?.value || '';
        const label = normalizeSubdomainLabel(labelRaw);
        if (! apex || ! nameEl) {
            return;
        }
        if (mode === 'apex') {
            nameEl.value = apex;
        } else if (isValidSubdomainLabel(label)) {
            nameEl.value = label + '.' + apex;
        } else {
            nameEl.value = '';
        }
        updateCfHostnamePreview();
        updateCfSelectionSummaryBlocks();
    }

    function onCfHostModeChange() {
        const row = document.getElementById('cf-subdomain-row');
        if (row) {
            row.classList.toggle('hidden', getCfHostMode() !== 'subdomain');
        }
        applyCfHostname();
    }

    function syncCfHostModeFromDomainName() {
        const apex = (document.getElementById('cf_zone_apex')?.value || '').trim().toLowerCase();
        const fullEl = document.getElementById('domain_name');
        const full = (fullEl?.value || '').trim().toLowerCase();
        const subInput = document.getElementById('cf_subdomain_label');
        if (! apex) {
            return;
        }
        if (full === apex) {
            setCfHostMode('apex');
            if (subInput) {
                subInput.value = '';
            }
            onCfHostModeChange();

            return;
        }
        const suf = '.' + apex;
        if (full.endsWith(suf) && full.length > suf.length) {
            const label = normalizeSubdomainLabel((fullEl.value || '').trim().slice(0, -(suf.length)));
            if (label && isValidSubdomainLabel(label)) {
                setCfHostMode('subdomain');
                if (subInput) {
                    subInput.value = label;
                }
                onCfHostModeChange();

                return;
            }
        }
        setCfHostMode('apex');
        if (subInput) {
            subInput.value = '';
        }
        if (fullEl && full && full !== apex) {
            fullEl.value = apex;
        }
        onCfHostModeChange();
    }

    function updateCfTriggerLabel() {
        const label = document.getElementById('cf_zone_trigger_label');
        if (! label) {
            return;
        }
        const zoneId = document.getElementById('cloudflare_zone_id')?.value || '';
        const apex = document.getElementById('cf_zone_apex')?.value || '';
        label.classList.remove('text-slate-400', 'text-slate-900');
        if (apex && zoneId) {
            const z = cfZonesCache.find(function (x) { return x.id === zoneId; });
            const st = z && z.status ? ' · ' + z.status : '';
            label.textContent = apex + st;
            label.classList.add('text-slate-900');
        } else if (cfZonesCache.length === 0) {
            const legacyName = document.getElementById('domain_name')?.value || '';
            if (legacyName && ! apex) {
                label.textContent = legacyName;
                label.classList.add('text-slate-900');
            } else {
                label.textContent = 'Önce listeyi getirin…';
                label.classList.add('text-slate-400');
            }
        } else {
            label.textContent = 'Zone kökünü seçin…';
            label.classList.add('text-slate-400');
        }
    }

    function resetCfZonePickerState() {
        cfZonesCache = [];
        const ul = document.getElementById('cf_zone_list');
        const trig = document.getElementById('cf_zone_trigger');
        if (ul) {
            ul.innerHTML = '';
        }
        if (trig) {
            trig.disabled = true;
        }
        hideCfHostnamePanel();
        setCfDropdownOpen(false);
        updateCfTriggerLabel();
    }

    function renderCfZoneList() {
        if (! isCfDropdownOpen()) {
            return;
        }
        const ul = document.getElementById('cf_zone_list');
        const search = document.getElementById('cf_zone_search');
        if (! ul || ! search) {
            return;
        }

        const selectedId = document.getElementById('cloudflare_zone_id')?.value || '';
        const q = normalizeCfSearch(search.value);
        let filtered = cfZonesCache;
        if (q) {
            filtered = cfZonesCache.filter(function (z) {
                const name = (z.name || '').toLowerCase();
                const st = (z.status || '').toLowerCase();
                const id = (z.id || '').toLowerCase();

                return name.includes(q) || st.includes(q) || id.includes(q);
            });
        }

        ul.innerHTML = '';

        if (cfZonesCache.length === 0) {
            const li = document.createElement('li');
            li.className = 'px-3 py-4 text-xs font-semibold text-slate-500 text-center';
            li.setAttribute('role', 'presentation');
            li.textContent = 'Önce «Listeyi getir» ile zone’ları yükleyin.';
            ul.appendChild(li);

            return;
        }

        if (filtered.length === 0) {
            const li = document.createElement('li');
            li.className = 'px-3 py-3 text-xs font-semibold text-slate-500';
            li.setAttribute('role', 'presentation');
            li.textContent = 'Aramanızla eşleşen zone yok.';
            ul.appendChild(li);

            return;
        }

        filtered.forEach(function (z) {
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.tabIndex = 0;
            li.className = 'cf-zone-item flex items-center justify-between gap-2 px-3 py-2.5 cursor-pointer border-b border-slate-200/80 last:border-b-0 text-sm font-bold text-slate-800 hover:bg-white transition';
            if (z.id === selectedId) {
                li.classList.add('bg-brand-50', 'ring-1', 'ring-inset', 'ring-brand-200');
            }
            li.dataset.zoneId = z.id;
            li.dataset.domainName = z.name;

            const nameSpan = document.createElement('span');
            nameSpan.className = 'truncate';
            nameSpan.textContent = z.name;
            li.appendChild(nameSpan);

            if (z.status) {
                const stSpan = document.createElement('span');
                stSpan.className = 'shrink-0 text-[10px] font-black uppercase tracking-wide text-slate-400';
                stSpan.textContent = z.status;
                li.appendChild(stSpan);
            }

            function pick() {
                selectCfZone(z.id, z.name, true);
            }
            li.addEventListener('click', function (e) {
                e.stopPropagation();
                pick();
            });
            li.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    pick();
                }
            });

            ul.appendChild(li);
        });
    }

    function selectCfZone(id, apexName, userPicked) {
        const zoneEl = document.getElementById('cloudflare_zone_id');
        const apexEl = document.getElementById('cf_zone_apex');
        const wrap = document.getElementById('cf-hostname-mode-wrap');
        if (zoneEl) {
            zoneEl.value = id || '';
        }
        if (apexEl) {
            apexEl.value = apexName || '';
        }
        if (wrap) {
            wrap.classList.remove('hidden');
        }
        updateCfApexDisplayStrings(apexName || '');

        if (userPicked) {
            const sub = document.getElementById('cf_subdomain_label');
            if (sub) {
                sub.value = '';
            }
            setCfHostMode('apex');
            onCfHostModeChange();
        } else {
            syncCfHostModeFromDomainName();
        }

        setCfDropdownOpen(false);
        updateCfTriggerLabel();
    }

    async function loadCloudflareZones() {
        const accountEl = document.getElementById('cloudflare_account_id');
        const status = document.getElementById('cf-zones-status');
        const btn = document.getElementById('cf-load-zones-btn');
        const trig = document.getElementById('cf_zone_trigger');
        const debugBox = document.getElementById('cf-debug-box');
        const debugPre = document.getElementById('cf-debug-pre');
        if (! accountEl || ! status || ! btn || ! trig) {
            return;
        }
        if (debugBox && debugPre) {
            debugBox.classList.add('hidden');
            debugPre.textContent = '';
        }

        resetCfZonePickerState();

        const accountId = accountEl.value;
        status.textContent = 'Yükleniyor...';
        btn.disabled = true;

        const url = new URL(CLOUDFLARE_ZONES_URL, window.location.origin);
        if (accountId) {
            url.searchParams.set('cloudflare_account_id', accountId);
        }

        try {
            const res = await fetch(url.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();

            if (! data.success) {
                cfZonesCache = [];
                status.textContent = data.message || 'Liste alınamadı.';
                trig.disabled = true;
                if (debugBox && debugPre && data.debug) {
                    debugPre.textContent = JSON.stringify(data.debug, null, 2);
                    debugBox.classList.remove('hidden');
                }

                return;
            }

            cfZonesCache = data.zones || [];
            status.textContent = cfZonesCache.length + ' zone listelendi. Kutuya tıklayıp arayarak seçin.';
            trig.disabled = false;

            const curZone = document.getElementById('cloudflare_zone_id')?.value || '';
            const curName = document.getElementById('domain_name')?.value || '';
            let restored = null;
            if (curZone) {
                restored = cfZonesCache.find(function (z) { return z.id === curZone; });
            }
            if (! restored && curName) {
                restored = cfZonesCache.find(function (z) { return z.name === curName; });
            }
            if (! restored && curName) {
                const curLower = curName.toLowerCase();
                let best = null;
                cfZonesCache.forEach(function (z) {
                    const zn = (z.name || '').toLowerCase();
                    if (curLower === zn || (curLower.endsWith('.' + zn) && curLower.length > zn.length + 1)) {
                        if (! best || zn.length > best.name.length) {
                            best = z;
                        }
                    }
                });
                restored = best;
            }

            if (restored) {
                selectCfZone(restored.id, restored.name, false);
            } else {
                updateCfTriggerLabel();
            }
        } catch (e) {
            status.textContent = 'İstek başarısız.';
            cfZonesCache = [];
            const ul = document.getElementById('cf_zone_list');
            if (ul) {
                ul.innerHTML = '';
            }
            trig.disabled = true;
            updateCfTriggerLabel();
        }
        btn.disabled = false;
    }

    function domainFormSwitchTab(key) {
        const keys = ['infra', 'marketing', 'payment', 'offers', 'gallery', 'upsell', 'affiliate', 'expenses'];
        if (! keys.includes(key)) return;

        keys.forEach(k => {
            const panel = document.getElementById('domain-tab-panel-' + k);
            const btn = document.getElementById('domain-tab-btn-' + k);
            
            if (panel) {
                panel.classList.toggle('hidden', k !== key);
            }
            if (btn) {
                btn.setAttribute('aria-selected', k === key ? 'true' : 'false');
                btn.classList.toggle('domain-tab-btn-active', k === key);
            }
        });
        try {
            localStorage.setItem('teksat_domain_form_tab', key);
        } catch (e) {}
    }

    function confirmDomainReset() {
        if (confirm('DİKKAT: Tanımlı olan site adresini ve altyapı bilgilerini silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
            document.getElementById('reset-domain-form').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (DOMAIN_FORM_IS_CREATE) {
            const trig = document.getElementById('cf_zone_trigger');
            const cfSearch = document.getElementById('cf_zone_search');
            const picker = document.getElementById('cf-zone-picker');
            const acc = document.getElementById('cloudflare_account_id');
            const btnLoad = document.getElementById('cf-load-zones-btn');
            const btnOnboardingDraft = document.getElementById('cf-onboarding-draft-btn');
            if (trig) {
                function toggleCfDropdown(e) {
                    if (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    if (trig.disabled || cfZonesCache.length === 0) {
                        return;
                    }
                    setCfDropdownOpen(! isCfDropdownOpen());
                }
                trig.addEventListener('click', toggleCfDropdown);
                trig.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        toggleCfDropdown(e);
                    }
                });
            }
            if (cfSearch) {
                cfSearch.addEventListener('input', function () {
                    renderCfZoneList();
                });
                cfSearch.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }
            document.addEventListener('click', function (e) {
                if (! picker || ! isCfDropdownOpen()) {
                    return;
                }
                if (! picker.contains(e.target)) {
                    setCfDropdownOpen(false);
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape' || ! isCfDropdownOpen()) {
                    return;
                }
                setCfDropdownOpen(false);
                trig && trig.focus();
            });
            if (acc) {
                acc.addEventListener('change', function () {
                    const z = document.getElementById('cloudflare_zone_id');
                    const n = document.getElementById('domain_name');
                    const st = document.getElementById('cf-zones-status');
                    const su = document.getElementById('cf-selection-summary');
                    if (z) z.value = '';
                    if (n) n.value = '';
                    if (st) st.textContent = '';
                    if (su) su.classList.add('hidden');
                    loadCloudflareZones();
                });
            }
            if (btnLoad) {
                btnLoad.addEventListener('click', function () {
                    loadCloudflareZones();
                });
            }
            if (btnOnboardingDraft) {
                btnOnboardingDraft.addEventListener('click', function () {
                    startCloudflareOnboardingDraft();
                });
            }
            document.querySelectorAll('input[name="cf_host_mode"]').forEach(function (r) {
                r.addEventListener('change', onCfHostModeChange);
            });
            const subLab = document.getElementById('cf_subdomain_label');
            if (subLab) {
                subLab.addEventListener('input', function () {
                    applyCfHostname();
                });
            }
            loadCloudflareZones();
        }

        if (DOMAIN_FORM_TAB_DEFAULT) {
            domainFormSwitchTab(DOMAIN_FORM_TAB_DEFAULT);
        } else {
            const saved = localStorage.getItem('teksat_domain_form_tab');
            if (saved && ['infra', 'marketing', 'payment', 'offers', 'gallery', 'upsell', 'expenses'].includes(saved)) {
                domainFormSwitchTab(saved);
            }
        }
    });

    const el = document.getElementById('sortable-gallery');
    Sortable.create(el, {
        animation: 150,
        handle: '.gallery-item',
        onEnd: function() {
            reorder();
        }
    });

    function reorder() {
        document.querySelectorAll('.sort-order-val').forEach((input, index) => {
            input.value = index + 1;
        });
    }

    function editImageProps(btn) {
        const item = btn.closest('.gallery-item');
        const props = item.querySelector('.hidden-props');
        props.classList.toggle('hidden');
    }

    async function deleteGalleryItem(btn) {
        const confirmed = await askConfirm('Bu görseli silmek istediğinize emin misiniz?');
        if (confirmed) {
            const item = btn.closest('.gallery-item');
            const delFlag = item.querySelector('.delete-flag');
            if (delFlag) {
                delFlag.checked = true;
                item.style.display = 'none';
            } else {
                item.remove();
            }
        }
    }

    function previewImages(input) {
        const container = document.getElementById('new-uploads-preview');
        if (input.files) {
            Array.from(input.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const html = `
                        <div class="gallery-item group relative aspect-[3/4] rounded-xl border-2 border-brand-100 overflow-hidden shadow-sm">
                            <img src="${e.target.result}" class="w-full h-full object-cover cursor-move">
                            <div class="absolute top-0 left-0 right-0 bg-green-600/95 text-white text-[9px] p-2 truncate pointer-events-none font-black uppercase tracking-wide">${file.name}</div>
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                                 <button type="button" onclick="this.closest('.gallery-item').remove()" class="bg-red-600 p-2 rounded-lg text-white hover:bg-red-700 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </div>
                        </div>
                    `;
                    document.getElementById('sortable-gallery').insertAdjacentHTML('beforeend', html);
                    reorder();
                }
                reader.readAsDataURL(file);
            });
        }
    }

    let offIdx = {{ count($offerRows) }};
    function renumberOfferBadges() {
        document.querySelectorAll('#offers-container .offer-row').forEach(function (row, i) {
            const badge = row.querySelector('.offer-pack-index');
            if (badge) {
                badge.textContent = String(i + 1);
            }
        });
    }
    function removeOfferRow(btn) {
        const row = btn.closest('.offer-row');
        const container = document.getElementById('offers-container');
        if (! row || ! container) {
            return;
        }
        row.remove();
        renumberOfferBadges();
    }
    function addOfferRow() {
        const container = document.getElementById('offers-container');
        const div = document.createElement('div');
        div.className = 'offer-row offer-pack-card p-4 sm:p-5';
        div.innerHTML = `
            <input type="hidden" name="offers[${offIdx}][id]" value="">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4 pb-4 border-b border-slate-100">
                <span class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-wide text-slate-400">
                    <span class="offer-pack-index flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-700 text-xs font-black">0</span>
                    Paket
                </span>
                <button type="button" onclick="removeOfferRow(this)" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-red-600 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 transition" title="Bu paketi listeden kaldır">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Kaldır
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="domain-label">API paket kimliği (harici)</label>
                    <input type="text" name="offers[${offIdx}][api_offer_id]" class="domain-input font-mono text-xs" placeholder="örn. PAKET-TEKLI, PAKET-3LU" maxlength="255" autocomplete="off">
                    <p class="text-[9px] text-slate-400 font-medium mt-1">Ortağın siparişte <code class="text-[10px]">api_offer_id</code> olarak göndermesi için. Adet ve tutar bu pakette zaten tanımlı; kod yoksa API tutar+adet ile eşler. İsteğe bağlı.</p>
                </div>
                <div>
                    <label class="domain-label">Paket adı</label>
                    <input type="text" name="offers[${offIdx}][offer_name]" class="domain-input font-bold" placeholder="Örn. 3 Al 2 Öde, Premium paket">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <div class="sm:col-span-12">
                        <label class="domain-label">Paket Görselleri (Normal / Seçili)</label>
                        <div class="flex flex-col gap-2">
                            <input type="file" name="offers[${offIdx}][image]" accept="image/*" class="domain-input offer-file-input text-[10px] font-medium text-slate-600 w-full py-2" title="Normal Görsel">
                            <input type="file" name="offers[${offIdx}][active_image]" accept="image/*" class="domain-input offer-file-input text-[10px] font-medium text-blue-600 w-full py-2 border-blue-100" title="Seçili Durum Görseli">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Paket İçeriği</h4>
                        <button type="button" onclick="addOfferItemRow(${offIdx})" class="text-[10px] font-bold text-brand-600 hover:text-brand-700 bg-white border border-brand-200 px-2 py-1 rounded-lg transition">Ürün Ekle</button>
                    </div>
                    <div id="offer-items-container-${offIdx}" class="space-y-2">
                        <p class="text-[10px] text-slate-400 italic text-center py-2 empty-msg">Paket içeriği tanımlanmadı. (Varsayılan ana ürün kullanılır)</p>
                    </div>
                </div>

                <label class="domain-toggle-focus flex items-center gap-3 p-4 rounded-2xl border border-amber-100 bg-amber-50/50 cursor-pointer hover:border-amber-200 transition max-w-md">
                    <input type="checkbox" name="offers[${offIdx}][is_popular]" value="1" class="w-5 h-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500 shrink-0">
                    <span class="min-w-0">
                        <span class="block font-black text-slate-900 text-xs uppercase tracking-wide">Popüler paket</span>
                        <span class="block text-[11px] text-slate-600 font-medium mt-0.5">Bu paket funnel’da vurgulu gösterilir.</span>
                    </span>
                </label>

                <div class="affiliate-commission-container bg-emerald-50/30 rounded-xl p-4 border border-emerald-100/80 mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-emerald-800 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Paket Affiliate Komisyon Ayarı
                        </h4>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="offers[${offIdx}][affiliate_active]" value="1" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-[11px] font-black text-emerald-900 uppercase">Bu Pakete Komisyon Ver</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Komisyon Türü</label>
                            <select name="offers[${offIdx}][commission_type]" onchange="toggleCommissionFields(this)" class="domain-input compact-select text-xs font-bold">
                                <option value="fixed" selected>Sabit Tutar (₺)</option>
                                <option value="percentage">Yüzde (%)</option>
                            </select>
                        </div>
                        <div class="comm-amount-wrapper">
                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Sabit Komisyon Tutarı (₺)</label>
                            <input type="number" step="0.01" name="offers[${offIdx}][commission_amount]" value="0.00" class="domain-input text-xs font-black py-2">
                        </div>
                        <div class="comm-rate-wrapper hidden">
                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Komisyon Oranı (%)</label>
                            <input type="number" step="0.01" name="offers[${offIdx}][commission_rate]" value="0.00" class="domain-input text-xs font-black py-2">
                        </div>
                        <div>
                            <label class="block text-[9px] font-black text-slate-500 uppercase mb-1">Özel Açıklama (Opsiyonel)</label>
                            <input type="text" name="offers[${offIdx}][affiliate_description]" value="" class="domain-input text-xs py-2" placeholder="Örn: 200 TL net kazanç!">
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(div);
        offIdx++;
        renumberOfferBadges();
    }

    function toggleCommissionFields(select) {
        const container = select.closest('.affiliate-commission-container');
        if (!container) return;
        const amountWrapper = container.querySelector('.comm-amount-wrapper');
        const rateWrapper = container.querySelector('.comm-rate-wrapper');
        if (select.value === 'percentage') {
            if (amountWrapper) amountWrapper.classList.add('hidden');
            if (rateWrapper) rateWrapper.classList.remove('hidden');
        } else {
            if (amountWrapper) amountWrapper.classList.remove('hidden');
            if (rateWrapper) rateWrapper.classList.add('hidden');
        }
    }

    function addOfferItemRow(offerIndex) {
        const container = document.getElementById('offer-items-container-' + offerIndex);
        if (!container) return;

        const emptyMsg = container.querySelector('.empty-msg');
        if (emptyMsg) emptyMsg.remove();

        const itemIdx = container.querySelectorAll('.offer-item-row').length;
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 offer-item-row p-1 bg-white rounded-xl border border-slate-100 shadow-sm';
        
        let productOptions = ALL_PRODUCTS.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        
        div.innerHTML = `
            <input type="hidden" name="offers[${offerIndex}][items][${itemIdx}][id]" value="">
            <select name="offers[${offerIndex}][items][${itemIdx}][product_id]" class="domain-input domain-select-chevron offer-item-input compact-select font-bold flex-1">
                ${productOptions}
            </select>
            <div class="w-20 shrink-0">
                <input type="number" name="offers[${offerIndex}][items][${itemIdx}][quantity]" value="1" min="1" class="domain-input text-xs font-black text-center py-2">
            </div>
            <div class="w-32 shrink-0">
                <input type="number" step="0.01" name="offers[${offerIndex}][items][${itemIdx}][price]" value="0" class="domain-input text-xs font-black text-center py-2">
            </div>
            <button type="button" onclick="this.closest('.offer-item-row').remove()" class="text-red-400 hover:text-red-600 p-1.5 hover:bg-red-50 rounded-lg transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        `;
        container.appendChild(div);
    }

    function startCloudflareOnboarding() {
        askConfirm('Domain Cloudflare hesabına yeni bir Zone olarak eklenecek. Devam edilsin mi?', 'Cloudflare Zone Aç').then(function (ok) {
            if (!ok) return;
            apiCall("{{ $domain->exists ? route('admin.domains.cloudflare.onboarding', $domain) : '' }}", 'POST');
        });
    }

    function renderCloudflareDraftNs(domainName, nameservers) {
        const box = document.getElementById('cf-onboarding-result');
        const domainEl = document.getElementById('cf-onboarding-result-domain');
        const nsEl = document.getElementById('cf-onboarding-result-ns');
        if (! box || ! domainEl || ! nsEl) {
            return;
        }

        domainEl.textContent = domainName ? ('Domain: ' + domainName) : '';
        nsEl.textContent = (nameservers && nameservers.length > 0)
            ? nameservers.join(' | ')
            : 'Cloudflare nameserver bilgisi dönmedi.';
        box.classList.remove('hidden');
    }

    async function startCloudflareOnboardingDraft() {
        const accountEl = document.getElementById('cloudflare_account_id');
        const domainInput = document.getElementById('domain_name');
        const fallbackDomain = (domainInput?.value || '').trim().toLowerCase();
        let requestedDomain = '';
        if (hasSwal()) {
            const res = await Swal.fire({
                title: 'Cloudflare\'e yeni domain ekle',
                input: 'text',
                inputLabel: 'Apex domain',
                inputPlaceholder: 'ornek.com',
                inputValue: fallbackDomain || '',
                showCancelButton: true,
                confirmButtonText: 'Ekle',
                cancelButtonText: 'İptal',
                inputValidator: (value) => {
                    const v = (value || '').trim().toLowerCase();
                    const ok = /^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/.test(v);
                    if (!ok) {
                        return 'Geçerli bir apex domain girin (örn: ornek.com).';
                    }
                    return null;
                }
            });
            if (!res.isConfirmed) {
                return;
            }
            requestedDomain = String(res.value || '').trim().toLowerCase();
        } else {
            requestedDomain = (window.prompt('Cloudflare\'e eklenecek apex domain (örn: ornek.com):', fallbackDomain || '') || '').trim().toLowerCase();
            if (!requestedDomain) return;
        }

        const confirmed = await askConfirm('Domain Cloudflare hesabına yeni bir zone olarak eklenecek. Devam edilsin mi?', 'Cloudflare Zone Aç');
        if (!confirmed) return;

        const loader = document.getElementById('loading-overlay');
        loader.classList.remove('hidden');

        try {
            const res = await fetch(CLOUDFLARE_ONBOARDING_DRAFT_URL, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    domain_name: requestedDomain,
                    cloudflare_account_id: accountEl?.value || null
                })
            });
            const rawText = await res.text();
            let data = null;
            try {
                data = JSON.parse(rawText);
            } catch (err) {
                data = {
                    success: false,
                    message: 'JSON parse hatası. Ham yanıt: ' + rawText.slice(0, 600),
                };
            }
            loader.classList.add('hidden');

            if (! data.success) {
                const dbg = data.debug ? ('\n\nDebug:\n' + JSON.stringify(data.debug, null, 2)) : '';
                await notifyError((data.message || 'Cloudflare zone oluşturulamadı.') + dbg);
                return;
            }

            const zoneName = data.zone_name || requestedDomain;
            const zoneId = data.zone_id || '';
            const nameservers = Array.isArray(data.name_servers) ? data.name_servers : [];
            if (zoneId && ! cfZonesCache.some(function (z) { return z.id === zoneId; })) {
                cfZonesCache.unshift({
                    id: zoneId,
                    name: zoneName,
                    status: 'pending',
                });
            }

            selectCfZone(zoneId, zoneName, true);
            renderCfZoneList();
            renderCloudflareDraftNs(zoneName, nameservers);
            await notifySuccess((data.message || 'Cloudflare zone oluşturuldu.') + '\n\nNS: ' + (nameservers.join(', ') || '-'));
        } catch (e) {
            loader.classList.add('hidden');
            await notifyError('Cloudflare zone oluşturma isteği başarısız.');
        }
    }

    function checkCloudflareStatus() {
        const url = "{{ $domain->exists ? route('admin.domains.cloudflare.status', $domain) : '' }}";
        if (! url) {
            return;
        }
        fetch(url)
            .then(res => res.json())
            .then(data => {
                const elStatus = document.getElementById('cf-status-label');
                const msg = data.message || (data.status ? 'Durum: ' + data.status : 'Bilinmiyor');
                if (elStatus) {
                    elStatus.innerText = msg;
                    elStatus.classList.remove('text-slate-600', 'text-green-400');
                    if (data.success && data.status === 'active') {
                        elStatus.classList.add('text-green-400');
                    } else {
                        elStatus.classList.add('text-slate-600');
                    }
                }
                notifyInfo(msg);
            })
            .catch(function () {
                notifyError('Durum isteği başarısız.');
            });
    }

    function finalizeCloudflareDNS() {
        askConfirm('Eksik DNS kayıtları eklenecek ve SSL "Full" yapılacak. Zaten uygunsa işlem yapılmaz. Devam?', 'Cloudflare Finalize').then(function (ok) {
            if (!ok) return;
            apiCall("{{ $domain->exists ? route('admin.domains.cloudflare.finalize', $domain) : '' }}", 'POST');
        });
    }

    async function provisionBunnyCdn() {
        const confirmed = await askConfirm('Bunny tarafında Pull Zone oluşturulacak ve domaine custom hostname eklenmeye çalışılacak. Devam?', 'Bunny Kurulum');
        if (!confirmed) return;
        apiCall("{{ $domain->exists ? route('admin.domains.bunny.provision', $domain) : '' }}", 'POST', function(data) {
            const el = document.getElementById('bunny-status-label');
            if (el) {
                el.innerText = data.message || 'Bunny kurulum tamamlandı.';
            }
        });
    }

    async function provisionBunnyCdnDraft() {
        const domainInput = document.getElementById('domain_name');
        const domainName = (domainInput?.value || '').trim();
        if (!domainName) {
            notifyError('Önce Cloudflare zone seçerek alan adını oluşturun.');
            return;
        }
        const confirmed = await askConfirm('Bunny tarafında Pull Zone oluşturulacak ve bu domain için custom hostname eklenecek. Devam?', 'Bunny Draft Kurulum');
        if (!confirmed) return;

        const loader = document.getElementById('loading-overlay');
        loader.classList.remove('hidden');

        fetch("{{ route('admin.domains.bunny.provision-draft') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ domain_name: domainName })
        })
            .then(res => res.json())
            .then(data => {
                loader.classList.add('hidden');
                const msg = data.message || (data.success ? 'Bunny kurulum tamamlandı.' : 'İşlem başarısız.');
                if (data.success) notifySuccess(msg); else notifyError(msg);
                const el = document.getElementById('bunny-status-label');
                if (el) el.innerText = msg;
                if (data.success) {
                    const pz = document.getElementById('bunny_pullzone_id');
                    const bh = document.getElementById('bunny_hostname');
                    if (pz) pz.value = data.pullzone_id || '';
                    if (bh) bh.value = data.hostname || domainName;
                }
            })
            .catch(() => {
                loader.classList.add('hidden');
                notifyError('Bunny draft kurulum isteği başarısız.');
            });
    }

    function checkBunnyStatus() {
        const url = "{{ $domain->exists ? route('admin.domains.bunny.status', $domain) : '' }}";
        if (!url) return;
        const loader = document.getElementById('loading-overlay');
        loader.classList.remove('hidden');
        fetch(url)
            .then(res => res.json())
            .then(data => {
                loader.classList.add('hidden');
                const msg = data.message || 'Bunny durum bilgisi alınamadı.';
                const el = document.getElementById('bunny-status-label');
                if (el) {
                    el.innerText = msg;
                }
                notifyInfo(msg);
            })
            .catch(() => {
                loader.classList.add('hidden');
                notifyError('Bunny durum isteği başarısız.');
            });
    }

    function apiCall(url, method, onSuccess) {
        if(!url) return;
        const loader = document.getElementById('loading-overlay');
        loader.classList.remove('hidden');

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loader.classList.add('hidden');
            const msg = data.message || (data.success ? 'Tamam.' : 'İşlem başarısız.');
            if (data.success) notifySuccess(msg); else notifyError(msg);
            
            if (data.success && typeof onSuccess === 'function') {
                onSuccess(data);
            }
            if (data.success && data.zone_id) {
                location.reload();
            }
        })
        .catch(err => {
            loader.classList.add('hidden');
            notifyError('İşlem sırasında hata oluştu.');
        });
    }

    async function createCloudflareDNS() {
        const confirmed = await askConfirm('Finalize ile aynı işlemi yapar: eksik A kayıtları ve SSL. Zaten uygunsa tekrar kayıt eklenmez. Devam?', 'Cloudflare DNS Oluştur');
        if (!confirmed) return;

        const loader = document.getElementById('loading-overlay');
        loader.classList.remove('hidden');

        fetch("{{ $domain->exists ? route('admin.domains.cloudflare', $domain) : '#' }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loader.classList.add('hidden');
            if(data.success) {
                notifySuccess('Başarılı: ' + data.message);
            } else {
                notifyError('Hata: ' + data.message);
            }
        })
        .catch(err => {
            loader.classList.add('hidden');
            notifyError('Sunucu iletişim hatası oluştu!');
        });
    }


    let upsellIdx = {{ count($upsellRows) }};
    function addUpsellRow() {
        const container = document.getElementById('upsells-container');
        const emptyState = document.getElementById('upsells-empty-state');
        if (emptyState) emptyState.remove();

        const div = document.createElement('div');
        div.className = 'upsell-row offer-pack-card p-4 sm:p-5 border-orange-100 bg-orange-50/10';
        div.innerHTML = `
            <input type="hidden" name="upsells[${upsellIdx}][id]" value="">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4 pb-4 border-b border-orange-100">
                <span class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-wide text-orange-600">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 text-orange-700 text-xs font-black">0</span>
                    Upsell Teklifi
                </span>
                <button type="button" onclick="removeUpsellRow(this)" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-red-600 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Kaldır
                </button>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="domain-label">Teklif Adı (Admin)</label>
                        <input type="text" name="upsells[${upsellIdx}][name]" class="domain-input font-bold" placeholder="Örn. +1 Adet Daha">
                    </div>
                    <div>
                        <label class="domain-label">Başlık (Müşteri)</label>
                        <input type="text" name="upsells[${upsellIdx}][title]" class="domain-input font-black" placeholder="Örn. Bu Fırsatı Kaçırma!">
                    </div>
                </div>
                <div>
                    <label class="domain-label">Açıklama</label>
                    <textarea name="upsells[${upsellIdx}][description]" rows="2" class="domain-input text-xs"></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="domain-label">Teklif Türü</label>
                        <select name="upsells[${upsellIdx}][offer_type]" class="domain-input font-bold text-xs">
                            <option value="add_same_product">Aynı üründen ek adet</option>
                            <option value="upgrade_package">Paket yükseltme</option>
                            <option value="complementary_product">Tamamlayıcı ürün</option>
                        </select>
                    </div>
                    <div>
                        <label class="domain-label">Hedef Ürün</label>
                        <select name="upsells[${upsellIdx}][target_product_id]" class="domain-input font-bold text-xs">
                            <option value="">— Seçin —</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="domain-label">Hedef Paket (Opsiyonel)</label>
                        <select name="upsells[${upsellIdx}][target_package_id]" class="domain-input font-bold text-xs">
                            <option value="">— Seçin —</option>
                            @foreach($domain->offers as $doff)
                                <option value="{{ $doff->id }}">{{ $doff->offer_name }} (₺{{ number_format($doff->price, 2, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="domain-label">Normal Fiyat</label>
                        <input type="number" step="0.01" name="upsells[${upsellIdx}][original_price]" class="domain-input tabular-nums" placeholder="0.00">
                    </div>
                    <div>
                        <label class="domain-label">İndirimli Fiyat (Satış)</label>
                        <input type="number" step="0.01" name="upsells[${upsellIdx}][discount_price]" class="domain-input font-black text-orange-700 tabular-nums" placeholder="0.00">
                    </div>
                    <div>
                        <label class="domain-label">Gösterim Zamanı</label>
                        <select name="upsells[${upsellIdx}][display_timing]" class="domain-input font-bold text-xs">
                            <option value="thank_you">Sadece Teşekkür Sayfası</option>
                            <option value="operator">Sadece Operatör Ekranı</option>
                            <option value="both" selected>İkisi Birden</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="upsells[${upsellIdx}][is_active]" value="1" checked class="w-4 h-4 rounded text-orange-600 focus:ring-orange-500">
                        <span class="text-xs font-bold text-slate-700">Aktif</span>
                    </label>
                </div>
            </div>
        `;
        container.appendChild(div);
        upsellIdx++;
        renumberUpsellBadges();
    }

    async function removeUpsellRow(btn) {
        const confirmed = await askConfirm('Bu teklifi listeden çıkarmak istediğinize emin misiniz?', 'Teklifi Kaldır');
        if (confirmed) {
            btn.closest('.upsell-row').remove();
            renumberUpsellBadges();
        }
    }

    function renumberUpsellBadges() {
        const rows = document.querySelectorAll('.upsell-row');
        rows.forEach((row, idx) => {
            const badge = row.querySelector('.flex.h-7.w-7');
            if(badge) badge.innerText = idx + 1;
        });
    }

    const mainDomainForm = document.getElementById('main-domain-form');
    if (mainDomainForm) {
        mainDomainForm.addEventListener('submit', function () {
            const loader = document.getElementById('loading-overlay');
            if (loader) {
                loader.classList.remove('hidden');
            }
        });
    }
</script>
@endsection
