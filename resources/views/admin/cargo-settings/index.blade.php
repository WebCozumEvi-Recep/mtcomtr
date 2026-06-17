@extends('layouts.admin')

@section('title', 'Kargo Entegrasyon Ayarları')

@section('content')
<div class="fade-in-up space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Kargo Ayarları</h2>
                <button 
                    x-data="favoriteManager" 
                    @click="toggle('admin.cargo-settings.index', 'Kargo Ayarları', '{{ route('admin.cargo-settings.index') }}', 'credit-card')"
                    class="favorite-btn"
                    style="display: inline-flex !important; min-width: 32px; min-height: 32px;"
                    title="Hızlı İşlemlere Ekle/Kaldır"
                >
                    <svg data-favorite-id="admin.cargo-settings.index" class="w-6 h-6 {{ (auth()->user()->favorites && collect(auth()->user()->favorites)->contains('id', 'admin.cargo-settings.index')) ? 'text-amber-400' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-slate-500 font-medium">Lojistik firmaları için API ve entegrasyon bilgilerini yönetin.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($settings as $setting)
        <div class="premium-card overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-white shadow-sm rounded-xl border border-slate-100">
                         <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 uppercase tracking-widest text-sm">{{ $setting->display_name }}</h3>
                        <span class="text-[10px] font-bold {{ $setting->is_active ? 'text-green-600' : 'text-slate-400' }} uppercase tracking-widest">
                            {{ $setting->is_active ? 'AKTİF' : 'PASİF' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.cargo-settings.update', $setting) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 flex items-center justify-between bg-slate-50 p-3 rounded-xl border border-slate-100 mb-2">
                             <label class="text-xs font-bold text-slate-700">Servis Durumu (Aktif/Pasif)</label>
                             <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $setting->is_active ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                            </label>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">API Kullanıcı Adı</label>
                            <input type="text" name="api_username" value="{{ $setting->api_username }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">API Şifre</label>
                            <input type="password" name="api_password" value="{{ $setting->api_password }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Müşteri / Abone Kodu</label>
                            <input type="text" name="api_customer_code" value="{{ $setting->api_customer_code }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">API Key / Token</label>
                            <input type="text" name="api_key" value="{{ $setting->api_key }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-brand-500 outline-none">
                        </div>
                        
                        @if($setting->carrier_name === 'yurtici')
                        <div>
                            <label class="text-[10px] font-black text-rose-500 uppercase tracking-widest block mb-1">Tahsilatlı API Kullanıcı Adı</label>
                            <input type="text" name="api_cod_username" value="{{ $setting->api_cod_username }}" class="w-full bg-rose-50/50 border border-rose-100 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-rose-500 outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-rose-500 uppercase tracking-widest block mb-1">Tahsilatlı API Şifre</label>
                            <input type="password" name="api_cod_password" value="{{ $setting->api_cod_password }}" class="w-full bg-rose-50/50 border border-rose-100 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-rose-500 outline-none">
                        </div>
                        @endif
                        <div class="col-span-2 border-t border-slate-100 pt-4 mt-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Firma Logosu (PNG Tavsiye Edilir)</label>
                            <div class="flex items-center gap-4">
                                @php
                                    $cargoSlug = strtolower(str_replace([' ', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ş', 'Ş', 'ö', 'Ö', 'ç', 'Ç'], ['-', 'i', 'i', 'g', 'g', 'u', 'u', 's', 's', 'o', 'o', 'c', 'c'], $setting->carrier_name));
                                    $logoPath = "uploads/logos/cargo/{$cargoSlug}.png";
                                @endphp
                                <div class="w-16 h-16 bg-white border border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shrink-0 shadow-inner">
                                    @if(file_exists(public_path($logoPath)))
                                        <img src="{{ asset($logoPath) }}?v={{ time() }}" class="max-w-full max-h-full object-contain">
                                    @else
                                        <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    @endif
                                </div>
                                <input type="file" name="logo" accept="image/*" class="flex-1 text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_test_mode" value="1" {{ $setting->is_test_mode ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Test Modu (Sandbox)</span>
                        </label>
                    </div>

                    @if(auth()->user()->hasPermission('settings.edit'))
                    <button type="submit" class="w-full bg-slate-900 text-white font-black py-3 rounded-xl text-xs uppercase tracking-widest hover:bg-black transition shadow-lg mt-2">
                        Değişiklikleri Kaydet
                    </button>
                    @else
                    <div class="w-full bg-slate-100 text-slate-400 font-black py-3 rounded-xl text-center text-[10px] uppercase tracking-widest border border-slate-200 mt-2">
                        DÜZENLEME YETKİNİZ YOK
                    </div>
                    @endif
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
