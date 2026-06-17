@extends('layouts.admin')

@section('title', 'CDN Firmalari')

@section('content')
<div class="max-w-6xl mx-auto flex flex-col gap-6 fade-in-up">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">CDN Firmalari</h2>
            <p class="text-sm text-slate-500 font-medium">CDN kayitlarini yonetin ve aktif saglayiciyi secin.</p>
        </div>
        <a href="{{ route('admin.cdn-providers.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 px-5 rounded-xl transition">
            Yeni CDN Ekle
        </a>
    </div>

    <div class="premium-card p-6 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 border-b border-slate-200">
                    <th class="py-3 pr-4">Firma</th>
                    <th class="py-3 pr-4">Tip</th>
                    <th class="py-3 pr-4">Base URL</th>
                    <th class="py-3 pr-4">Durum</th>
                    <th class="py-3 text-right">Islemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers as $item)
                    <tr class="border-b border-slate-100">
                        <td class="py-4 pr-4 font-semibold text-slate-800">{{ $item->name }}</td>
                        <td class="py-4 pr-4 text-slate-600 uppercase">{{ $item->provider }}</td>
                        <td class="py-4 pr-4 text-slate-600">{{ $item->base_url }}</td>
                        <td class="py-4 pr-4">
                            @if($item->is_active)
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">Pasif</span>
                            @endif
                        </td>
                        <td class="py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(! $item->is_active)
                                    <form method="POST" action="{{ route('admin.cdn-providers.activate', $item) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-semibold text-xs">Aktif Et</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.cdn-providers.edit', $item) }}" class="px-3 py-1.5 rounded-lg bg-slate-700 text-white font-semibold text-xs">Duzenle</a>
                                <form method="POST" action="{{ route('admin.cdn-providers.destroy', $item) }}" onsubmit="return confirmDelete(event, '[target] CDN kaydını silmek istediğinize emin misiniz?', '{{ $item->name }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white font-semibold text-xs">Sil</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-slate-400 font-medium">Henuz CDN kaydi yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
