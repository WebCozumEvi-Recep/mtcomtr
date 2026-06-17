@extends('layouts.affiliate')

@section('title', 'Ödemeler')

@section('content')
<div class="mb-8">
    <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight font-display">Hak Ediş & Ödeme Talepleri</h2>
    <p class="text-slate-400 text-xs md:text-sm mt-1">Kazandığınız komisyonları takip edebilir, banka IBAN bilgilerinizi güncelleyerek ödeme talebi gönderebilirsiniz.</p>
</div>

<!-- Grid Balance Metrics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Checkable Withdrawable Balance -->
    <div class="glassmorphic-card p-6 border-l-4 border-teal-500 relative overflow-hidden">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Çekilebilir Bakiyeniz</p>
        <h3 class="text-3xl font-extrabold text-white font-display">{{ number_format($withdrawableBalance, 2) }} <span class="text-sm font-bold text-slate-400">TL</span></h3>
        <p class="text-[10px] text-teal-400 font-semibold mt-1">Ödeme talebi oluşturmaya hazır.</p>
    </div>

    <!-- Processing Balance -->
    <div class="glassmorphic-card p-6 border-l-4 border-sky-500 relative overflow-hidden">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">İşlemdeki Talepleriniz</p>
        <h3 class="text-3xl font-extrabold text-white font-display">{{ number_format($withdrawingBalance, 2) }} <span class="text-sm font-bold text-slate-400">TL</span></h3>
        <p class="text-[10px] text-sky-400 font-semibold mt-1">İnceleme ve onay sürecindedir.</p>
    </div>

    <!-- Total Paid Out -->
    <div class="glassmorphic-card p-6 border-l-4 border-indigo-500 relative overflow-hidden">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Toplam Ödenen</p>
        <h3 class="text-3xl font-extrabold text-white font-display">{{ number_format($totalPaidOut, 2) }} <span class="text-sm font-bold text-slate-400">TL</span></h3>
        <p class="text-[10px] text-indigo-400 font-semibold mt-1">Hesabınıza gönderilen toplam kazanç.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Payout Request Form -->
    <div class="glassmorphic-card p-6 md:p-8 h-fit lg:col-span-1">
        <h3 class="text-base font-bold text-white tracking-wide font-display mb-2">Ödeme Talebi Gönder</h3>
        <p class="text-xs text-slate-400 mb-6">Asgari ödeme limiti olan 500 TL'yi aştığınızda talep gönderebilirsiniz.</p>

        @if($withdrawableBalance >= 500.00)
            <form method="POST" action="{{ route('affiliate.withdrawals.request') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">VERGİ PROFİLİNİZ</label>
                    <div class="p-3.5 rounded-xl bg-slate-950/60 border border-white/5 text-slate-300 text-xs leading-relaxed">
                        @if($affiliate->tax_type === 'individual')
                            Brüt hak edişiniz üzerinden %{{ \App\Models\Setting::val('affiliate_withholding_rate', 20) }} stopaj kesintisi ile bireysel ödeme yapılır.
                        @elseif($affiliate->tax_type === 'company')
                            Net hak edişinize +%{{ \App\Models\Setting::val('affiliate_vat_rate', 20) }} KDV eklenerek şirketiniz adına faturalandırılır.
                        @else
                            Muafiyetli/Vergisiz transfer.
                        @endif
                    </div>
                </div>

                <div>
                    <label for="iban" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">BANKA IBAN NUMARANIZ</label>
                    <input type="text" name="iban" id="iban" required value="{{ old('iban', $affiliate->iban) }}"
                           class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all font-mono">
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-6 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs tracking-wide transition-all hover:shadow-lg hover:shadow-teal-500/10">
                    Ödeme Talebi Oluştur ({{ number_format($withdrawableBalance, 2) }} TL)
                </button>
            </form>
        @else
            <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs leading-relaxed flex flex-col gap-2">
                <div class="flex items-center gap-2 font-bold uppercase tracking-wider text-[10px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Limit Yetersiz
                </div>
                <span>Ödeme alabilmek için çekilebilir bakiyenizin en az 500.00 TL olması gerekmektedir. Şu anki bakiyeniz: <b>{{ number_format($withdrawableBalance, 2) }} TL</b>.</span>
            </div>
        @endif
    </div>

    <!-- Payout Requests History Ledger -->
    <div class="glassmorphic-card p-6 md:p-8 lg:col-span-2">
        <h3 class="text-base font-bold text-white tracking-wide font-display mb-6">Ödeme Talepleri Geçmişi</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <th class="pb-3">Tarih</th>
                        <th class="pb-3">Ödeme IBAN</th>
                        <th class="pb-3 text-right">Net Ödeme</th>
                        <th class="pb-3 text-right">Vergi Detayları</th>
                        <th class="pb-3 text-center">Durum</th>
                        <th class="pb-3 text-right">Dekont</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    @forelse($requests as $req)
                        <tr class="group hover:bg-white/[0.01] transition-colors">
                            <td class="py-4 text-xs font-semibold">
                                {{ $req->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="py-4 text-xs font-mono text-slate-400">
                                {{ $req->iban }}
                            </td>
                            <td class="py-4 text-right font-display font-extrabold text-xs text-teal-400">
                                {{ number_format($req->net_payment, 2) }} TL
                            </td>
                            <td class="py-4 text-right text-[10px] text-slate-500 leading-normal">
                                <span class="block">Brüt: {{ number_format($req->gross_amount, 2) }} TL</span>
                                @if($req->withholding_amount > 0)
                                    <span class="block text-rose-500/70">Stopaj: -{{ number_format($req->withholding_amount, 2) }} TL</span>
                                @elseif($req->vat_amount > 0)
                                    <span class="block text-teal-500/70">KDV: +{{ number_format($req->vat_amount, 2) }} TL</span>
                                @endif
                            </td>
                            <td class="py-4 text-center">
                                @if($req->status === 'pending')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-500/10 border border-amber-500/20 text-amber-400 uppercase">Onay Bekliyor</span>
                                @elseif($req->status === 'paid')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-teal-500/10 border border-teal-500/20 text-teal-400 uppercase">Ödendi</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-500/10 border border-rose-500/20 text-rose-400 uppercase">Reddedildi</span>
                                @endif
                            </td>
                            <td class="py-4 text-right">
                                @if($req->receipt_path)
                                    <a href="{{ asset($req->receipt_path) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-[10px] font-bold text-teal-400 hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Dekont İndir
                                    </a>
                                @else
                                    <span class="text-[10px] text-slate-500">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500 text-xs font-medium">Henüz bir ödeme talebiniz bulunmamaktadır.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="mt-6 pt-4 border-t border-white/5">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
