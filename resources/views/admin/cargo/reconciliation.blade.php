@extends('layouts.admin')

@section('title', 'Kargo Mutabakatı')

@section('content')
<style>
    @media print {
        header, aside, .no-print, .premium-card > div:not(.overflow-x-auto) { display: none !important; }
        .premium-card { border: none !important; box-shadow: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
        .lg\:col-span-1, .action-column { display: none !important; }
        .lg\:col-span-3 { width: 100% !important; }
        table { width: 100% !important; border-collapse: collapse !important; }
        th, td { border: 1px solid #e2e8f0 !important; padding: 8px !important; }
    }
    .modal-backdrop { background-color: rgba(15, 23, 42, 0.5); backdrop-filter: blur(8px); }
    .tr-reconciled { background-color: #f0fdf4; }
</style>

<div class="fade-in-up space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 no-print">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Kargo Mutabakatı</h2>
                <button 
                    x-data="favoriteManager" 
                    @click="toggle('admin.cargo.reconciliation', 'Kargo Mutabakatı', '{{ route('admin.cargo.reconciliation') }}', 'check-square')"
                    class="favorite-btn"
                    style="display: inline-flex !important; min-width: 32px; min-height: 32px;"
                    title="Hızlı İşlemlere Ekle/Kaldır"
                >
                    <svg data-favorite-id="admin.cargo.reconciliation" class="w-6 h-6 {{ (auth()->user()->favorites && collect(auth()->user()->favorites)->contains('id', 'admin.cargo.reconciliation')) ? 'text-amber-400' : 'text-slate-400' }}" fill="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                </button>
            </div>
            <p class="text-sm text-slate-500 font-medium">Teslim edilen siparişlerin ödeme mutabakatını yapın.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Range Selector -->
            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
                @php $currentRange = request('range', 'all'); @endphp
                <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->all(), ['range' => 'today'])) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'today' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Günlük</a>
                <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->all(), ['range' => 'this_week'])) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'this_week' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Haftalık</a>
                <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->all(), ['range' => 'this_month'])) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'this_month' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Aylık</a>
                <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->all(), ['range' => 'this_year'])) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'this_year' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Yıllık</a>
                <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->all(), ['range' => 'all'])) }}" class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-lg transition {{ $currentRange === 'all' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">Tümü</a>
            </div>

            <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-2">
                @foreach(request()->except(['range', 'date']) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <input type="hidden" name="range" value="{{ request('range', 'all') }}">
                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm focus:ring-2 focus:ring-brand-500 transition outline-none">
            </form>
            
            <button onclick="location.reload()" class="p-2.5 bg-white border border-slate-200 rounded-xl text-slate-500 hover:text-brand-600 shadow-sm hover:shadow transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </button>

            <div class="h-8 w-px bg-slate-200 mx-2"></div>

            <div class="flex flex-wrap gap-3">
                @if(auth()->user()->hasPermission('cargo.edit'))
                <div class="bg-brand-50 border border-brand-100 p-2 rounded-xl flex items-center gap-3 group cursor-pointer hover:bg-brand-100 transition">
                    <div class="bg-brand-600 text-white p-2 rounded-lg shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[9px] font-black text-brand-800 uppercase tracking-widest">Excel Dosyası</div>
                        <div class="text-[10px] font-bold text-brand-600">Mutabakat Yükle</div>
                    </div>
                </div>
                @endif
                <button onclick="window.print()" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-xs font-black hover:bg-black transition shadow-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Yazdır
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Area -->
    <div class="premium-card p-4 no-print">
        <form action="{{ route('admin.cargo.reconciliation') }}" method="GET" class="flex flex-wrap gap-4 items-center">
            <input type="hidden" name="filter" value="1">
            
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Sipariş No, Ad, Telefon..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-sm font-medium">
            </div>

            <select name="cargo_firm" class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-brand-500 shadow-sm">
                <option value="">Tüm Firmalar</option>
                @foreach(['aras', 'yurtici', 'mng', 'surat', 'ptt', 'dhl'] as $f)
                    <option value="{{ $f }}" {{ request('cargo_firm') == $f ? 'selected' : '' }}>{{ strtoupper($f) }}</option>
                @endforeach
            </select>

            <label class="flex items-center gap-2 cursor-pointer select-none bg-slate-50 px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
                <input type="checkbox" name="hide_paid" value="1" {{ (!request()->has('filter') || request()->has('hide_paid')) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500">
                <span class="text-xs font-black text-slate-600 uppercase tracking-tighter">Onaylananları Gizle</span>
            </label>

            <button type="submit" class="bg-slate-800 text-white font-bold px-6 py-2 rounded-xl text-sm hover:bg-black transition shadow-md" style="color:white!important;">Süz</button>
            
            @if(request()->has('filter'))
                <a href="{{ route('admin.cargo.reconciliation') }}" class="text-slate-400 hover:text-red-500 transition" title="Temizle">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table id="reconciliationTable" class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 text-[10px] uppercase font-black text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="p-4 border-r border-slate-100">Tarih</th>
                        <th class="p-4 border-r border-slate-100">Sipariş No</th>
                        <th class="p-4 border-r border-slate-100">Kargo / Takip</th>
                        <th class="p-4 border-r border-slate-100">Müşteri</th>
                        <th class="p-4 border-r border-slate-100">Tutar</th>
                        <th class="p-4 border-r border-slate-100 text-center">Durum</th>
                        <th class="p-4 text-right action-column">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-xs font-bold text-slate-700">
                    @forelse($orders as $order)
                    @php
                        $isPaid = $order->payment_status == 'reconciled';
                        $productName = $order->domain->products->first()->name ?? ($order->offer->offer_name ?? 'Ürün Paketi');
                        $quantityText = ($order->offer->quantity ?? 1) . ' Adet';
                        $customerName = $order->customer->full_name;
                        $addressData = $order->address;
                        $locationData = ($order->district ?? '') . ' / ' . ($order->city ?? '');
                    @endphp
                    <tr class="hover:bg-slate-50 transition {{ $isPaid ? 'tr-reconciled' : '' }}">
                        <td class="p-4 border-r border-slate-50 whitespace-nowrap">
                            <span class="text-slate-400 text-[10px]">{{ $order->created_at->format('d/m/Y') }}</span>
                            <div class="text-[9px] text-slate-300">{{ $order->created_at->format('H:i') }}</div>
                        </td>
                        <td class="p-4 border-r border-slate-50">
                            <button onclick="showPopDetail({{ json_encode($order->order_number) }}, {{ json_encode($productName) }}, {{ json_encode($quantityText) }}, {{ json_encode($order->domain->domain_name ?? '-') }})" class="text-brand-600 font-extrabold hover:underline whitespace-nowrap">
                                {{ $order->order_number }}
                            </button>
                        </td>
                        <td class="p-4 border-r border-slate-50">
                            <div class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ $order->cargo_firm }}</div>
                            <button onclick="openAddrModal({{ json_encode($customerName) }}, {{ json_encode($addressData) }}, {{ json_encode($locationData) }})" class="font-mono text-indigo-600 hover:underline">
                                {{ $order->tracking_number ?? 'BİLGİ YOK' }}
                            </button>
                        </td>
                        <td class="p-4 border-r border-slate-50">
                            <div class="truncate max-w-[150px]">{{ $customerName }}</div>
                            <div class="text-[9px] text-slate-400 font-medium">{{ $order->customer->phone }}</div>
                        </td>
                        <td class="p-4 border-r border-slate-50 font-black text-slate-900 whitespace-nowrap">₺{{ number_format($order->grand_total, 2) }}</td>
                        <td class="p-4 border-r border-slate-50 text-center">
                            @if($isPaid)
                                <div class="flex items-center justify-center text-green-600 gap-1 animate-in zoom-in duration-300">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span class="text-[8px] font-black uppercase">TAMAM</span>
                                </div>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase bg-amber-100 text-amber-700">BEKLEYEN</span>
                            @endif
                        </td>
                        <td class="p-4 text-right action-column whitespace-nowrap">
                            @if(auth()->user()->hasPermission('cargo.edit'))
                                @if($isPaid)
                                    <form action="{{ route('admin.cargo.reconciliation.unpaid', $order) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-rose-50 text-rose-600 px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition shadow-sm">İptal</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.cargo.reconciliation.paid', $order) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-brand-600/20 hover:bg-brand-700 transition active:scale-95">Onayla</button>
                                    </form>
                                @endif
                            @else
                                <span class="text-[9px] font-black text-slate-300 uppercase italic">Yetki Yok</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-16 text-center text-slate-300 italic font-bold tracking-widest">Kayıt Bulunamadı</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Structure -->
<div id="reconModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 modal-backdrop no-print">
    <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="modalWindow">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 id="modalTitle" class="text-xs font-black text-slate-900 uppercase tracking-widest">Detay</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="modalBody" class="p-8"></div>
        <div class="p-6 bg-slate-50/50 border-t border-slate-100 text-right">
            <button onclick="closeModal()" class="bg-slate-900 text-white px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest active:scale-95 transition">KAPAT</button>
        </div>
    </div>
</div>

<script>
function showPopDetail(no, prod, qty, domain) {
    document.getElementById('modalTitle').innerText = no + ' - SIPARISETILEN URÜN';
    document.getElementById('modalBody').innerHTML = `
        <div class="space-y-6">
            <div class="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Orjinal Ürün Adı</div>
                <div class="text-base font-black text-slate-900 leading-tight">${prod}</div>
            </div>
            <div class="flex gap-4">
                <div class="flex-1 bg-brand-50 p-5 rounded-3xl border border-brand-100/50 text-center">
                    <div class="text-[10px] font-black text-brand-400 uppercase tracking-widest mb-1">Miktar</div>
                    <div class="text-xl font-black text-brand-700">${qty}</div>
                </div>
                <div class="flex-1 bg-indigo-50 p-5 rounded-3xl border border-indigo-100/50 text-center">
                    <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Domain</div>
                    <div class="text-[11px] font-black text-indigo-700 break-all leading-tight">${domain}</div>
                </div>
            </div>
        </div>
    `;
    openModal();
}

function openAddrModal(name, addr, loc) {
    document.getElementById('modalTitle').innerText = 'TESLIMAT ADRES BILGISI';
    document.getElementById('modalBody').innerHTML = `
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Alıcı</div>
                    <div class="font-black text-slate-900 text-lg tracking-tight">${name}</div>
                </div>
            </div>
            <div class="bg-indigo-50/50 p-6 rounded-[2.5rem] border border-indigo-100/50">
                <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3 italic">Açık Adres</div>
                <div class="text-sm font-bold text-slate-800 leading-relaxed">${addr}</div>
                <div class="mt-4 pt-4 border-t border-indigo-100/30 text-xs font-black text-indigo-700 uppercase tracking-widest">${loc}</div>
            </div>
        </div>
    `;
    openModal();
}

function openModal() {
    const backdrop = document.getElementById('reconModal');
    const window = document.getElementById('modalWindow');
    backdrop.classList.remove('hidden');
    setTimeout(() => {
        window.classList.remove('scale-95', 'opacity-0');
    }, 10);
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const backdrop = document.getElementById('reconModal');
    const window = document.getElementById('modalWindow');
    window.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        backdrop.classList.add('hidden');
    }, 200);
    document.body.style.overflow = 'auto';
}

function exportToExcel() {
    let originalTable = document.getElementById("reconciliationTable");
    let clonedTable = originalTable.cloneNode(true);
    let rows = clonedTable.rows;
    for (let i = 0; i < rows.length; i++) {
        rows[i].deleteCell(rows[i].cells.length - 1);
    }
    let html = clonedTable.outerHTML;
    let url = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
    let link = document.createElement("a");
    link.download = "kargo_mutabakat_" + (new Date().toISOString().split('T')[0]) + ".xls";
    link.href = url;
    link.click();
}
</script>
@endsection
