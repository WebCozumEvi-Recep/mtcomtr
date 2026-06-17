@extends('layouts.admin')

@section('title', $order->order_number)

@section('content')
@php
    $lastShipment = $order->shipments()->latest()->first();
@endphp


<div class="fade-in-up">


    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm font-medium text-slate-500 no-print">
        <a href="{{ route('admin.orders') }}" class="hover:text-slate-700">Siparişler</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">Sipariş Detayı</span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content -->
        <div class="flex-1 space-y-6">
            <!-- Order Status & Actions -->
            <div class="premium-card p-6 flex flex-wrap items-center justify-between gap-4 no-print">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-brand-50 rounded-2xl text-brand-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-slate-900 tracking-tight">{{ $order->order_number }}</h1>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-0.5">{{ $order->created_at->format('d.m.Y, H:i') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        @if(auth()->user()->hasPermission('orders.print'))
                        <a href="{{ route('admin.orders.bulk-print', ['ids' => $order->id]) }}" target="_blank" class="bg-white border {{ $order->is_printed ? 'border-green-200' : 'border-slate-200' }} text-slate-700 font-bold px-4 py-2.5 rounded-xl text-xs hover:bg-slate-50 transition shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4 {{ $order->is_printed ? 'text-green-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            {{ $order->is_printed ? 'Yeniden Yazdır' : 'Yazdır' }}
                        </a>
                        @endif
                        @if($order->is_printed)
                            <div class="bg-green-50 text-green-600 p-1.5 rounded-full border border-green-100 shadow-sm" title="Bu sipariş yazdırıldı">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        @endif
                    </div>
                    @if(auth()->user()->hasPermission('orders.edit'))
                    <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="flex items-center gap-3 border-l pl-3 ml-3 border-slate-200" onsubmit="return confirmAction(event, 'Sipariş durumunu güncellemek istediğinize emin misiniz?')">
                        @csrf
                        <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending (Beklemede)</option>
                            <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed (Ön Onaylı)</option>
                            <option value="yeni" {{ $order->status == 'yeni' ? 'selected' : '' }}>Yeni</option>
                            <option value="aranacak" {{ $order->status == 'aranacak' ? 'selected' : '' }}>Aranacak</option>
                            <option value="onaylandı" {{ $order->status == 'onaylandı' ? 'selected' : '' }}>Onaylandı</option>
                            <option value="iptal" {{ $order->status == 'iptal' ? 'selected' : '' }}>İptal</option>
                            <option value="kargoya_verildi" {{ $order->status == 'kargoya_verildi' ? 'selected' : '' }}>Kargoya Verildi</option>
                            <option value="teslim_edildi" {{ $order->status == 'teslim_edildi' ? 'selected' : '' }}>Teslim Edildi</option>
                            <option value="iade" {{ $order->status == 'iade' ? 'selected' : '' }}>İade</option>
                        </select>
                        <button type="submit" class="bg-slate-900 text-white font-bold px-5 py-2.5 rounded-xl text-xs hover:bg-black transition shadow-sm">
                            Güncelle
                        </button>
                    </form>
                    @endif
                    @if(auth()->user()->hasPermission('orders.delete'))
                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, 'DİKKAT: [target] numaralı siparişi sildiğinizde; sipariş bilgileri, ürün kalemleri ve istatistiklerden KALICI OLARAK silinecektir. Bu işlem geri alınamaz!', '{{ $order->order_number }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-50 text-red-600 font-bold px-4 py-2.5 rounded-xl text-xs hover:bg-red-100 transition shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Sil
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            @if($order->is_api)
            <div class="premium-card p-6 border-violet-100 bg-gradient-to-br from-violet-50/50 to-white no-print">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-xs font-black text-violet-900 uppercase tracking-widest">Harici API siparişi</h3>
                        <dl class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                            <div><dt class="text-[10px] font-bold text-slate-400 uppercase">is_api</dt><dd class="font-bold text-slate-800">Evet</dd></div>
                            <div><dt class="text-[10px] font-bold text-slate-400 uppercase">Gönderim (API)</dt><dd class="font-bold text-slate-800">{{ $order->api_sent_at ? $order->api_sent_at->format('d.m.Y H:i') : '—' }}</dd></div>
                            <div><dt class="text-[10px] font-bold text-slate-400 uppercase">Harici sipariş no</dt><dd class="font-mono font-bold text-slate-800">{{ $order->api_order_id ?: '—' }}</dd></div>
                        </dl>
                    </div>
                    @if(!$order->api_approved && auth()->user()->hasPermission('orders.edit'))
                    <form action="{{ route('admin.orders.api-approve', $order) }}" method="POST" onsubmit="return confirmAction(event, 'Bu harici API siparişini panelden onaylamak istediğinize emin misiniz?')">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto bg-violet-600 text-white font-black px-5 py-3 rounded-xl text-xs uppercase tracking-widest hover:bg-violet-700 transition shadow-md">
                            API onayını ver
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endif

            @php
                $operatorUpsells = $order->domain->upsellOffers()
                    ->where('is_active', true)
                    ->whereIn('display_timing', ['operator', 'both'])
                    ->get();
            @endphp

            @if($operatorUpsells->isNotEmpty() && !$order->has_upsell && auth()->user()->hasPermission('orders.edit'))
            <div class="premium-card p-6 bg-gradient-to-br from-orange-50 to-white border-orange-100 no-print">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-orange-100 rounded-2xl text-orange-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">SATIŞI ARTIR: Upsell Önerileri</h3>
                        <p class="text-xs text-orange-600 font-bold">Müşteriyle görüşürken bu fırsatları teklif edin!</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($operatorUpsells as $up)
                    <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-orange-100 shadow-sm group hover:border-orange-300 transition-all">
                        <div class="min-w-0">
                            <div class="text-xs font-black text-slate-900 uppercase truncate">{{ $up->name }}</div>
                            <div class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $up->title }}</div>
                            <div class="mt-2 text-sm font-black text-orange-600">{{ number_format($up->discount_price, 2) }} ₺</div>
                        </div>
                        <button type="button" onclick="applyAdminUpsell({{ $up->id }})" class="shrink-0 bg-orange-600 text-white font-black px-4 py-2 rounded-xl text-[10px] uppercase tracking-widest hover:bg-orange-700 transition shadow-md shadow-orange-600/20">
                            EKLE
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

            <script>
                function applyAdminUpsell(offerId) {
                    Swal.fire({
                        title: 'Emin misiniz?',
                        text: 'Bu upsell teklifini siparişe eklemek istediğinize emin misiniz? Toplam tutar güncellenecektir.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#16a34a',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Evet, Ekle',
                        cancelButtonText: 'Vazgeç',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-3xl',
                            confirmButton: 'rounded-xl px-6 py-3 font-bold',
                            cancelButton: 'rounded-xl px-6 py-3 font-bold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const loader = document.getElementById('loading-overlay');
                            if (loader) loader.classList.remove('hidden');

                            fetch('{{ route("admin.orders.upsell.add", $order) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ upsell_offer_id: offerId })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if(data.success) {
                                    location.reload();
                                } else {
                                    if (loader) loader.classList.add('hidden');
                                    Swal.fire('Hata', data.message || 'Hata oluştu.', 'error');
                                }
                            })
                            .catch(() => {
                                if (loader) loader.classList.add('hidden');
                                Swal.fire('Bağlantı Hatası', 'İşlem sırasında bir sorun oluştu.', 'error');
                            });
                        }
                    });
                }
            </script>
            @endif

            <!-- Items -->
            <div class="premium-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 no-print">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Sipariş İçeriği</h3>
                </div>
                <div class="p-6">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="pb-4">Ürün Detayı</th>
                                <th class="pb-4 text-center">ADET</th>
                                <th class="pb-4 text-right">Fiyat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php 
                                $offer = $order->offer;
                                $items = ($offer && $offer->items->isNotEmpty()) ? $offer->items : collect();
                            @endphp
                            
                            @if($items->isNotEmpty())
                                @foreach($items as $item)
                                <tr>
                                    <td class="py-4">
                                        <div class="flex items-center gap-4">
                                            @if($item->product && $item->product->image_url)
                                                <img src="{{ $item->product->image_url }}" class="w-12 h-12 rounded-xl object-cover border border-slate-100">
                                            @else
                                                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-bold text-slate-900 text-sm">{{ $item->product->name ?? 'Ürün' }}</div>
                                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">PAKET: {{ $offer->offer_name ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 text-center">
                                        <span class="text-sm font-black text-slate-900">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="py-4 text-right font-black text-slate-900 text-sm">
                                        {{ number_format($item->price, 2) }} ₺
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                @php $mainProduct = $order->domain->products->first(); @endphp
                                <tr>
                                    <td class="py-6">
                                        <div class="flex items-center gap-6">
                                            @if($mainProduct && $mainProduct->image_url)
                                                <img src="{{ $mainProduct->image_url }}" class="w-20 h-20 rounded-2xl object-cover border border-slate-100 shadow-sm">
                                            @elseif($offer && $offer->offer_image)
                                                <img src="{{ asset('uploads/offers/' . $offer->offer_image) }}" class="w-20 h-20 rounded-2xl object-cover border border-slate-100 shadow-sm">
                                            @else
                                                <div class="w-20 h-20 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-black text-slate-900 text-lg tracking-tight">{{ $mainProduct->name ?? 'Ürün' }}</div>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="bg-indigo-50 text-indigo-700 text-[10px] px-2 py-0.5 rounded-full font-black uppercase tracking-wider">PAKET: {{ $offer->offer_name ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-6 text-center">
                                        <div class="inline-flex flex-col items-center">
                                            <span class="text-2xl font-black text-slate-900">{{ $offer->quantity ?? 1 }}</span>
                                        </div>
                                    </td>
                                    <td class="py-6 text-right font-black text-slate-900 text-xl">{{ number_format($order->grand_total, 2) }} ₺</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="pt-8 text-right text-sm font-black text-slate-400 uppercase tracking-widest">Genel Toplam</td>
                                <td class="pt-8 text-right">
                                    <span class="text-3xl font-black text-brand-600 whitespace-nowrap">{{ number_format($order->grand_total, 2) }} ₺</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            <div class="premium-card p-6 no-print">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">İşlem Geçmişi / Notlar</h3>
                    <button @click="$refs.noteForm.classList.toggle('hidden')" class="text-[10px] font-black text-brand-600 hover:text-brand-800 uppercase tracking-widest flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        YENİ NOT EKLE
                    </button>
                </div>
                
                <div x-ref="noteForm" class="mb-4 hidden fade-in">
                    <form action="{{ route('admin.orders.add-note', $order) }}" method="POST">
                        @csrf
                        <textarea name="note" rows="3" placeholder="Sipariş için notunuzu buraya yazın..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 mb-2 font-medium" required></textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-brand-600 text-white font-black px-6 py-2 rounded-xl text-[10px] uppercase tracking-widest hover:bg-brand-700 transition shadow-md">NOTU KAYDET</button>
                        </div>
                    </form>
                </div>

                <div class="bg-slate-50 rounded-2xl p-4 font-mono text-xs text-slate-600 leading-relaxed whitespace-pre-line border border-slate-100 min-h-[100px]">
                    {{ $order->order_notes ?: 'Henüz bir not eklenmemiş.' }}
                </div>

                @if($order->histories->isNotEmpty())
                <div class="mt-6 space-y-3">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2">Resmi İşlem Kayıtları</h4>
                    @foreach($order->histories->sortByDesc('created_at') as $history)
                    <div class="flex items-start gap-3 text-xs">
                        <div class="shrink-0 mt-1">
                            @if($history->type == 'whatsapp_message')
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                            @elseif($history->type == 'status_change')
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                            @else
                                <div class="w-2 h-2 rounded-full bg-slate-300"></div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800">{{ $history->message }}</span>
                                <span class="text-[10px] text-slate-400">{{ $history->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                            <div class="text-[10px] text-slate-400 font-medium">İşlemi Yapan: {{ $history->user->name ?? 'Sistem' }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($order->upsells->where('status', 'accepted')->isNotEmpty())
                <div class="mt-4 p-4 bg-orange-50 border border-orange-100 rounded-2xl">
                    <h4 class="text-[10px] font-black text-orange-800 uppercase tracking-widest mb-2">Uygulanan Upsell Teklifleri</h4>
                    <div class="space-y-2">
                        @foreach($order->upsells->where('status', 'accepted') as $u)
                        <div class="flex items-center justify-between text-xs font-bold text-orange-950">
                            <span>{{ $u->offer->name ?? 'Teklif' }} (+₺{{ number_format($u->added_amount, 2) }})</span>
                            <span class="text-[10px] text-orange-700/60 font-medium">
                                {{ $u->operator ? $u->operator->full_name : 'Müşteri (Self-Service)' }} 
                                • {{ $u->accepted_at ? $u->accepted_at->format('d.m.Y H:i') : $u->created_at->format('d.m.Y H:i') }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar / Customer & Cargo Info -->
        <div class="w-full lg:w-96 space-y-6 no-print">
            <!-- Domain Source Badge -->
            <div class="p-4 rounded-3xl bg-slate-900 text-white flex items-center justify-between no-print">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                    </div>
                    <div>

                        <div class="text-sm font-bold truncate max-w-[180px]">{{ $order->domain->domain_name ?? '-' }}</div>
                        @if($order->domain && $order->domain->brand)
                            <div class="text-[10px] font-black text-brand-400 uppercase tracking-tighter mt-0.5">
                                {{ $order->domain->brand->name }}
                            </div>
                        @endif
                    </div>
                </div>
                <a href="https://{{ $order->domain->domain_name }}" target="_blank" class="text-slate-400 hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>

            <!-- Customer Info -->
            <div class="premium-card overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Müşteri Bilgileri</h3>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div>
                                <div class="font-black text-slate-900">{{ $order->customer->full_name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 font-bold uppercase">{{ $order->customer->phone ?? '-' }}</div>
                            </div>
                        </div>
                        @if($order->ip_address)
                            <div class="text-right">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">IP ADRESİ</div>
                                <div class="text-xs font-mono font-bold text-slate-600">{{ $order->ip_address }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Teslimat Adresi</div>
                                @if((($order->status != 'kargoya_verildi' || $order->status == 'iptal')) && auth()->user()->hasPermission('orders.edit_address'))
                                    <button type="button" 
                                            onclick="handleAddressEdit()"
                                            class="text-[10px] font-black text-brand-600 uppercase hover:underline no-print">
                                        Düzenle
                                    </button>
                                @endif
                            </div>
                            <div id="address-display" class="text-sm font-bold text-slate-900 leading-relaxed">
                                <span id="display-address">{{ $order->address }}</span><br>
                                <span id="display-district">{{ $order->district }}</span> / <span id="display-city">{{ $order->city }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 no-print">
                            <div class="flex gap-2">
                                <a href="tel:{{ $order->customer->phone }}" class="flex-1 bg-slate-100 text-slate-700 font-black py-3 rounded-2xl text-center text-[10px] uppercase tracking-wide hover:bg-slate-200 transition">Ara</a>
                                <button type="button" @click="$dispatch('open-whatsapp-modal')" class="flex-1 bg-green-500 text-white font-black py-3 rounded-2xl text-center text-[10px] uppercase tracking-wide hover:bg-green-600 transition shadow-lg shadow-green-500/20">WhatsApp</button>
                            </div>
                            
                             @if($order->customer && auth()->user()->hasPermission('risk.edit'))
                                <form action="{{ route('admin.customers.toggle-blacklist', $order->customer) }}" method="POST" onsubmit="return confirmAction(event, 'Bu müşteriyi kara listeye eklemek (veya listeden çıkarmak) istediğinize emin misiniz?')">
                                    @csrf
                                    <button type="submit" class="w-full {{ $order->customer->is_blacklisted ? 'bg-slate-100 text-slate-600' : 'bg-red-50 text-red-600' }} font-black py-3 rounded-2xl text-center text-[10px] uppercase tracking-wide hover:opacity-80 transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                        {{ $order->customer->is_blacklisted ? 'ENGELİ KALDIR' : 'KARA LİSTEYE EKLE (ENGELLE)' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cargo Management -->
            <div class="premium-card overflow-hidden no-print">
                <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Kargo & Lojistik</h3>
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <div class="p-6">
                    @if($order->tracking_number)
                        <div class="bg-indigo-50 border border-indigo-100 p-6 rounded-[2rem] mb-4 shadow-inner">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">TAŞIYICI FİRMA</div>
                                    <div class="flex items-center gap-3">
                                        @php
                                            $cargoSlug = strtolower(str_replace([' ', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ş', 'Ş', 'ö', 'Ö', 'ç', 'Ç'], ['-', 'i', 'i', 'g', 'g', 'u', 'u', 's', 's', 'o', 'o', 'c', 'c'], $order->cargo_firm));
                                            $logoPath = "uploads/logos/cargo/{$cargoSlug}.png";
                                        @endphp
                                        @if(file_exists(public_path($logoPath)))
                                            <img src="{{ asset($logoPath) }}" class="h-8 object-contain">
                                        @endif
                                        <div class="text-xl font-black text-indigo-900 uppercase">{{ $order->cargo_firm }}</div>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2 items-end">
                                    <div class="bg-indigo-600 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-tighter shadow-md">KARGODA</div>
                                    @if($lastShipment && $lastShipment->raw_api_response)
                                        <button onclick="showCargoDataPreview()" class="text-[9px] font-black text-indigo-500 uppercase tracking-widest hover:text-indigo-700 transition">API DATA PREVIEW ⓘ</button>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">TAKİP NUMARASI</div>
                                    @if($order->cargo_firm === 'yurtici')
                                        <a href="http://musteri.yurticikargo.com/customer/selfservis/selfservis_gonderi_rapor.asp?ssfldvn=99&sskurkod=888328027&refnumber={{ $order->tracking_number }}" target="_blank" class="text-lg font-mono font-black text-indigo-800 tracking-wider hover:text-indigo-600 transition underline decoration-dotted decoration-2 underline-offset-4">
                                            {{ $order->tracking_number }}
                                        </a>
                                    @else
                                        <div class="text-lg font-mono font-black text-indigo-800 tracking-wider">{{ $order->tracking_number }}</div>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    @if(auth()->user()->hasPermission('orders.print'))
                                    <a href="{{ route('admin.orders.barcode', $order) }}" target="_blank" class="bg-white text-indigo-600 border-2 border-indigo-200 font-extrabold py-4 rounded-2xl text-[10px] uppercase tracking-widest flex items-center justify-center gap-3 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition shadow-lg active:scale-95">
                                        BARKODU YAZDIR
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('orders.send_cargo'))
                                    <form action="{{ route('admin.orders.cancel-cargo', $order) }}" method="POST" onsubmit="return confirmAction(event, 'Kargo gönderimini iptal etmek istediğinize emin misiniz? Bu işlem takip numarasını silecek ve siparişi tekrar gönderilebilir hale getirecektir.')">
                                        @csrf
                                        <button type="submit" class="w-full bg-indigo-100 text-indigo-600 font-extrabold py-4 rounded-2xl text-[10px] uppercase tracking-widest hover:bg-rose-500 hover:text-white transition shadow-lg active:scale-95">
                                            İPTAL ET
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        @if(auth()->user()->hasPermission('orders.send_cargo'))
                        <form action="{{ route('admin.orders.send-cargo', $order) }}" method="POST" class="space-y-5" onsubmit="return confirmAction(event, 'Siparişi kargoya vermek ve barkod oluşturmak istediğinize emin misiniz?')">
                            @csrf
                            <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Tedarikçi Kargo Firması</label>
                                <select name="cargo_firm" required class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition shadow-sm appearance-none">
                                    <option value="">Lütfen Firma Seçin...</option>
                                    @foreach($cargoSettings as $cs)
                                        <option value="{{ $cs->carrier_name }}">{{ $cs->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-brand-600 text-white font-black py-5 rounded-[2rem] text-[11px] uppercase tracking-widest shadow-2xl shadow-brand-600/30 hover:bg-brand-700 hover:-translate-y-1 transition active:scale-95 flex items-center justify-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                KARGOYA VER VE BARKOD OLUŞTUR
                            </button>
                        </form>
                        @else
                            <div class="p-8 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px] border-2 border-dashed border-slate-100 rounded-[2rem]">
                                KARGO GÖNDERİM YETKİNİZ BULUNMUYOR
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Risk Badge -->
            @if($order->risk_level == 'high')
            <div class="p-5 rounded-[32px] bg-red-100/50 border border-red-200/50 flex items-start gap-4 text-red-900 no-print">
                <div class="p-2 bg-red-600 rounded-xl text-white">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <div class="text-xs font-black uppercase tracking-tighter">Yüksek Riskli İşlem</div>
                    <div class="text-[10px] font-bold opacity-60 leading-tight mt-0.5">Bu sipariş şüpheli görünüyor (Fraud Skoru: {{ $order->fraud_score }}). Onaylamadan önce müşteriyle görüşün.</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($lastShipment && $lastShipment->raw_api_response)
<!-- Data Preview Modal (Improved) -->
<div id="cargoDataModal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[99999] flex items-center justify-center p-4 md:p-10">
    <div class="bg-white rounded-[2.5rem] w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden shadow-[0_0_100px_-20px_rgba(0,0,0,0.5)] animate-in zoom-in duration-300">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-white">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-brand-50 rounded-2xl text-brand-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </div>
                <div>
                    <h4 class="text-lg font-black text-slate-900 uppercase tracking-tighter">Entegrasyon Yanıtı</h4>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kargo Servisi Ham Veri Çıktısı (Raw Data)</p>
                </div>
            </div>
            <button onclick="closeCargoDataPreview()" class="p-2 bg-slate-50 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="flex-1 p-8 overflow-y-auto bg-slate-50">
            <div class="relative">
                <div class="absolute top-4 right-4 bg-emerald-500/10 text-emerald-600 text-[9px] font-black px-2 py-1 rounded uppercase tracking-widest">JSON Format</div>
                <pre class="bg-[#0f172a] text-emerald-400 p-8 rounded-[2rem] text-[13px] font-mono leading-relaxed overflow-x-auto shadow-2xl border border-slate-800">{{ json_encode(json_decode($lastShipment->raw_api_response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        <div class="p-8 bg-white border-t border-slate-100 flex justify-end">
            <button onclick="closeCargoDataPreview()" class="bg-slate-900 text-white font-black px-10 py-4 rounded-2xl text-xs uppercase tracking-widest hover:bg-black hover:-translate-y-0.5 transition shadow-xl active:scale-95">
                Pencereyi Kapat
            </button>
        </div>
    </div>
</div>

<script>
    function showCargoDataPreview() {
        document.getElementById('cargoDataModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeCargoDataPreview() {
        document.getElementById('cargoDataModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    // Close on click outside
    document.getElementById('cargoDataModal').addEventListener('click', function(e) {
        if (e.target === this) closeCargoDataPreview();
    });
</script>
@endif

<!-- Loading Overlay -->
<div id="loading-overlay" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[99999] flex flex-col items-center justify-center text-white">
    <div class="w-16 h-16 border-4 border-brand-500 border-t-transparent rounded-full animate-spin mb-4"></div>
    <div class="text-xl font-black uppercase tracking-widest text-center">İşlem Yapılıyor<br><span class="text-brand-400 text-sm">Lütfen Bekleyin</span></div>
</div>

</div>
@endsection

@section('extra_css')
<style>
    @media print {
        .no-print { display: none !important; }
    }
</style>

<!-- Address Edit Modal -->
<div id="addressEditModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 no-print">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in duration-200">
        <form id="addressUpdateForm" onsubmit="submitAddressUpdate(event)">
            @csrf
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Adres Bilgilerini Güncelle</h3>
                <button type="button" onclick="closeAddressModal()" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">İl</label>
                        <input type="text" name="city" id="input-city" value="{{ $order->city }}" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">İlçe</label>
                        <input type="text" name="district" id="input-district" value="{{ $order->district }}" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition shadow-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Tam Adres</label>
                    <textarea name="address" id="input-address" required rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition shadow-sm">{{ $order->address }}</textarea>
                </div>
            </div>
            <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex gap-3">
                <button type="button" onclick="closeAddressModal()" class="flex-1 px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-white border border-slate-200 transition">İptal</button>
                <button type="submit" id="submit-address-btn" class="flex-1 px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-widest text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-600/20 transition">Adresi Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
    function handleAddressEdit() {
        const isPrinted = {{ $order->is_printed ? 'true' : 'false' }};
        
        if (isPrinted) {
            Swal.fire({
                title: 'Dikkat: Sipariş Yazdırıldı!',
                text: 'Bu sipariş daha önce yazdırılmıştır. Eğer adres değiştirirseniz depoda ikinci kez sipariş gönderilme riski var. Bunu kontrol ettiniz mi?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Evet, Kontrol Ettim',
                cancelButtonText: 'Vazgeç',
                customClass: {
                    popup: 'rounded-3xl border-none shadow-2xl',
                    confirmButton: 'rounded-xl px-6 py-3 font-bold',
                    cancelButton: 'rounded-xl px-6 py-3 font-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    openAddressModal();
                }
            });
        } else {
            openAddressModal();
        }
    }

    function openAddressModal() {
        document.getElementById('addressEditModal').classList.remove('hidden');
    }

    function closeAddressModal() {
        document.getElementById('addressEditModal').classList.add('hidden');
    }

    function submitAddressUpdate(e) {
        e.preventDefault();
        const btn = document.getElementById('submit-address-btn');
        btn.disabled = true;
        btn.innerText = 'Güncelleniyor...';

        const formData = new FormData(e.target);
        
        fetch('{{ route('admin.orders.update-address', $order) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('display-address').innerText = data.new_address;
                document.getElementById('display-district').innerText = data.new_district;
                document.getElementById('display-city').innerText = data.new_city;
                
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                closeAddressModal();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata',
                    text: data.message
                });
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Hata',
                text: 'Bir sorun oluştu.'
            });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Adresi Güncelle';
        });
    }
</script>

<!-- WhatsApp Template Modal -->
<div x-data="{ 
    open: false, 
    selectedTemplateId: null,
    templates: @js($messageTemplates),
    order: @js($order),
    filledMessage: '',
    
    get selectedTemplate() {
        return this.templates.find(t => t.id == this.selectedTemplateId);
    },
    
    init() {
        if(this.templates.length > 0) {
            this.selectedTemplateId = this.templates[0].id;
            this.fillMessage();
        }
    },
    
    fillMessage() {
        let template = this.selectedTemplate;
        if(!template || !template.content) {
            this.filledMessage = 'Lütfen bir kalıp seçin veya kalıp içeriğinin dolu olduğundan emin olun.';
            return;
        }
        
        let content = template.content;
        
        let domainName = this.order.domain?.domain || 'Web Sitemiz';
        
        // Replacement with safety
        try {
            content = content.split('Teksat').join(domainName);
            content = content.split('teksat').join(domainName);

            // Multiple products logic with quantities
            let productLines = [];
            if (this.order.offer?.items?.length > 0) {
                productLines = this.order.offer.items.map(item => `${item.product?.name || 'Ürün'} (${item.quantity || 1} Adet)`);
            } else if (this.order.domain?.products?.length > 0) {
                productLines = [this.order.domain.products[0].name];
            } else {
                productLines = ['Ürün'];
            }
            
            let productString = productLines.join('\n');

            // Variables
            const vars = {
                '[CUSTOMER_NAME]': (this.order.customer?.full_name || this.order.customer?.name || '').toUpperCase(),
                '[ORDER_NUMBER]': this.order.order_number || '',
                '[PRODUCT_NAME]': productString,
                '[PRODUCT_LIST]': productString,
                '[PACKAGE_NAME]': this.order.offer?.offer_name || '-',
                '[TOTAL_PRICE]': parseFloat(this.order.grand_total || 0).toLocaleString('tr-TR', {minimumFractionDigits: 2}),
                '[PAYMENT_METHOD]': this.order.payment_status == 'pending' ? 'Kapıda Ödeme' : 'Ödendi',
                '[ADDRESS]': (this.order.address || '') + ' ' + (this.order.district || '') + ' / ' + (this.order.city || ''),
                '[CARGO_FIRM]': this.order.cargo_firm || '-',
                '[TRACKING_NUMBER]': this.order.tracking_number || '-',
                '[TRACKING_URL]': this.order.tracking_url || '-',
                '[DOMAIN_NAME]': domainName
            };
            
            for (const [key, value] of Object.entries(vars)) {
                content = content.split(key).join(value || '');
            }
            
            this.filledMessage = content;
        } catch (e) {
            console.error('WhatsApp Template Error:', e);
            this.filledMessage = template.content; // Fallback to raw content
        }
    },
    
    sendMessage() {
        const phone = this.order.customer?.phone.replace(/[^0-9]/g, '');
        const encodedMessage = encodeURIComponent(this.filledMessage);
        const url = `https://wa.me/${phone}?text=${encodedMessage}`;
        
        // Log to server first
        fetch('{{ route('admin.orders.whatsapp-log', $order) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                template_name: this.selectedTemplate.name,
                message: this.filledMessage
            })
        }).then(() => {
            window.open(url, '_blank');
            this.open = false;
            // Optionally reload or update history list
            setTimeout(() => location.reload(), 1000);
        });
    }
}" 
@open-whatsapp-modal.window="open = true"
x-show="open" 
x-cloak
class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4 no-print">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden animate-in zoom-in duration-200">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-100 rounded-2xl text-green-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">WhatsApp Mesaj Gönder</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Kalıp Seçin ve Gönderin</p>
                </div>
            </div>
            <button type="button" @click="open = false" class="text-slate-300 hover:text-slate-600 transition">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-8 space-y-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Mesaj Kalıbı</label>
                <div class="grid grid-cols-1 gap-3">
                    <template x-for="tpl in templates" :key="tpl.id">
                        <button type="button" 
                                @click="selectedTemplateId = tpl.id; fillMessage()"
                                :class="selectedTemplateId == tpl.id ? 'border-green-500 bg-green-50 text-green-700' : 'border-slate-200 hover:border-slate-300'"
                                class="flex items-center justify-between p-4 rounded-2xl border-2 transition text-left">
                            <span class="text-sm font-black" x-text="tpl.name"></span>
                            <div x-show="selectedTemplateId == tpl.id" class="w-5 h-5 bg-green-500 text-white rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <div class="space-y-3">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest">Mesaj Önizleme</label>
                <div class="bg-slate-50 border border-slate-200 rounded-[2rem] p-6 text-sm font-medium text-slate-700 whitespace-pre-line leading-relaxed italic shadow-inner min-h-[200px]" x-text="filledMessage"></div>
            </div>
        </div>
        <div class="p-8 bg-slate-50/50 border-t border-slate-100 flex gap-4">
            <button type="button" @click="open = false" class="flex-1 px-8 py-5 rounded-[2rem] text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-white border border-slate-200 transition">Vazgeç</button>
            <button type="button" @click="sendMessage()" class="flex-1 px-8 py-5 rounded-[2rem] text-xs font-black uppercase tracking-widest text-white bg-green-600 hover:bg-green-700 shadow-xl shadow-green-600/20 transition flex items-center justify-center gap-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                GÖNDER VE KAYDET
            </button>
        </div>
    </div>
</div>

@endsection
