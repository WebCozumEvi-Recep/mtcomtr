<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toplu Sipariş Yazdır</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 0;
        }
        @media print {
            @page { 
                size: A5 landscape; 
                margin: 0; 
            }
            body { 
                background: white; 
            }
            .no-print { display: none !important; }
            .order-page {
                page-break-after: always;
                height: 148mm;
                width: 210mm;
                margin: 0 !important;
                padding: 6mm !important;
                box-shadow: none !important;
                border: none !important;
                display: flex;
                flex-direction: column;
                box-sizing: border-box;
            }
        }
        .order-page {
            background: white;
            width: 210mm;
            height: 148mm;
            margin: 10px auto;
            padding: 6mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-sizing: border-box;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-50">
        <div class="font-black tracking-tight uppercase">YAZDIRMA ÖNİZLEME ({{ count($orders) }} Sipariş)</div>
        <div class="flex gap-4">
            <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-8 py-2 rounded-xl font-black text-sm transition shadow-lg shadow-green-600/20">YAZDIR</button>
            <button onclick="window.close()" class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2 rounded-xl font-bold text-sm transition">KAPAT</button>
        </div>
    </div>

    @foreach($orders as $order)
        <div class="order-page">
            <!-- Header Section (Ultra Compact) -->
            <div class="border-[1.5pt] border-slate-900 p-2 mb-2">
                <div class="flex justify-between items-center mb-2">
                    <div class="w-1/4">
                        <div class="text-[8pt] font-black text-slate-900 uppercase">SİPARİŞ:</div>
                        <div class="text-[7pt] font-bold text-slate-600">{{ $order->order_number }}</div>
                    </div>
                    <div class="w-2/4 flex flex-col items-center">
                        @php $barcodeData = $order->tracking_number ?: $order->order_number; @endphp
                        <img src="https://barcodeapi.org/api/128/{{ $barcodeData }}?width=250&height=40" class="h-10 object-contain" alt="Barcode">
                        <div class="text-[7pt] font-bold text-slate-900 mt-0.5">{{ $barcodeData }}</div>
                        <div class="text-base font-black text-slate-900 uppercase tracking-tighter leading-none">{{ $order->cargo_firm ?: 'YURTİÇİ KARGO' }}</div>
                    </div>
                    <div class="w-1/4 text-right">
                        <div class="text-[8pt] font-black text-slate-900 uppercase">TARİH:</div>
                        <div class="text-[7pt] font-bold text-slate-600">{{ $order->created_at->format('d.m.Y') }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-0 border-t border-slate-900">
                    <div class="p-2 border-r border-slate-900">
                        <div class="text-[7pt] font-black text-slate-400 uppercase tracking-widest mb-0.5">ALICI BİLGİLERİ:</div>
                        <div class="text-[14pt] font-black text-slate-900 leading-tight">{{ $order->customer->full_name ?? '-' }}</div>
                        <div class="text-[10pt] font-bold text-slate-800">{{ $order->customer->phone ?? '-' }}</div>
                    </div>
                    <div class="p-2">
                        <div class="text-[7pt] font-black text-slate-400 uppercase tracking-widest mb-0.5">TESLİMAT ADRESİ:</div>
                        <div class="text-[8.5pt] font-bold text-slate-900 leading-tight uppercase line-clamp-2">{{ $order->address }}</div>
                        <div class="text-[11pt] font-black text-slate-900 uppercase tracking-tight mt-0.5">{{ $order->district }} / {{ $order->city }}</div>
                    </div>
                </div>
            </div>

            <!-- Warehouse Info Section (Compact) -->
            <div class="border border-slate-900 rounded-lg p-2 mb-2 bg-slate-50">
                <div class="text-[7pt] font-black text-slate-400 uppercase tracking-widest mb-1">📦 DEPO / PAKET İÇERİĞİ:</div>
                <div class="flex justify-between items-center">
                    @php 
                        $product = $order->domain->products->first(); 
                        $quantity = $order->offer->quantity ?? 1;
                    @endphp
                    <div class="text-[10pt] font-black text-slate-900">
                        <span class="text-brand-600">[{{ $product->sku ?? 'NO-SKU' }}]</span> - {{ $product->name ?? 'Ana Ürün' }}
                    </div>
                    <div class="text-[16pt] font-black text-slate-900 ml-4">
                        {{ $quantity }} ADET
                    </div>
                </div>
            </div>

            <div class="mt-auto">
                <!-- Payment Box (Side-by-side, Reduced Size) -->
                <div class="border-[3pt] border-red-600 rounded-xl bg-red-50/10 p-3 flex items-center justify-center gap-6">
                    <div class="text-[36pt] font-black text-red-600 tracking-tighter leading-none flex items-baseline">
                        {{ number_format($order->grand_total, 2, ',', '.') }} <span class="text-[18pt] ml-2 font-black">TL</span>
                    </div>
                    <div class="text-[18pt] font-black text-red-600 uppercase tracking-tight leading-none border-l-2 border-red-600 pl-6 py-1">
                        KAPIDA ÖDEME
                    </div>
                </div>
                <!-- Domain Footer -->
                <div class="text-center mt-2">
                    <div class="text-[9pt] font-black text-slate-900 tracking-[0.2em] lowercase opacity-60">{{ $order->domain->domain_name ?? '-' }}</div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="no-print fixed bottom-10 right-10">
        <button onclick="window.print()" class="bg-green-600 text-white w-20 h-20 rounded-full shadow-2xl flex items-center justify-center hover:scale-110 transition active:scale-95 group">
            <svg class="w-10 h-10 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        </button>
    </div>
</body>
</html>
