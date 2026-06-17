@extends('layouts.admin')

@section('title', 'Ödeme Ayarları')

@section('content')
<div class="flex flex-col gap-6 fade-in-up">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Ödeme Sağlayıcılar</h2>
            </div>
            <p class="text-sm text-slate-500 font-medium">Sanal POS ve banka entegrasyon ayarları</p>
        </div>
        @if(auth()->user()->hasPermission('settings.edit'))
        <div class="flex gap-2">
            <a href="{{ route('admin.payment-providers.create') }}" class="bg-brand-600 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-brand-700 transition shadow-md flex items-center" style="color:white!important;">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span style="color:white;">Yeni Sağlayıcı Ekle</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Data Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500">
                    <tr>
                        <th class="px-6 py-4 border-b border-slate-200">Adı</th>
                        <th class="px-6 py-4 border-b border-slate-200">Tür</th>
                        <th class="px-6 py-4 border-b border-slate-200">Durum</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($providers as $provider)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $provider->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md font-bold text-[10px] uppercase">
                                    {{ $provider->provider_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($provider->is_active)
                                    <span class="status-pill success">Aktif</span>
                                @else
                                    <span class="status-pill danger">Pasif</span>
                                @endif
                            </td>
                             <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(auth()->user()->hasPermission('settings.edit'))
                                        <a href="{{ route('admin.payment-providers.edit', $provider) }}" class="p-2 bg-slate-50 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="Düzenle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>

                                        <form action="{{ route('admin.payment-providers.destroy', $provider) }}" method="POST" onsubmit="return confirm('Bu sağlayıcıyı silmek istediğinize emin misiniz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-red-50 text-red-400 hover:text-red-600 hover:bg-red-100 rounded-lg transition" title="Sil">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500 font-medium">
                                Henüz bir ödeme sağlayıcı tanımlanmadı.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
