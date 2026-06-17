@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <a href="{{ route('admin.roles') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 font-bold mb-6 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Geri Dön
    </a>

    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">{{ $role->exists ? 'ROLÜ DÜZENLE' : 'YENİ ROL OLUŞTUR' }}</h2>
            <p class="text-slate-500 font-bold">Yetki setlerini belirleyin.</p>
        </div>
    </div>

    <form action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" method="POST" class="space-y-8 pb-20">
        @csrf
        @if($role->exists) @method('PUT') @endif

        <div class="premium-card p-8 md:p-12">
            <div class="mb-12">
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4">ROL ADI / TANIMI</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                        <svg class="w-6 h-6 text-slate-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" 
                           class="w-full bg-slate-50 border-2 border-slate-100 rounded-3xl pl-16 pr-6 py-6 outline-none focus:border-brand-500 focus:bg-white transition-all font-black text-2xl tracking-tight text-slate-900 shadow-inner" 
                           placeholder="Örn: Müşteri Hizmetleri, Depo Sorumlusu..." required>
                </div>
            </div>

            <div class="flex items-center gap-4 mb-8 border-b border-slate-100 pb-6">
                <div class="w-12 h-12 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-600 shadow-sm border border-brand-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase">YETKİ MATRİSİ</h3>
                    <p class="text-[11px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Modül bazlı yetkilendirme standartlarını belirleyin</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-10">
                @php
                    $modules = [
                        'dashboard' => [
                            'label' => 'Genel Bakış (Dashboard)',
                            'description' => 'Satış istatistikleri, finansal özet ve sistem sağlığı',
                            'icon' => 'chart-bar',
                            'permissions' => [
                                'view' => 'Görüntüleme'
                            ]
                        ],
                        'orders' => [
                            'label' => 'Sipariş Yönetimi',
                            'description' => 'Sipariş listesi, detaylar, kargo ve operasyonel işlemler',
                            'icon' => 'shopping-cart',
                            'permissions' => [
                                'view' => 'Görüntüleme',
                                'edit' => 'Ekleme / Düzenleme',
                                'delete' => 'Sipariş Silme',
                                'edit_address' => 'Adres Güncelleme',
                                'print' => 'Yazdırma / Barkod',
                                'send_cargo' => 'Kargo Gönderimi',
                                'export' => 'Dışa Aktarma (Excel)'
                            ]
                        ],
                        'products' => [
                            'label' => 'Ürün & Paket Kataloğu',
                            'description' => 'Ürün listesi, paket oluşturma ve stok yönetimi',
                            'icon' => 'cube',
                            'permissions' => [
                                'view' => 'Görüntüleme',
                                'edit' => 'Ekleme / Düzenleme',
                                'delete' => 'Silme Yetkisi'
                            ]
                        ],
                        'domains' => [
                            'label' => 'Domain & Satış Ağları',
                            'description' => 'Funnel alan adları, Cloudflare ve teknik altyapı',
                            'icon' => 'globe',
                            'permissions' => [
                                'view' => 'Görüntüleme',
                                'edit' => 'Ekleme / Düzenleme',
                                'delete' => 'Silme Yetkisi'
                            ]
                        ],
                        'brands' => [
                            'label' => 'Marka Yönetimi',
                            'description' => 'Kurumsal marka tanımlamaları ve organizasyon',
                            'icon' => 'office-building',
                            'permissions' => [
                                'view' => 'Görüntüleme',
                                'edit' => 'Ekleme / Düzenleme',
                                'delete' => 'Silme Yetkisi'
                            ]
                        ],
                        'risk' => [
                            'label' => 'Risk & Sahtecilik Analizi',
                            'description' => 'Fraud skorları, kara liste ve şüpheli işlem takibi',
                            'icon' => 'shield-check',
                            'permissions' => [
                                'view' => 'Görüntüleme',
                                'edit' => 'Kara Liste Yönetimi'
                            ]
                        ],
                        'users' => [
                            'label' => 'Kullanıcılar & Roller',
                            'description' => 'Ekip üyeleri ve yetki seviyeleri yönetimi',
                            'icon' => 'users',
                            'permissions' => [
                                'view' => 'Görüntüleme',
                                'edit' => 'Ekleme / Düzenleme',
                                'delete' => 'Silme Yetkisi'
                            ]
                        ],
                        'settings' => [
                            'label' => 'Sistem & Kargo Ayarları',
                            'description' => 'API anahtarları, site kimliği ve altyapı servisleri',
                            'icon' => 'cog',
                            'permissions' => [
                                'view' => 'Görüntüleme',
                                'edit' => 'Ayarları Güncelleme'
                            ]
                        ],
                        'cargo' => [
                            'label' => 'Kargo Mutabakatı',
                            'description' => 'Teslim edilen kargoların ödeme ve hakediş kontrolü',
                            'icon' => 'credit-card',
                            'permissions' => [
                                'view' => 'Görüntüleme',
                                'edit' => 'Mutabakat İşlemi (Onay/İptal)'
                            ]
                        ]
                    ];
                @endphp

                @foreach($modules as $mKey => $module)
                <div class="relative pl-12 group/module">
                    <!-- Module Indicator Line -->
                    <div class="absolute left-6 top-10 bottom-0 w-px bg-slate-100 group-last/module:hidden"></div>
                    
                    <div class="flex items-start gap-4 mb-6">
                        <div class="absolute left-0 top-0 w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-md text-slate-400 border border-slate-100 group-hover/module:border-brand-300 group-hover/module:text-brand-600 transition-all">
                            @if($module['icon'] == 'chart-bar')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            @elseif($module['icon'] == 'shopping-cart')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            @elseif($module['icon'] == 'cube')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            @elseif($module['icon'] == 'globe')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                            @elseif($module['icon'] == 'users')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            @elseif($module['icon'] == 'cog')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            @elseif($module['icon'] == 'shield-check')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            @elseif($module['icon'] == 'credit-card')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            @endif
                        </div>
                        <div class="pt-1">
                            <h4 class="font-black text-slate-900 tracking-tight text-base">{{ $module['label'] }}</h4>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-tighter">{{ $module['description'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($module['permissions'] as $pKey => $pLabel)
                        <label class="relative flex items-center gap-4 bg-slate-50 border-2 border-transparent hover:border-brand-200 hover:bg-white p-4 rounded-2xl cursor-pointer transition-all duration-200 group/perm shadow-sm">
                            <div class="shrink-0">
                                <input type="checkbox" name="permissions[]" value="{{ $mKey }}.{{ $pKey }}" 
                                       {{ in_array($mKey.'.'.$pKey, old('permissions', $role->permissions ?? [])) ? 'checked' : '' }} 
                                       class="w-6 h-6 rounded-lg border-slate-200 text-brand-600 focus:ring-4 focus:ring-brand-500/10 transition-all cursor-pointer">
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-slate-700 group-hover/perm:text-brand-900 transition-colors">{{ $pLabel }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase group-hover/perm:text-brand-600/60">{{ $mKey }}.{{ $pKey }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <button type="submit" class="w-full md:w-auto bg-slate-900 text-white font-black px-16 py-6 rounded-3xl shadow-2xl hover:bg-brand-600 hover:-translate-y-1 transition-all active:scale-95 text-lg uppercase tracking-widest">
                {{ $role->exists ? 'Değişiklikleri Kaydet' : 'Rolü Oluştur ve Kaydet' }}
            </button>
        </div>
    </form>
</div>
@endsection
