@extends('layouts.admin')

@section('title', $provider->exists ? 'Ödeme Sağlayıcı Düzenle' : 'Yeni Ödeme Sağlayıcı')

@section('content')
<div class="flex flex-col gap-6 fade-in-up max-w-3xl mx-auto">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-slate-900 tracking-tighter">{{ $provider->exists ? 'Düzenle: ' . $provider->name : 'Yeni Ödeme Sağlayıcı' }}</h2>
        <a href="{{ route('admin.payment-providers.index') }}" class="text-sm font-bold text-slate-400 hover:text-slate-600 transition">← Listeye Dön</a>
    </div>

    <form action="{{ $provider->exists ? route('admin.payment-providers.update', $provider) : route('admin.payment-providers.store') }}" method="POST">
        @csrf
        @if($provider->exists) @method('PUT') @endif

        <div class="premium-card p-6 sm:p-8 space-y-6">
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Tanım Adı</label>
                <input type="text" name="name" value="{{ old('name', $provider->name) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Örn: PayTR - {{ config('app.name') }} Hesabı" required>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Sağlayıcı Türü</label>
                <select name="provider_type" id="provider_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-brand-500 cursor-pointer" required onchange="updateConfigFields(this.value)">
                    <option value="paytr" {{ old('provider_type', $provider->provider_type) == 'paytr' ? 'selected' : '' }}>PayTR</option>
                    <option value="iyzico" {{ old('provider_type', $provider->provider_type) == 'iyzico' ? 'selected' : '' }}>iyzico</option>
                    <option value="vakifbank" {{ old('provider_type', $provider->provider_type) == 'vakifbank' ? 'selected' : '' }}>Vakıfbank</option>
                    <option value="bank_direct" {{ old('provider_type', $provider->provider_type) == 'bank_direct' ? 'selected' : '' }}>Banka Direkt (Özel API)</option>
                </select>
            </div>

            <div id="config-fields" class="space-y-4 pt-4 border-t border-slate-100">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">API Yapılandırması</h3>
                
                <div id="paytr-fields" class="config-group {{ old('provider_type', $provider->provider_type ?? 'paytr') == 'paytr' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Merchant ID</label>
                            <input type="text" name="config[merchant_id]" value="{{ old('config.merchant_id', $provider->config['merchant_id'] ?? '') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Merchant Key</label>
                            <input type="text" name="config[merchant_key]" value="{{ old('config.merchant_key', $provider->config['merchant_key'] ?? '') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Merchant Salt</label>
                            <input type="text" name="config[merchant_salt]" value="{{ old('config.merchant_salt', $provider->config['merchant_salt'] ?? '') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                    </div>
                </div>

                <div id="iyzico-fields" class="config-group {{ old('provider_type', $provider->provider_type) == 'iyzico' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">API Key</label>
                            <input type="text" name="config[api_key]" value="{{ old('config.api_key', $provider->config['api_key'] ?? '') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Secret Key</label>
                            <input type="text" name="config[secret_key]" value="{{ old('config.secret_key', $provider->config['secret_key'] ?? '') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Base URL</label>
                            <input type="text" name="config[base_url]" value="{{ old('config.base_url', $provider->config['base_url'] ?? 'https://api.iyzipay.com') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                    </div>
                </div>

                <div id="vakifbank-fields" class="config-group {{ old('provider_type', $provider->provider_type) == 'vakifbank' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Terminal No</label>
                            <input type="text" name="config[terminal_id]" value="{{ old('config.terminal_id', $provider->config['terminal_id'] ?? '') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Mağaza No (Merchant ID)</label>
                            <input type="text" name="config[merchant_id]" value="{{ old('config.merchant_id', $provider->config['merchant_id'] ?? '') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Kullanıcı Adı</label>
                            <input type="text" name="config[user_name]" value="{{ old('config.user_name', $provider->config['user_name'] ?? '') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Şifre / Password</label>
                            <input type="text" name="config[password]" value="{{ old('config.password', $provider->config['password'] ?? '') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">API URL (Live)</label>
                            <input type="text" name="config[api_url]" value="{{ old('config.api_url', $provider->config['api_url'] ?? 'https://3dsecure.vakifbank.com.tr/MPIAPI/MPI_Enrollment.aspx') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">API URL (Test)</label>
                            <input type="text" name="config[api_demo_url]" value="{{ old('config.api_demo_url', $provider->config['api_demo_url'] ?? 'https://3dsecuretest.vakifbank.com.tr/MPIAPI/MPI_Enrollment.aspx') }}" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Çalışma Modu</label>
                            <select name="config[mode]" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm">
                                <option value="test" {{ old('config.mode', $provider->config['mode'] ?? 'test') == 'test' ? 'selected' : '' }}>Test (Sandbox)</option>
                                <option value="live" {{ old('config.mode', $provider->config['mode'] ?? 'test') == 'live' ? 'selected' : '' }}>Live (Production)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="bank_direct-fields" class="config-group {{ old('provider_type', $provider->provider_type) == 'bank_direct' ? '' : 'hidden' }}">
                     <p class="text-xs text-slate-400 italic">Banka direkt entegrasyonu için teknik dökümana uygun parametreleri giriniz.</p>
                     <textarea name="config[custom_params]" rows="4" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm font-mono" placeholder='{"terminal_id": "...", "password": "..."}'>{{ old('config.custom_params', isset($provider->config['custom_params']) ? (is_array($provider->config['custom_params']) ? json_encode($provider->config['custom_params']) : $provider->config['custom_params']) : '') }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 p-4 rounded-2xl border border-slate-100 bg-slate-50">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $provider->is_active ?? 1) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <label for="is_active" class="font-bold text-slate-700 text-xs cursor-pointer">Bu sağlayıcıyı kullanıma aç</label>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('admin.payment-providers.index') }}" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition">İptal</a>
                <button type="submit" class="bg-slate-900 text-white font-black px-10 py-3 rounded-xl shadow-lg hover:bg-black transition">
                    {{ $provider->exists ? 'Güncelle' : 'Kaydet' }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function updateConfigFields(type) {
        document.querySelectorAll('.config-group').forEach(el => el.classList.add('hidden'));
        const active = document.getElementById(type + '-fields');
        if (active) active.classList.remove('hidden');
    }
</script>
@endsection
