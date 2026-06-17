@extends('layouts.admin')

@section('title', $product->exists ? 'Ürün Düzenle' : 'Yeni Ürün Ekle')

@section('content')
<div class="flex flex-col gap-6 fade-in-up max-w-4xl mx-auto">
    
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $product->exists ? 'Ürünü Düzenle' : 'Kataloğa Ürün Ekle' }}</h2>
            <p class="text-sm text-slate-500 font-medium">Platformunuzda satışa sunulacak ana ürünü yapılandırın</p>
        </div>
        <a href="{{ route('admin.catalog') }}" class="text-slate-500 font-bold hover:text-slate-800 transition">Geri Dön</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm">
            <ul class="list-disc pl-5 font-medium text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $product->exists ? route('admin.catalog.update', $product) : route('admin.catalog.store') }}" method="POST" enctype="multipart/form-data" class="premium-card p-8 flex flex-col gap-6">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Ürün Adı <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="Örn: Su Tasarrufu Sağlayan Eco Duş Başlığı">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Fiyat (₺) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="399.90">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Stok Miktarı <span class="text-red-500">*</span></label>
                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="1000">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Ürün / Varyant Kodu (SKU)</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="TEK-512">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">API ürün kimliği</label>
                <input type="text" name="api_product_id" value="{{ old('api_product_id', $product->api_product_id) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="Harici sistemdeki ürün ID veya kodu">
                <p class="text-xs text-slate-500 mt-1.5">Entegrasyon API listesinde döner; boş bırakılabilir.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Ürün Kapak Görseli (Opsiyonel)1</label>
                @if($product->image_url)
                    <div class="mb-3">
                        <img src="{{ $localImageUrl ?? $product->image_url }}" class="h-16 w-16 object-cover rounded-lg shadow-sm border border-slate-200">
                    </div>
                @endif
                <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition cursor-pointer">
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Kısa Açıklama & Notlar</label>
                <textarea name="description" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white placeholder-slate-400" placeholder="Ürünle ilgili kampanya kurgusu veya teknik özellikleri buraya düşebilirsiniz...">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>

        @if(auth()->user()->hasPermission('products.edit'))
        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-transform hover:-translate-y-0.5" style="color:white!important;">
                {{ $product->exists ? 'Değişiklikleri Kaydet' : 'Ürünü Oluştur' }}
            </button>
        </div>
        @else
        <div class="mt-6 flex justify-end">
            <div class="bg-slate-100 text-slate-400 font-bold py-3 px-8 rounded-xl border-2 border-dashed border-slate-200 uppercase text-[10px] tracking-widest">
                DÜZENLEME YETKİNİZ BULUNMUYOR
            </div>
        </div>
        @endif
    </form>
</div>
@endsection
