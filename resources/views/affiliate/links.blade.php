@extends('layouts.affiliate')

@section('title', 'Linklerim')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight font-display">Affiliate Linklerim</h2>
        <p class="text-slate-400 text-xs md:text-sm mt-1">Oluşturduğunuz ve tıklanma/satış takibi yaptığınız yönlendirme adresleri.</p>
    </div>
    <a href="{{ route('affiliate.campaigns') }}" class="py-3 px-6 rounded-2xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs flex items-center gap-2 tracking-wide shadow-lg shadow-teal-500/10 hover:shadow-teal-500/20 transition-all transform hover:-translate-y-0.5 self-start">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
        Yeni Link Oluştur
    </a>
</div>

<div class="glassmorphic-card p-6 md:p-8">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="border-b border-white/5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <th class="pb-3">Domain & Hedef</th>
                    <th class="pb-3">Kanal / Etiket</th>
                    <th class="pb-3">Yönlendirme Linki</th>
                    <th class="pb-3 text-center">Tıklama</th>
                    <th class="pb-3">Oluşturma Tarihi</th>
                    <th class="pb-3 text-right">İşlemler</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-slate-300">
                @forelse($links as $link)
                    <tr class="group hover:bg-white/[0.01] transition-colors" x-data="{ copied: false }">
                        <td class="py-4">
                            <span class="font-bold text-white font-display text-xs">{{ $link->domain ? $link->domain->domain_name : $link->domain_url }}</span>
                            <span class="block text-[10px] text-slate-500 truncate max-w-[150px]" title="Hedef Yol: {{ $link->target_path }}">Yol: {{ $link->target_path }}</span>
                        </td>
                        <td class="py-4">
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-950/60 border border-white/5 text-[10px] font-bold text-slate-300 capitalize inline-block">
                                {{ $link->channel ?: 'Doğrudan' }}
                            </span>
                            @if($link->keyword)
                                <span class="block text-[10px] text-slate-500 font-medium mt-1">#{{ $link->keyword }}</span>
                            @endif
                        </td>
                        <td class="py-4">
                            <div class="flex items-center gap-2 max-w-[280px]">
                                <input type="text" readonly value="{{ $link->full_affiliate_url }}"
                                       class="w-full py-1.5 px-3 bg-slate-950/60 border border-white/5 text-slate-400 placeholder-slate-500 text-[10px] font-bold font-mono rounded-lg outline-none cursor-text truncate">
                                
                                <button @click="navigator.clipboard.writeText('{{ $link->full_affiliate_url }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                                        class="p-2 rounded-lg bg-teal-500/10 hover:bg-teal-500/20 text-teal-400 border border-teal-500/10 transition-colors flex-shrink-0 relative"
                                        title="Kopyala">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!copied">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                    </svg>
                                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="copied" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td class="py-4 text-center font-display font-extrabold text-xs text-white">
                            {{ number_format($link->clicks_count) }}
                        </td>
                        <td class="py-4 text-xs text-slate-500 font-medium">
                            {{ $link->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="py-4 text-right">
                            <form method="POST" action="{{ route('affiliate.links.delete', ['id' => $link->id]) }}"
                                  onsubmit="return confirm('Bu linki silmek istediğinize emin misiniz?')" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/10 transition-colors" title="Sil">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 text-xs font-medium">Henüz bir affiliate linki oluşturmadınız. Kampanyalar sayfasından başlayın!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    @if($links->hasPages())
        <div class="mt-6 pt-4 border-t border-white/5">
            {{ $links->links() }}
        </div>
    @endif
</div>
@endsection
