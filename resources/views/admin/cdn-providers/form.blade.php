@extends('layouts.admin')

@section('title', $provider->exists ? 'CDN Duzenle' : 'Yeni CDN Ekle')

@section('content')
<div class="max-w-4xl mx-auto flex flex-col gap-6 fade-in-up">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $provider->exists ? 'CDN Kaydini Duzenle' : 'Yeni CDN Kaydi' }}</h2>
            <p class="text-sm text-slate-500 font-medium">Bunny veya diger CDN saglayicilarini buradan yonetin.</p>
        </div>
        <a href="{{ route('admin.cdn-providers.index') }}" class="text-slate-500 font-bold hover:text-slate-800 transition">Geri Don</a>
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

    <form action="{{ $provider->exists ? route('admin.cdn-providers.update', $provider) : route('admin.cdn-providers.store') }}" method="POST" class="premium-card p-8 flex flex-col gap-6">
        @csrf
        @if($provider->exists)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Kayit Adi</label>
                <input type="text" name="name" value="{{ old('name', $provider->name) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="Orn: Bunny Production">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Saglayici Tipi</label>
                <select name="provider" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white">
                    @php $type = old('provider', $provider->provider ?: 'bunny'); @endphp
                    <option value="bunny" {{ $type === 'bunny' ? 'selected' : '' }}>BunnyCDN</option>
                    <option value="cloudflare" {{ $type === 'cloudflare' ? 'selected' : '' }}>Cloudflare CDN</option>
                    <option value="cloudfront" {{ $type === 'cloudfront' ? 'selected' : '' }}>AWS CloudFront</option>
                    <option value="custom" {{ $type === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Base URL</label>
                <input type="url" name="base_url" value="{{ old('base_url', $provider->base_url) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="Opsiyonel (domain akisindan otomatik atanabilir)">
                <p class="text-[11px] text-slate-500 font-medium mt-1">Bunny kullaniyorsan burayi bos birakabilirsin; domain otomasyonu Pull Zone olustururken kendi host bilgisini yonetir.</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">API Token</label>
                <input type="password" name="api_token" value="" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="{{ $provider->exists ? 'Bos birakirsan mevcut token korunur' : 'Token girin (opsiyonel)' }}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Bunny Pull Zone</label>
                <input type="text" name="bunny_pull_zone" value="{{ old('bunny_pull_zone', $provider->config['bunny_pull_zone'] ?? '') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="teksat-zone">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Bunny Storage Zone</label>
                <input type="text" name="bunny_storage_zone" value="{{ old('bunny_storage_zone', $provider->config['bunny_storage_zone'] ?? '') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="teksat-storage">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Not</label>
                <textarea name="notes" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white" placeholder="Bu kayit hakkinda not...">{{ old('notes', $provider->config['notes'] ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $provider->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm font-semibold text-slate-700">Kaydettikten sonra aktif CDN olarak ata</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition">
                {{ $provider->exists ? 'Degisiklikleri Kaydet' : 'CDN Kaydini Olustur' }}
            </button>
        </div>
    </form>
</div>
@endsection
