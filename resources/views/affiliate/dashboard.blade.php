@extends('layouts.affiliate')

@section('title', 'Dashboard')

@section('content')
<!-- Welcome banner -->
<div class="mb-8 p-6 md:p-8 rounded-3xl bg-gradient-to-r from-teal-500/10 via-emerald-500/5 to-transparent border border-teal-500/15 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight font-display">Tekrar hoş geldiniz, {{ Auth::guard('affiliate')->user()->name }}!</h2>
        <p class="text-slate-400 text-xs md:text-sm mt-1">Affiliate performansınızı, kazançlarınızı ve tıklama istatistiklerinizi buradan izleyebilirsiniz.</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-widest">KODUNUZ:</span>
        <span class="bg-teal-500/10 border border-teal-500/20 px-3.5 py-1.5 rounded-xl text-teal-400 text-xs font-extrabold font-display tracking-wider">{{ Auth::guard('affiliate')->user()->affiliate_code }}</span>
    </div>
</div>

<!-- Grid Statistics Cards -->
<div class="grid grid-cols-2 lg:grid-cols-6 gap-4 md:gap-6 mb-8">
    <!-- Click count -->
    <div class="glassmorphic-card p-5 relative overflow-hidden">
        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Toplam Tıklama</p>
        <h3 class="text-2xl md:text-3xl font-extrabold text-white font-display">{{ number_format($totalClicks) }}</h3>
        <div class="absolute right-4 bottom-4 w-9 h-9 rounded-xl bg-teal-500/10 flex items-center justify-center border border-teal-500/20 text-teal-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
        </div>
    </div>

    <!-- Order count -->
    <div class="glassmorphic-card p-5 relative overflow-hidden">
        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Toplam Satış</p>
        <h3 class="text-2xl md:text-3xl font-extrabold text-white font-display">{{ number_format($totalOrders) }}</h3>
        <div class="absolute right-4 bottom-4 w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20 text-emerald-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        </div>
    </div>

    <!-- Conversion -->
    <div class="glassmorphic-card p-5 relative overflow-hidden">
        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Dönüşüm Oranı</p>
        <h3 class="text-2xl md:text-3xl font-extrabold text-white font-display">%{{ $conversionRate }}</h3>
        <div class="absolute right-4 bottom-4 w-9 h-9 rounded-xl bg-sky-500/10 flex items-center justify-center border border-sky-500/20 text-sky-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        </div>
    </div>

    <!-- Pending earnings -->
    <div class="glassmorphic-card p-5 relative overflow-hidden">
        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Bekleyen Bakiye</p>
        <h3 class="text-2xl md:text-3xl font-extrabold text-amber-400 font-display">{{ number_format($pendingBalance, 2) }} <span class="text-xs font-bold">TL</span></h3>
        <div class="absolute right-4 bottom-4 w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20 text-amber-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <!-- Approved checkable earnings -->
    <div class="glassmorphic-card p-5 relative overflow-hidden">
        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Çekilebilir Bakiye</p>
        <h3 class="text-2xl md:text-3xl font-extrabold text-teal-400 font-display">{{ number_format($withdrawableBalance, 2) }} <span class="text-xs font-bold">TL</span></h3>
        <div class="absolute right-4 bottom-4 w-9 h-9 rounded-xl bg-teal-500/10 flex items-center justify-center border border-teal-500/20 text-teal-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <!-- Paid earnings -->
    <div class="glassmorphic-card p-5 relative overflow-hidden">
        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Ödenen Toplam</p>
        <h3 class="text-2xl md:text-3xl font-extrabold text-indigo-400 font-display">{{ number_format($paidBalance, 2) }} <span class="text-xs font-bold">TL</span></h3>
        <div class="absolute right-4 bottom-4 w-9 h-9 rounded-xl bg-indigo-500/10 flex items-center justify-center border border-indigo-500/20 text-indigo-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
</div>

<!-- Performance Chart Section -->
<div class="glassmorphic-card p-6 md:p-8 mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-bold text-white tracking-wide font-display">15 Günlük Performans Analizi</h3>
            <p class="text-slate-400 text-xs mt-0.5">Tıklanma ve kazanç grafik trendleri</p>
        </div>
    </div>
    <div class="h-[320px] w-full">
        <canvas id="performanceChart"></canvas>
    </div>
</div>

