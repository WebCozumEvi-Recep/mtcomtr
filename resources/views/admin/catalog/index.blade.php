@extends('layouts.admin')

@section('title', 'Katalog & Envanter')

@section('content')
<div class="flex flex-col gap-6 fade-in-up">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Ürünler</h2>
                <button 
                    x-data="favoriteManager" 
                    @click="toggle('admin.catalog', 'Ürünler', '{{ route('admin.catalog') }}', 'box')"
                    class="favorite-btn"
                    style="display: inline-flex !important; min-width: 32px; min-height: 32px;"
                    title="Hızlı İşlemlere Ekle/Kaldır"
                >
                    <svg data-favorite-id="admin.catalog" class="w-6 h-6 {{ (auth()->user()->favorites && collect(auth()->user()->favorites)->contains('id', 'admin.catalog')) ? 'text-amber-400' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-slate-500 font-medium">Satıştaki ürünlerin genel yönetimi</p>
        </div>
        @if(auth()->user()->hasPermission('products.edit'))
        <div class="flex gap-2">
            <a href="{{ route('admin.catalog.create') }}" class="bg-brand-600 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-brand-700 transition shadow-md flex items-center" style="color:white!important;">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span style="color:white;">Yeni Ürün Ekle</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Search Area -->
    <div class="premium-card p-4">
        <form action="{{ route('admin.catalog') }}" method="GET" class="flex gap-4 items-center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ürün adı, SKU veya API ürün kimliği ara..." class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <button type="submit" class="bg-slate-800 text-white font-bold px-6 py-2 rounded-xl text-sm hover:bg-black transition" style="color:white!important;">Ara</button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.catalog') }}" class="text-slate-400 hover:text-red-500 transition" title="Aramayı Temizle">
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

    <!-- Data Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500">
                    <tr>
                        <th class="px-6 py-4 border-b border-slate-200">Görsel</th>
                        <th class="px-6 py-4 border-b border-slate-200">Ürün Adı & SKU</th>
                        <th class="px-6 py-4 border-b border-slate-200">Fiyat & Stok</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden">
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $product->name }}</div>
                                <div class="text-xs text-slate-400">SKU: {{ $product->sku ?? 'Belirtilmedi' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">API ürün kimliği: {{ $product->api_product_id ?: '—' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-brand-700">{{ number_format($product->price, 2) }} ₺</span>
                                <div class="text-xs font-semibold text-slate-500 mt-1">Stok: {{ $product->stock_quantity }} adet</div>
                            </td>
                             <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(auth()->user()->hasPermission('products.edit'))
                                        <a href="{{ route('admin.catalog.edit', $product) }}" class="p-2 bg-slate-50 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="Düzenle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('products.delete'))
                                        <form action="{{ route('admin.catalog.destroy', $product) }}" method="POST" onsubmit="return confirm('Bu ürünü silmek istediğinize emin misiniz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-red-50 text-red-400 hover:text-red-600 hover:bg-red-100 rounded-lg transition" title="Sil">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif

                                    @if(!auth()->user()->hasPermission('products.edit') && !auth()->user()->hasPermission('products.delete'))
                                        <span class="text-slate-400 text-[10px] font-bold uppercase">Sadece Görüntüleme</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500 font-medium">
                                Sistemde listelenecek ürün bulunamadı.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
