@extends('layouts.admin')

@section('title', 'Risk (Fraud) Analizi')

@section('content')
<div class="flex flex-col gap-6 fade-in-up">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter flex items-center">
                    <svg class="w-6 h-6 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Risk Analizi
                </h2>
                <button 
                    x-data="favoriteManager" 
                    @click="toggle('admin.risk', 'Risk Analizi', '{{ route('admin.risk') }}', 'shield-exclamation')"
                    class="favorite-btn"
                    style="display: inline-flex !important; min-width: 32px; min-height: 32px;"
                    title="Hızlı İşlemlere Ekle/Kaldır"
                >
                    <svg data-favorite-id="admin.risk" class="w-6 h-6 {{ (auth()->user()->favorites && collect(auth()->user()->favorites)->contains('id', 'admin.risk')) ? 'text-amber-400' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-slate-500 font-medium">Yüksek risk skoruna sahip veya şüpheli görülen siparişler</p>
        </div>
    </div>

    <!-- Warning Banner -->
    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm flex items-start">
        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="font-medium text-sm">
            <span class="font-bold">Dikkat:</span> Bu listedeki siparişler, API tarafından yapılan analizde mükerrer sipariş, IP uyuşmazlığı veya cihaz izinden dolayı yüksek risk ("High Risk") olarak işaretlenmiştir. Kargo onayından önce teyit araması tavsiye edilir.
        </div>
    </div>

    <!-- Data Table -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="premium-card overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Şüpheli / Yüksek Riskli Siparişler</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500">
                            <tr>
                                <th class="px-6 py-4 border-b border-slate-200">Sipariş Tarihi</th>
                                <th class="px-6 py-4 border-b border-slate-200">Sipariş No</th>
                                <th class="px-6 py-4 border-b border-slate-200">Müşteri</th>
                                <th class="px-6 py-4 border-b border-slate-200 text-center">Risk Skoru</th>
                                <th class="px-6 py-4 border-b border-slate-200 text-right">Eylem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($orders as $order)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs font-bold text-slate-900">{{ $order->created_at->format('d.m.Y') }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $order->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-slate-900 hover:text-brand-600">#{{ $order->order_number }}</a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $order->customer->full_name ?? '-' }}</div>
                                        <div class="text-xs text-slate-400">{{ $order->customer->phone ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center bg-red-100 text-red-700 px-3 py-1 rounded-full font-bold text-xs shadow-sm">
                                            {{ $order->fraud_score ?? '95' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold bg-white text-slate-700 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition shadow-sm border border-slate-200">İncele</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500 font-medium italic">
                                        Şu an için analiz bekleyen yüksek riskli işlem bulunmuyor.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                <div class="p-3 border-t border-slate-100">
                    {{ $orders->links() }}
                </div>
                @endif
            </div>
        </div>

        <div class="xl:col-span-1">
            <div class="premium-card overflow-hidden h-full flex flex-col">
                <div class="p-4 border-b border-slate-100 bg-red-50 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    <h3 class="text-sm font-black text-red-900 uppercase tracking-widest">Kara Liste</h3>
                </div>
                <div class="p-2 flex-grow overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 font-bold text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3">Müşteri</th>
                                <th class="px-4 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($blacklistedCustomers as $customer)
                                <tr class="hover:bg-red-50/30 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-900">{{ $customer->full_name }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $customer->phone }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <form action="{{ route('admin.customers.toggle-blacklist', $customer) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-indigo-600 font-bold hover:underline">Aç</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-10 text-center text-slate-400 italic">
                                        Boş.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="xl:col-span-1">
            <!-- KRİTİK UYARILAR -->
            <div class="premium-card overflow-hidden bg-white border border-slate-200 h-full flex flex-col">
                <div class="px-6 py-4 border-b border-slate-100 bg-rose-50/50 flex items-center justify-between">
                    <h3 class="text-[11px] font-black text-rose-800 uppercase tracking-widest flex items-center gap-2">
                        KRİTİK UYARILAR
                    </h3>
                    <span id="alert-count" class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-white text-[10px] font-black">{{ count($criticalAlerts) }}</span>
                </div>
                <div class="p-2 space-y-1 flex-grow overflow-y-auto custom-scrollbar">
                    @forelse($criticalAlerts as $alert)
                    <div data-alert-key="{{ $alert['key'] }}" class="flex items-start justify-between gap-2 p-3 rounded-xl hover:bg-slate-50 transition-colors group alert-item">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 w-1.5 h-1.5 rounded-full {{ ($alert['type'] ?? '') === 'danger' ? 'bg-rose-500' : 'bg-amber-500' }} shrink-0"></div>
                            <p class="text-[11px] font-bold text-slate-700 leading-tight">{!! $alert['message'] !!}</p>
                        </div>
                        <button onclick="hideAlert('{{ $alert['key'] }}', {{ $alert['is_system'] ?? 'false' ? ($alert['id'] ?? 'null') : 'null' }}, this)" class="text-slate-300 hover:text-rose-500 transition p-0.5" title="Gizle">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 italic text-xs">
                        Kritik uyarı bulunmuyor.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hiddenAlerts = JSON.parse(localStorage.getItem('hidden_alerts') || '[]');
        document.querySelectorAll('.alert-item').forEach(item => {
            if (hiddenAlerts.includes(item.dataset.alertKey)) {
                item.remove();
            }
        });
        updateAlertCount();
    });

    function hideAlert(key, systemId, btn) {
        const alertItem = btn.closest('.alert-item');
        
        if (systemId) {
            fetch(`/admin/alerts/${systemId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
        }

        const hiddenAlerts = JSON.parse(localStorage.getItem('hidden_alerts') || '[]');
        if (!hiddenAlerts.includes(key)) {
            hiddenAlerts.push(key);
            localStorage.setItem('hidden_alerts', JSON.stringify(hiddenAlerts));
        }

        alertItem.style.opacity = '0';
        alertItem.style.transform = 'translateX(20px)';
        setTimeout(() => {
            alertItem.remove();
            updateAlertCount();
        }, 300);
    }

    function updateAlertCount() {
        const count = document.querySelectorAll('.alert-item').length;
        const countEl = document.getElementById('alert-count');
        if (countEl) {
            countEl.textContent = count;
            if (count === 0) {
                const container = countEl.closest('.premium-card').querySelector('.p-2');
                if (container) container.innerHTML = '<div class="p-8 text-center text-slate-400 italic text-xs">Kritik uyarı bulunmuyor.</div>';
            }
        }
    }
</script>
</div>
@endsection
