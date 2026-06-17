@extends('layouts.affiliate')

@section('title', 'Medya Merkezi')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight font-display">Medya Merkezi & Reklam Görselleri</h2>
        <p class="text-slate-400 text-xs md:text-sm mt-1">Sosyal medya ve dijital platformlarda paylaşabileceğiniz hazır materyal bankası.</p>
    </div>
</div>

<!-- Grid listings -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @forelse($media as $item)
        <div class="glassmorphic-card overflow-hidden flex flex-col justify-between h-full group" x-data="{ copied: false }">
            <div>
                <!-- Image Preview -->
                <div class="h-80 w-full bg-slate-950/60 relative overflow-hidden flex items-center justify-center border-b border-white/5">
                    @if($item->file_path)
                        <img src="{{ asset('uploads/affiliate_media/' . $item->file_path) }}" alt="{{ $item->title }}" class="object-contain w-full h-full group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="text-slate-600 flex flex-col items-center gap-2">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-[10px] font-bold uppercase tracking-wider">Görsel Bulunmuyor</span>
                        </div>
                    @endif
                    
                    <!-- Media dimensions / type badge -->
                    <div class="absolute top-4 left-4 flex gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-slate-950/80 border border-white/10 text-[9px] font-extrabold text-teal-400 uppercase tracking-widest">{{ $item->media_type ?: 'Bilinmiyor' }}</span>
                        @if($item->size_label)
                            <span class="px-2.5 py-1 rounded-lg bg-slate-950/80 border border-white/10 text-[9px] font-extrabold text-slate-300 tracking-wider">{{ $item->size_label }}</span>
                        @endif
                    </div>
                </div>

                <!-- Media Details -->
                <div class="p-6">
                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block mb-1">
                        {{ $item->domain ? $item->domain->domain_name : 'Tüm Alan Adları' }}
                    </span>
                    <h3 class="text-base font-bold text-white font-display tracking-wide mb-2 truncate" title="{{ $item->title }}">{{ $item->title }}</h3>
                    
                    <!-- Warnings for the domain of this media -->
                    @if($item->domain && $item->domain->affiliateSetting && ($item->domain->affiliateSetting->warning_text || $item->domain->affiliateSetting->forbidden_terms))
                        <div class="mb-4 p-3 rounded-xl bg-amber-500/[0.03] border border-amber-500/10 space-y-2">
                            @if($item->domain->affiliateSetting->warning_text)
                                <div class="text-[10px] text-slate-400 leading-relaxed">
                                    <strong class="text-amber-400 font-extrabold uppercase tracking-wide block mb-0.5" style="font-size: 8px;">⚠️ Kurallar / Uyarı:</strong>
                                    {{ $item->domain->affiliateSetting->warning_text }}
                                </div>
                            @endif
                            @if($item->domain->affiliateSetting->forbidden_terms)
                                <div class="text-[10px] text-slate-400 leading-relaxed {{ $item->domain->affiliateSetting->warning_text ? 'border-t border-white/5 pt-2' : '' }}">
                                    <strong class="text-rose-400 font-extrabold uppercase tracking-wide block mb-0.5" style="font-size: 8px;">🚫 Yasaklar:</strong>
                                    {{ $item->domain->affiliateSetting->forbidden_terms }}
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($item->share_text)
                        <div class="space-y-2">
                            <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest">Paylaşım Metni</label>
                            <textarea readonly class="w-full h-16 p-3 bg-slate-950/60 border border-white/5 rounded-xl text-slate-300 text-xs focus:outline-none resize-none font-sans leading-relaxed">{{ $item->share_text }}</textarea>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Media Actions -->
            <div class="p-6 pt-0 flex items-center gap-3">
                @if($item->file_path)
                    <a href="{{ asset('uploads/affiliate_media/' . $item->file_path) }}" download class="flex-1 py-3 px-4 rounded-xl bg-slate-950/60 hover:bg-slate-950 border border-white/10 text-white font-bold text-xs flex items-center justify-center gap-2 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        İndir
                    </a>
                @endif

                @if($item->share_text)
                    <button @click="navigator.clipboard.writeText('{{ addslashes($item->share_text) }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                            class="flex-1 py-3 px-4 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs flex items-center justify-center gap-2 transition-all">
                        <svg class="w-4 h-4 text-slate-950 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!copied">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="copied" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span x-text="copied ? 'Kopyalandı!' : 'Metni Kopyala'">Metni Kopyala</span>
                    </button>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full glassmorphic-card p-12 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <h3 class="text-base font-bold text-white font-display">Henüz Medya Materyali Eklenmedi</h3>
            <p class="text-xs text-slate-500 mt-1">Yönetici reklam ve sosyal medya görselleri yüklediğinde burada görüntülenecektir.</p>
        </div>
    @endforelse
</div>

<!-- Pagination links -->
@if($media->hasPages())
    <div class="mt-8 pt-4 border-t border-white/5">
        {{ $media->links() }}
    </div>
@endif
@endsection
