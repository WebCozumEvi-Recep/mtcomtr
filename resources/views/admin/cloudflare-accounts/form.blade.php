@extends('layouts.admin')

@section('title', $account->exists ? 'Cloudflare Hesabı Düzenle' : 'Yeni Cloudflare Hesabı')

@section('content')
<x-admin.cf-module
    class="max-w-2xl"
    title="{{ $account->exists ? 'Hesabı düzenle' : 'Yeni API hesabı' }}"
    subtitle="Cloudflare Dashboard → My Profile → API Tokens. Zone / DNS düzenleme yetkisi olan bir token oluşturun."
    badge="{{ $account->exists ? 'Düzenle' : 'Oluştur' }}"
>
    <x-slot name="actions">
        <a href="{{ route('admin.cloudflare-accounts.index') }}" class="inline-flex items-center px-5 py-3 rounded-2xl text-sm font-black text-white/95 bg-white/15 hover:bg-white/25 border border-white/30 transition uppercase tracking-wide">
            ← Listeye dön
        </a>
    </x-slot>

    <form action="{{ $account->exists ? route('admin.cloudflare-accounts.update', $account) : route('admin.cloudflare-accounts.store') }}" method="POST" class="cf-card premium-card bg-white rounded-[24px] p-8 sm:p-10 space-y-6 border border-orange-100/80">
        @csrf
        @if($account->exists) @method('PUT') @endif

        <div class="flex items-center gap-3 pb-4 border-b border-orange-100">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center shadow-md shadow-orange-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-black text-orange-600 uppercase tracking-widest">API erişimi</p>
                <p class="text-sm font-bold text-slate-700">Token sadece bu panelde kullanılır</p>
            </div>
        </div>

        <div>
            <label class="block text-xs font-black text-slate-500 mb-2 uppercase tracking-wider">Görünen ad</label>
            <input type="text" name="name" value="{{ old('name', $account->name) }}" required class="w-full bg-orange-50/40 border border-orange-100 rounded-2xl px-5 py-4 outline-none font-bold focus:ring-2 focus:ring-orange-400/40 focus:border-orange-300" placeholder="Örn. Müşteri A — Cloudflare">
            @error('name')<p class="text-red-600 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-black text-slate-500 mb-2 uppercase tracking-wider">Account ID (opsiyonel ama önerilir)</label>
            <input type="text" name="account_identifier" value="{{ old('account_identifier', $account->account_identifier) }}" class="w-full bg-orange-50/40 border border-orange-100 rounded-2xl px-5 py-4 outline-none font-mono text-sm focus:ring-2 focus:ring-orange-400/40 focus:border-orange-300" placeholder="Örn: 96a5d7f412fc1190ec2c44918724f011" autocomplete="off">
            <p class="text-[10px] text-slate-500 mt-2 font-semibold">Zone listeleme isteğinde account.id filtresi olarak kullanılır.</p>
            @error('account_identifier')<p class="text-red-600 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-black text-slate-500 mb-2 uppercase tracking-wider">API token</label>
            <input type="password" name="api_token" class="w-full bg-slate-900 text-orange-100 border border-slate-700 rounded-2xl px-5 py-4 outline-none font-mono text-sm placeholder:text-slate-500 focus:ring-2 focus:ring-orange-500/50" placeholder="{{ $account->exists ? 'Değiştirmek için yeni token yapıştırın' : 'cf_ veya Global Key…' }}" {{ $account->exists ? '' : 'required' }} autocomplete="off">
            @if($account->exists)
                <p class="text-[10px] text-slate-500 mt-2 font-semibold">Boş bırakırsan mevcut token aynen kalır.</p>
            @endif
            @error('api_token')<p class="text-red-600 text-xs mt-2 font-bold">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-3 p-4 bg-orange-50/60 rounded-2xl border border-orange-100">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $account->exists ? $account->is_active : true) ? 'checked' : '' }} class="w-6 h-6 rounded border-orange-200 text-orange-600 focus:ring-orange-500">
            <label for="is_active" class="font-bold text-slate-800 text-xs cursor-pointer leading-snug">Hesap aktif — pasif hesaplar listede “(pasif)” olarak görünür</label>
        </div>

        <div class="flex flex-wrap gap-4 pt-2">
            <button type="submit" class="cf-btn-primary text-white font-black px-8 py-4 rounded-2xl transition uppercase tracking-widest text-sm" style="color:#fff!important;">
                {{ $account->exists ? 'Kaydet' : 'Oluştur' }}
            </button>
        </div>
    </form>
</x-admin.cf-module>
@endsection
