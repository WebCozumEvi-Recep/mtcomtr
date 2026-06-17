@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">ROLLER & YETKİLER</h2>
        <p class="text-slate-500 font-bold">Ekip üyelerinin hangi modüllere erişebileceğini tanımlayın.</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="bg-brand-600 text-white font-black px-8 py-4 rounded-2xl shadow-xl hover:bg-brand-700 transition flex items-center gap-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        YENİ ROL EKLE
    </a>
</div>

<div class="premium-card overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 text-xs font-black text-slate-400 uppercase tracking-widest">
                <th class="p-6">ROL ADI</th>
                <th class="p-6">YETKİ SAYISI</th>
                <th class="p-6 text-right">İŞLEMLER</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($roles as $role)
            <tr class="group hover:bg-slate-50 transition-colors">
                <td class="p-6">
                    <span class="text-lg font-bold text-slate-900">{{ $role->name }}</span>
                </td>
                <td class="p-6">
                    <span class="bg-brand-50 text-brand-700 px-3 py-1 rounded-lg text-xs font-black">
                        {{ count($role->permissions ?? []) }} YETKİ
                    </span>
                </td>
                <td class="p-6 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="p-3 bg-white border border-slate-200 text-slate-600 rounded-xl hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </a>
                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirmDelete(event, '[target] isimli rolü silmek istediğinize emin misiniz? Bu role sahip kullanıcılar yetkisiz kalabilir.', '{{ $role->name }}')">
                            @csrf @method('DELETE')
                            <button class="p-3 bg-white border border-slate-200 text-red-400 rounded-xl hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
