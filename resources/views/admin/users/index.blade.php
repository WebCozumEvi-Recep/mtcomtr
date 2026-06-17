@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <div class="flex items-center gap-3">
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Kullanıcılar</h2>
            <button 
                x-data="favoriteManager" 
                @click="toggle('admin.users', 'Kullanıcılar', '{{ route('admin.users') }}', 'users')"
                class="favorite-btn"
                style="display: inline-flex !important; min-width: 32px; min-height: 32px;"
                title="Hızlı İşlemlere Ekle/Kaldır"
            >
                <svg data-favorite-id="admin.users" class="w-6 h-6 {{ (auth()->user()->favorites && collect(auth()->user()->favorites)->contains('id', 'admin.users')) ? 'text-amber-400' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
            </button>
        </div>
        <p class="text-slate-500 font-bold">Paneli kullanan ekip üyelerini yönetin.</p>
    </div>
    <div class="flex gap-4">
        @if(auth()->user()->hasPermission('users.view'))
        <a href="{{ route('admin.roles') }}" class="bg-white border-2 border-slate-200 text-slate-700 font-black px-6 py-4 rounded-2xl hover:bg-slate-50 transition">
            ROLLERİ YÖNET
        </a>
        @endif
        @if(auth()->user()->hasPermission('users.edit'))
        <a href="{{ route('admin.users.create') }}" class="bg-brand-600 text-white font-black px-8 py-4 rounded-2xl shadow-xl hover:bg-brand-700 transition flex items-center gap-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            YENİ KULLANICI
        </a>
        @endif
    </div>
</div>

<!-- Search Area -->
<div class="premium-card p-4 mb-8">
    <form action="{{ route('admin.users') }}" method="GET" class="flex gap-4 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Kullanıcı Adı veya E-posta ara..." class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        <button type="submit" class="bg-slate-800 text-white font-bold px-6 py-2 rounded-xl text-sm hover:bg-black transition" style="color:white!important;">Ara</button>
        @if(request()->filled('search'))
            <a href="{{ route('admin.users') }}" class="text-slate-400 hover:text-red-500 transition" title="Aramayı Temizle">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </a>
        @endif
    </form>
</div>

<div class="premium-card overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 text-xs font-black text-slate-400 uppercase tracking-widest">
                <th class="p-6">KULLANICI</th>
                <th class="p-6">ROL</th>
                <th class="p-6">E-POSTA</th>
                <th class="p-6 text-right">İŞLEMLER</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
            @foreach($users as $user)
            <tr class="group hover:bg-slate-50 transition-colors">
                <td class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-brand-100 group-hover:text-brand-600 transition font-black">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <span class="text-slate-900">{{ $user->name }}</span>
                    </div>
                </td>
                <td class="p-6">
                    @if($user->isGlobalAdmin())
                        <span class="bg-slate-900 text-white px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">
                            GLOBAL ADMIN
                        </span>
                    @elseif($user->customRole)
                        <span class="bg-brand-50 text-brand-700 px-3 py-1 rounded-lg text-[10px] font-black uppercase">
                            {{ $user->customRole->name }}
                        </span>
                    @else
                        <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-lg text-[10px] font-black uppercase">
                            STANDART USER
                        </span>
                    @endif
                </td>
                <td class="p-6 text-slate-500">{{ $user->email }}</td>
                <td class="p-6 text-right">
                    <div class="flex justify-end gap-2 text-xs">
                        @if(auth()->user()->hasPermission('users.edit'))
                        <a href="{{ route('admin.users.edit', $user) }}" class="p-3 bg-white border border-slate-200 text-slate-600 rounded-xl hover:text-brand-600 hover:border-brand-200 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </a>
                        @endif

                        @if(auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirmDelete(event, '[target] isimli kullanıcıyı sistemden silmek istediğinize emin misiniz?', '{{ $user->name }}')">
                            @csrf @method('DELETE')
                            <button class="p-3 bg-white border border-slate-200 text-red-400 rounded-xl hover:text-red-600 hover:border-red-200 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-8">
    {{ $users->links() }}
</div>
@endsection
