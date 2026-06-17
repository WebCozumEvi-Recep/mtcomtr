<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kargo Barkodu - {{ $order->tracking_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        .barcode { font-family: 'Libre Barcode 128', cursive; font-size: 70px; }
        @media print {
            @page { size: 100mm 150mm; margin: 0; }
            .no-print { display: none; }
            body { padding: 0; margin: 0; background: white; }
            .label-page { border: none; box-shadow: none; margin: 0; padding: 5mm; width: 100mm; height: 150mm; }
        }
        body { background: #f1f5f9; font-family: 'Inter', sans-serif; padding: 20px; }
        .label-page { background: white; width: 100mm; height: 150mm; margin: 0 auto; border: 1px solid #000; padding: 6mm; display: flex; flex-direction: column; color: black; overflow: hidden; }
        .dashed-line { border-top: 1px dashed #000; margin: 3mm 0; }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print mb-6 text-center">
        <button onclick="window.print()" class="bg-slate-900 text-white font-black px-8 py-4 rounded-2xl shadow-xl hover:scale-105 transition">YAZDIRMAYI BAŞLAT</button>
    </div>

    <div class="label-page">
        <!-- Header Info -->
        <div class="text-center mb-3 shrink-0">
            <div class="text-2xl font-black uppercase tracking-tight">{{ $order->cargo_firm }} KARGO</div>
            <div class="text-[11px] font-bold mt-1">Sipariş No: {{ $order->order_number }}</div>
            <div class="text-[11px] font-bold mt-0.5">Entegrasyon Kodu: {{ $order->tracking_number }}</div>
            <div class="text-[11px] font-bold mt-0.5">Talep Numarası: {{ $order->shipment_id ?? '7000946' }}</div>
            <div class="text-[11px] font-black mt-1 uppercase">Ödeme: KAPIDA ÖDEME</div>
        </div>

        <!-- Barcode Area -->
        <div class="text-center py-3 border-t border-b border-black my-2 shrink-0">
            <div class="barcode leading-none">{{ $order->tracking_number }}</div>
            <div class="text-xs font-bold tracking-[0.2em] mt-1">{{ $order->tracking_number }}</div>
        </div>

        <!-- Addresses -->
        <div class="flex-1 space-y-3 overflow-hidden">
            <!-- Sender -->
            <div class="flex gap-4">
                <div class="font-black text-sm w-20 shrink-0">Gönderici</div>
                <div class="text-[10px] font-bold leading-tight uppercase truncate">
                    {{ $order->domain->domain_name ?? 'PANEL' }}<br>
                    İSTANBUL / TÜRKİYE
                </div>
            </div>

            <div class="dashed-line"></div>

            <!-- Receiver -->
            <div class="flex gap-4">
                <div class="font-black text-sm w-20 shrink-0">Alıcı</div>
                <div class="flex-1 min-w-0">
                    <div class="text-base font-black uppercase mb-0.5 truncate">{{ $order->customer->full_name ?? '-' }}</div>
                    <div class="text-[10px] font-bold leading-relaxed uppercase">
                        {{ $order->address }}<br>
                        {{ $order->district }} / {{ $order->city }}<br>
                        {{ $order->customer->phone ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Warning Footer -->
        <div class="mt-2 pt-2 border-t border-black text-center shrink-0">
            <p class="text-[9px] font-black leading-tight uppercase">
                {{ $order->cargo_firm }} Kargo Şubesi Dikkatine: Kargo barkodunu, Barkod Destekli Fatura menüsünden, yukarıda bulunan Talep Numarası girilerek okutulması gerekmektedir.
            </p>
        </div>
    </div>
</body>
</html>