<!-- Tables Layout Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent commissions -->
    <div class="glassmorphic-card p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-white tracking-wide font-display">Son Satışlar & Hak Edişler</h3>
            <a href="{{ route('affiliate.stats') }}" class="text-xs font-bold text-teal-400 hover:text-teal-300 transition">Tümünü Gör</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <th class="pb-3">Sipariş</th>
                        <th class="pb-3">Site / Domain</th>
                        <th class="pb-3 text-right">Tutar</th>
                        <th class="pb-3 text-right">Komisyon</th>
                        <th class="pb-3 text-right">Durum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($recentCommissions as $commission)
                        <tr class="group hover:bg-white/[0.01] transition-colors">
                            <td class="py-3.5">
                                <span class="font-bold text-white font-display text-xs">#{{ $commission->order_id }}</span>
                                <span class="block text-[10px] text-slate-500">{{ $commission->created_at->format('d.m.Y H:i') }}</span>
                            </td>
                            <td class="py-3.5">
                                <span class="text-xs font-semibold text-slate-300 truncate block max-w-[120px]">{{ $commission->domain ? $commission->domain->domain_name : '-' }}</span>
                                <span class="text-[10px] text-slate-500 block capitalize">{{ $commission->channel ?: 'Doğrudan' }}</span>
                            </td>
                            <td class="py-3.5 text-right font-display font-medium text-xs text-slate-400">
                                {{ number_format($commission->order_total, 2) }} TL
                            </td>
                            <td class="py-3.5 text-right font-display font-bold text-xs text-teal-400">
                                {{ number_format($commission->net_amount, 2) }} TL
                            </td>
                            <td class="py-3.5 text-right">
                                @if($commission->status === 'pending')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-500/10 border border-amber-500/20 text-amber-400 uppercase">Bekliyor</span>
                                @elseif($commission->status === 'approved')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-teal-500/10 border border-teal-500/20 text-teal-400 uppercase">Onaylandı</span>
                                @elseif($commission->status === 'withdrawing')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-sky-500/10 border border-sky-500/20 text-sky-400 uppercase">Çekim Sırasında</span>
                                @elseif($commission->status === 'paid')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 uppercase">Ödendi</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-500/10 border border-rose-500/20 text-rose-400 uppercase">İptal/Red</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 text-xs font-medium">Henüz hak ediş kaydınız bulunmamaktadır.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent clicks -->
    <div class="glassmorphic-card p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-white tracking-wide font-display">Son Tıklamalar</h3>
            <a href="{{ route('affiliate.stats') }}" class="text-xs font-bold text-teal-400 hover:text-teal-300 transition">Tümünü Gör</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <th class="pb-3">Tıklama ID</th>
                        <th class="pb-3">Kanal</th>
                        <th class="pb-3">Kelime (Keyword)</th>
                        <th class="pb-3">Cihaz</th>
                        <th class="pb-3 text-right">Zaman</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($recentClicks as $click)
                        <tr class="group hover:bg-white/[0.01] transition-colors">
                            <td class="py-3.5">
                                <span class="font-bold text-white font-display text-xs truncate block max-w-[120px]" title="{{ $click->click_id }}">{{ substr($click->click_id, 0, 8) }}...</span>
                                <span class="text-[10px] text-slate-500 block">{{ $click->ip_address }}</span>
                            </td>
                            <td class="py-3.5 capitalize text-xs text-slate-300">
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-950/60 border border-white/5 text-[10px] font-bold text-slate-300">
                                    {{ $click->channel ?: 'Doğrudan' }}
                                </span>
                            </td>
                            <td class="py-3.5 text-xs text-slate-400 font-medium">
                                {{ $click->keyword ?: '-' }}
                            </td>
                            <td class="py-3.5 text-xs text-slate-400 capitalize">
                                @if($click->device === 'mobile')
                                    <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> Mobil</span>
                                @elseif($click->device === 'tablet')
                                    <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> Tablet</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> Masaüstü</span>
                                @endif
                            </td>
                            <td class="py-3.5 text-right font-display text-xs text-slate-500">
                                {{ $click->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 text-xs font-medium">Henüz tıklama logunuz bulunmamaktadır.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        const gradientClicks = ctx.createLinearGradient(0, 0, 0, 300);
        gradientClicks.addColorStop(0, 'rgba(20, 184, 166, 0.25)');
        gradientClicks.addColorStop(1, 'rgba(20, 184, 166, 0.00)');

        const gradientEarnings = ctx.createLinearGradient(0, 0, 0, 300);
        gradientEarnings.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
        gradientEarnings.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

        const labels = @json($dates);
        const clicksData = @json($clicksArray);
        const earningsData = @json($earningsArray);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Tıklama Sayısı',
                        data: clicksData,
                        borderColor: '#14b8a6',
                        borderWidth: 3,
                        backgroundColor: gradientClicks,
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'yClicks',
                        pointBackgroundColor: '#14b8a6',
                        pointBorderColor: '#0f172a',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Net Kazanç (TL)',
                        data: earningsData,
                        borderColor: '#6366f1',
                        borderWidth: 3,
                        backgroundColor: gradientEarnings,
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'yEarnings',
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#0f172a',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#94a3b8',
                            font: {
                                family: 'Inter',
                                size: 12,
                                weight: '500'
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        padding: 12,
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255, 255, 255, 0.08)',
                        borderWidth: 1,
                        usePointStyle: true,
                        titleFont: {
                            family: 'Outfit',
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Inter',
                            size: 12
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: 'Inter',
                                size: 10,
                                weight: '500'
                            }
                        }
                    },
                    yClicks: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: {
                            color: 'rgba(255, 255, 255, 0.03)'
                        },
                        ticks: {
                            color: '#64748b',
                            stepSize: 5,
                            font: {
                                family: 'Inter',
                                size: 10
                            }
                        },
                        title: {
                            display: true,
                            text: 'Tıklamalar',
                            color: '#14b8a6',
                            font: {
                                family: 'Outfit',
                                size: 10,
                                weight: 'bold'
                            }
                        }
                    },
                    yEarnings: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: 'Inter',
                                size: 10
                            }
                        },
                        title: {
                            display: true,
                            text: 'Kazanç (TL)',
                            color: '#6366f1',
                            font: {
                                family: 'Outfit',
                                size: 10,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
