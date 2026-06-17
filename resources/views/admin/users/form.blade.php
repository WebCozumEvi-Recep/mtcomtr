@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 font-bold mb-6 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Geri Dön
    </a>

    <form action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST" class="space-y-8">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">{{ $user->exists ? 'KULLANICIYI DÜZENLE' : 'YENİ KULLANICI EKLE' }}</h2>
                <p class="text-slate-500 font-bold">Ekip üyesi giriş bilgilerini belirleyin.</p>
            </div>
        </div>

        <div class="premium-card p-10 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">TAM AD SOYAD</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 outline-none focus:border-brand-500 font-bold" required>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">E-POSTA ADRESİ</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 outline-none focus:border-brand-500 font-bold" required>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">HESAP YETKİ TÜRÜ</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ type: '{{ old('account_type', $user->role === \App\Enums\UserRole::Admin ? 'global' : ($user->custom_role_id ? 'custom' : 'global')) }}' }">
                    <label class="relative flex flex-col p-4 bg-slate-50 border-2 rounded-2xl cursor-pointer transition-all" :class="type === 'global' ? 'border-brand-500 bg-brand-50/30' : 'border-slate-100'">
                        <input type="radio" name="account_type" value="global" x-model="type" class="hidden">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center" :class="type === 'global' ? 'border-brand-500' : 'border-slate-300'">
                                <div class="w-2 h-2 rounded-full bg-brand-500" x-show="type === 'global'"></div>
                            </div>
                            <span class="text-sm font-black text-slate-900">Tam Yetkili (Admin)</span>
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 ml-7 uppercase tracking-tight">Tüm modüllere ve ayarlara sınırsız erişim.</p>
                    </label>

                    <label class="relative flex flex-col p-4 bg-slate-50 border-2 rounded-2xl cursor-pointer transition-all" :class="type === 'custom' ? 'border-brand-500 bg-brand-50/30' : 'border-slate-100'">
                        <input type="radio" name="account_type" value="custom" x-model="type" class="hidden">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center" :class="type === 'custom' ? 'border-brand-500' : 'border-slate-300'">
                                <div class="w-2 h-2 rounded-full bg-brand-500" x-show="type === 'custom'"></div>
                            </div>
                            <span class="text-sm font-black text-slate-900">Kısıtlı Yetkili (Rol Bazlı)</span>
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 ml-7 uppercase tracking-tight">Sadece seçilen role ait modülleri yönetebilir.</p>
                    </label>

                    <div class="md:col-span-2 mt-4 animate-in fade-in slide-in-from-top-2 duration-300" x-show="type === 'custom'">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 italic">Atanacak Rolü Seçin</label>
                        <select name="custom_role_id" class="w-full bg-white border border-brand-200 rounded-2xl px-6 py-4 outline-none focus:border-brand-500 font-bold shadow-sm">
                            <option value="">Lütfen Rol Seçin...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('custom_role_id', $user->custom_role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">{{ $user->exists ? 'ŞİFRE (DEĞİŞTİRMEK İÇİN DOLDURUN)' : 'ŞİFRE' }}</label>
                <input type="password" name="password" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 outline-none focus:border-brand-500 font-bold" {{ $user->exists ? '' : 'required' }}>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-slate-900 text-white font-black px-12 py-5 rounded-2xl shadow-2xl hover:bg-black transition-all active:scale-95">
                KAYDET VE YAYINLA
            </button>
        </div>
    </form>
</div>
@endsection
