@props([
    'title',
    'subtitle' => null,
    'badge' => 'API & DNS',
])

<style>
    .cf-module { --cf-orange: #f38020; --cf-amber: #faad3f; --cf-deep: #1d1f20; }
    .cf-hero {
        background: linear-gradient(135deg, var(--cf-orange) 0%, #e35b0e 45%, var(--cf-amber) 100%);
        box-shadow: 0 12px 40px -8px rgba(243, 128, 32, 0.45), inset 0 1px 0 rgba(255,255,255,0.25);
    }
    .cf-hero-inner { text-shadow: 0 1px 2px rgba(0,0,0,0.12); }
    .cf-mark {
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.35);
    }
    .cf-card {
        border: 1px solid rgba(243, 128, 32, 0.18);
        box-shadow: 0 10px 40px -12px rgba(243, 128, 32, 0.15), 0 4px 6px -2px rgba(0,0,0,0.04);
    }
    .cf-table-head { background: linear-gradient(180deg, #fff8f2 0%, #fff4eb 100%); }
    .cf-link { color: #c2410c; }
    .cf-link:hover { color: #ea580c; }
    .cf-btn-primary {
        background: linear-gradient(135deg, var(--cf-orange) 0%, #ea580c 100%);
        box-shadow: 0 4px 14px -3px rgba(243, 128, 32, 0.55);
    }
    .cf-btn-primary:hover { filter: brightness(1.05); }
    .cf-pill { background: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.4); }
</style>

<div {{ $attributes->class(['cf-module flex flex-col gap-6 fade-in-up max-w-5xl mx-auto']) }}>
    <div class="cf-hero rounded-[28px] p-6 sm:p-8 text-white cf-hero-inner">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="cf-mark w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-9 h-9 text-white" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M16 4v4M16 24v4M4 16h4M24 16h4M7 7l2.8 2.8M22.2 22.2L25 25M7 25l2.8-2.8M22.2 9.8L25 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.85"/>
                        <path d="M22 18.5h-1.2A4.5 4.5 0 0 0 12.5 16a4.3 4.3 0 0 0-4.2 3.1A3.2 3.2 0 0 0 8 22.5h14a2.5 2.5 0 0 0 .2-5z" fill="currentColor" fill-opacity="0.35" stroke="currentColor" stroke-width="1.2"/>
                    </svg>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-lg font-black tracking-tight opacity-95" style="letter-spacing: -0.02em;">Cloudflare</span>
                        <span class="cf-pill text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full">{{ $badge }}</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white/95">{{ $title }}</h2>
                    @if($subtitle)
                        <p class="text-sm text-white/90 font-medium mt-2 max-w-xl leading-relaxed">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            @isset($actions)
                @if(! $actions->isEmpty())
                    <div class="flex flex-wrap gap-3 sm:justify-end">
                        {{ $actions }}
                    </div>
                @endif
            @endisset
        </div>
    </div>

    {{ $slot }}
</div>
