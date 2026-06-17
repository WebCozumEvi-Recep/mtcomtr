@extends('layouts.admin')

@section('title', $user->name . ' - Bildirim Geçmişi')

@section('content')
<div class="flex flex-col gap-6 fade-in-up">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.alerts.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">{{ $user->name }}</h2>
                <p class="text-sm text-slate-500 font-medium">Kullanıcı bildirim ve aksiyon geçmişi</p>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500">
                    <tr>
                        <th class="px-6 py-4 border-b border-slate-200">Tür</th>
                        <th class="px-6 py-4 border-b border-slate-200">Başlık</th>
                        <th class="px-6 py-4 border-b border-slate-200">Mesaj</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-center">Durum</th>
                        <th class="px-6 py-4 border-b border-slate-200">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider
                                    @if($alert->type == 'danger') bg-red-100 text-red-600 
                                    @elseif($alert->type == 'warning') bg-amber-100 text-amber-600 
                                    @else bg-blue-100 text-blue-600 @endif">
                                    @if($alert->type == 'danger') KRİTİK
                                    @elseif($alert->type == 'warning') UYARI
                                    @else BİLGİ @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $alert->title }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {!! $alert->message !!}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($alert->users->contains($user->id))
                                    <span class="inline-flex items-center gap-1 text-green-600 font-bold text-[10px] uppercase">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        Gördü
                                    </span>
                                    <div class="text-[9px] text-slate-400 font-medium mt-0.5">
                                        {{ $alert->users->find($user->id)->pivot->read_at->format('d.m.Y H:i') }}
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-400 font-bold text-[10px] uppercase">
                                        Görmedi
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-400">
                                {{ $alert->created_at->format('d.m.Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">
                                Henüz bir bildirim kaydı bulunmuyor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($alerts->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
