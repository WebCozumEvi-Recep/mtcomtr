@extends('layouts.admin')

@section('title', 'Mesaj Ayarları')

@section('content')
<div class="fade-in-up">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Mesaj Ayarları</h1>
            <p class="text-slate-500 font-medium">WhatsApp üzerinden müşterilere gönderilecek mesaj kalıplarını buradan düzenleyebilirsiniz.</p>
        </div>
    </div>

    <form action="{{ route('admin.settings.messages.update') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 gap-8">
            @foreach($templates as $index => $template)
            <div class="premium-card p-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 flex items-center justify-center text-brand-600">
                        @if($template->key == 'order_confirmation')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @elseif($template->key == 'shipping_info')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">{{ $template->name }}</h3>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">{{ $template->key }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <input type="hidden" name="templates[{{ $index }}][id]" value="{{ $template->id }}">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Mesaj İçeriği</label>
                        <textarea name="templates[{{ $index }}][content]" rows="8" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition shadow-sm">{{ $template->content }}</textarea>
                    </div>
                    
                    <div class="p-6 bg-slate-100/50 rounded-[2rem] border border-slate-200/50">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Kullanılabilir Değişkenler & Örnek Çıktılar
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[CUSTOMER_NAME]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Müşteri Adı Soyadı (BÜYÜK HARF)</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: RECEP YILMAZ</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[PRODUCT_LIST]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Ürün ve Adet Listesi (Alt Alta)</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: Ürün Adı (2 Adet)</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[DOMAIN_NAME]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Siparişin Geldiği Web Sitesi</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: nipponsampuan.com</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[ORDER_NUMBER]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Sipariş Takip Numarası</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: TS-20260507-0096</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[TOTAL_PRICE]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Sipariş Toplam Tutarı</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: 1.598,00</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[PAYMENT_METHOD]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Ödeme Tipi (Kapıda vb.)</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: Kapıda Ödeme</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[ADDRESS]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Teslimat Adresi ve İlçe/İl</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: Örnek Mah. No:1...</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[PACKAGE_NAME]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Seçilen Kampanya/Paket Adı</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: 4 Al 2 Öde Seti</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[CARGO_FIRM]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Kargo Şirketi Adı</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: Yurtiçi Kargo</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[TRACKING_NUMBER]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Kargo Takip Numarası</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: 123456789</p>
                            </div>
                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm group hover:border-brand-300 transition-colors">
                                <code class="text-[10px] font-bold text-brand-600 block mb-1">[TRACKING_URL]</code>
                                <p class="text-[9px] text-slate-500 leading-tight">Kargo Takip Web Bağlantısı</p>
                                <p class="text-[8px] text-slate-400 italic mt-1 font-medium">Örn: kargo.com/takip/...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-brand-600 text-white font-black px-10 py-4 rounded-[2rem] text-sm uppercase tracking-widest shadow-2xl shadow-brand-600/30 hover:bg-brand-700 hover:-translate-y-1 transition active:scale-95">
                DEĞİŞİKLİKLERİ KAYDET
            </button>
        </div>
    </form>
</div>
@endsection
