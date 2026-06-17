@extends('admin.layout')

@section('title', 'Komisyon Hak Edişleri - Admin Panel')

@section('extra_css')
<style>
/* ════════════════════════════════
   PAGE
════════════════════════════════ */
.cp-page { display:flex; flex-direction:column; gap:1.4rem; }

.cp-header { display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:.75rem; }
.cp-header h1 { font-size:1.45rem; font-weight:800; color:#0f172a; margin:0 0 .2rem; letter-spacing:-.5px; }
.cp-header p  { font-size:.82rem; color:#64748b; margin:0; }

/* ════════════════════════════════
   FLASH
════════════════════════════════ */
.cp-flash { border-radius:12px; padding:.8rem 1.1rem; font-size:.875rem; font-weight:500; display:flex; align-items:center; gap:.6rem; }
.cp-flash-success { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.cp-flash-error   { background:#fff1f2; color:#be123c; border:1px solid #fecdd3; }

/* ════════════════════════════════
   FILTER CARD
════════════════════════════════ */
.cp-filter {
    background:#fff; border:1px solid #f1f5f9; border-radius:16px;
    padding:1rem 1.25rem; box-shadow:0 1px 3px rgba(15,23,42,.05);
}
.cp-filter form { display:flex; align-items:flex-end; gap:.75rem; flex-wrap:wrap; }
.cp-filter-field { display:flex; flex-direction:column; gap:.3rem; flex:1; min-width:160px; }
.cp-filter-field label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#64748b; }
.cp-filter-field .form-select {
    border-radius:10px; border:1px solid #e2e8f0; font-size:.84rem;
    padding:.46rem .85rem; box-shadow:none; background:#fafafa; color:#334155;
    transition:border-color .2s, box-shadow .2s; height:38px;
}
.cp-filter-field .form-select:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); background:#fff; outline:none; }
.btn-cp-filter {
    background:#6366f1; color:#fff; border:none; border-radius:10px;
    padding:.46rem 1.2rem; font-size:.82rem; font-weight:700;
    display:inline-flex; align-items:center; gap:.4rem; cursor:pointer;
    transition:background .2s; white-space:nowrap; text-decoration:none; height:38px;
}
.btn-cp-filter:hover { background:#4f46e5; color:#fff; }

/* ════════════════════════════════
   TABS
════════════════════════════════ */
.cp-tabs {
    display:flex; gap:.4rem;
    background:#fff; border:1px solid #f1f5f9; border-radius:14px;
    padding:.4rem; box-shadow:0 1px 3px rgba(15,23,42,.05);
}
.cp-tab {
    flex:1; text-align:center; padding:.5rem 1rem; border-radius:10px;
    font-size:.82rem; font-weight:700; cursor:pointer; border:none;
    background:transparent; color:#64748b; transition:all .2s;
    display:flex; align-items:center; justify-content:center; gap:.4rem;
}
.cp-tab.active { background:#6366f1; color:#fff; box-shadow:0 2px 8px rgba(99,102,241,.25); }
.cp-tab:hover:not(.active) { background:#f1f5f9; color:#334155; }

/* ════════════════════════════════
   TAB PANELS
════════════════════════════════ */
.cp-panel { display:none; }
.cp-panel.active { display:block; }

/* ════════════════════════════════
   TABLE CARD
════════════════════════════════ */
.cp-card {
    background:#fff; border:1px solid #f1f5f9; border-radius:16px;
    box-shadow:0 1px 3px rgba(15,23,42,.05); overflow:hidden;
}
.cp-table-wrap { overflow-x:auto; }
.cp-table {
    width:100%; border-collapse:collapse; font-size:.82rem; min-width:900px;
}
.cp-table thead tr {
    background:#f8fafc; border-bottom:2px solid #f1f5f9;
}
.cp-table thead th {
    padding:.75rem 1rem; font-size:.68rem; font-weight:800;
    text-transform:uppercase; letter-spacing:.5px; color:#64748b;
    white-space:nowrap; border:none;
}
.cp-table tbody tr { border-bottom:1px solid #f8fafc; transition:background .15s; }
.cp-table tbody tr:last-child { border-bottom:none; }
.cp-table tbody tr:hover { background:#fafbfc; }
.cp-table tbody td { padding:.75rem 1rem; vertical-align:middle; border:none; color:#334155; }

/* Amount cells */
.amount-gross  { font-family:monospace; font-weight:700; color:#0f172a; font-size:.83rem; }
.amount-tax    { font-family:monospace; font-size:.8rem; }
.amount-tax.deduct { color:#e11d48; }
.amount-tax.add    { color:#2563eb; }
.amount-net    { font-family:monospace; font-weight:800; color:#059669; font-size:.9rem; }
.amount-zero   { color:#94a3b8; font-family:monospace; font-size:.78rem; }

/* Status badges */
.st-badge { font-size:.68rem; font-weight:800; padding:.22rem .6rem; border-radius:20px; white-space:nowrap; }
.st-pending    { background:#fef9c3; color:#a16207; border:1px solid #fde68a; }
.st-approved   { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
.st-rejected   { background:#ffe4e6; color:#be123c; border:1px solid #fecdd3; }
.st-withdrawing{ background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe; }
.st-paid       { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }

/* Manage buttons (inline, no dropdown) */
.manage-grp { display:flex; border-radius:8px; overflow:hidden; border:1px solid #e2e8f0; }
.manage-grp form { display:flex; margin:0; }
.manage-grp form + form { border-left:1px solid #e2e8f0; }
.manage-btn {
    border:none; background:#f8fafc; color:#64748b;
    font-size:.68rem; font-weight:700; padding:.35rem .55rem;
    cursor:pointer; display:flex; align-items:center; gap:.25rem;
    transition:background .15s; white-space:nowrap; line-height:1;
}
.manage-btn:hover { background:#f1f5f9; }
.manage-btn.mb-approve { color:#15803d; }
.manage-btn.mb-approve:hover { background:#dcfce7; }
.manage-btn.mb-reject  { color:#be123c; }
.manage-btn.mb-reject:hover  { background:#ffe4e6; }
.manage-btn.mb-pending { color:#a16207; }
.manage-btn.mb-pending:hover { background:#fef9c3; }
.manage-btn:disabled { opacity:.4; cursor:not-allowed; }

/* ════════════════════════════════
   REPORT / SUMMARY TABLE
════════════════════════════════ */
.rp-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:1rem 1.25rem; border-bottom:1px solid #f1f5f9;
}
.rp-title { font-size:.9rem; font-weight:800; color:#0f172a; }
.rp-sub   { font-size:.75rem; color:#64748b; margin-top:1px; }
.btn-export {
    background:linear-gradient(135deg,#059669,#10b981);
    color:#fff; border:none; border-radius:10px;
    padding:.45rem 1rem; font-size:.78rem; font-weight:700;
    display:inline-flex; align-items:center; gap:.4rem;
    cursor:pointer; transition:opacity .2s; text-decoration:none;
    white-space:nowrap;
}
.btn-export:hover { opacity:.88; color:#fff; }

/* Summary table specific */
.rp-table { min-width:860px; }
.rp-table .col-name  { min-width:160px; }
.rp-table .col-iban  { min-width:200px; font-family:monospace; font-size:.76rem; }
.rp-name { font-weight:700; color:#0f172a; font-size:.84rem; }
.rp-code { font-size:.72rem; color:#94a3b8; font-family:monospace; margin-top:1px; }
.rp-tax-badge { font-size:.68rem; font-weight:700; padding:.18rem .5rem; border-radius:20px; }
.rp-company    { background:#eff6ff; color:#1d4ed8; }
.rp-individual { background:#fef3c7; color:#92400e; }
.rp-none       { background:#f1f5f9; color:#64748b; }

/* Totals row */
.totals-row td { background:#f8fafc; font-weight:800; border-top:2px solid #e2e8f0 !important; color:#0f172a; }
.totals-row td.net-total { color:#059669; font-size:.95rem; }

/* ════════════════════════════════
   EMPTY
════════════════════════════════ */
.cp-empty { padding:4rem 2rem; text-align:center; color:#94a3b8; }
.cp-empty i { font-size:2.5rem; display:block; margin-bottom:.85rem; opacity:.4; }
.cp-empty p { font-size:.9rem; color:#64748b; margin:0; }

/* ════════════════════════════════
   PAGINATION
════════════════════════════════ */
.cp-pager { padding:.85rem 1.25rem; border-top:1px solid #f1f5f9; }
.cp-pager .pagination { margin:0; }

/* Small affiliate info cell */
.aff-cell-name  { font-weight:700; color:#0f172a; font-size:.84rem; }
.aff-cell-code  { font-size:.72rem; color:#94a3b8; font-family:monospace; margin-top:1px; }
.order-cell     { font-size:.8rem; color:#334155; }
.order-cell small { color:#94a3b8; font-size:.7rem; }
</style>
@endsection

@section('content')
<div class="cp-page">

    {{-- ── Header ── --}}
    <div class="cp-header">
        <div>
            <h1>Komisyon Hak Edişleri</h1>
            <p>Tüm siparişlerin affiliate komisyon kayıtları ve ödeme raporu</p>
        </div>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
        <div class="cp-flash cp-flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
    @endif

    {{-- ── Filter ── --}}
    <div class="cp-filter">
        <form method="GET" action="{{ route('admin.affiliate.commissions') }}">
            <div class="cp-filter-field">
                <label>Affiliate Üye</label>
                <select class="form-select" name="affiliate_id">
                    <option value="">Tümü</option>
                    @foreach($affiliates as $aff)
                        <option value="{{ $aff->id }}" {{ request('affiliate_id') == $aff->id ? 'selected' : '' }}>
                            {{ $aff->name }} ({{ $aff->affiliate_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="cp-filter-field">
                <label>Satış Sitesi</label>
                <select class="form-select" name="domain_id">
                    <option value="">Tümü</option>
                    @foreach($domains as $dom)
                        <option value="{{ $dom->id }}" {{ request('domain_id') == $dom->id ? 'selected' : '' }}>
                            {{ $dom->domain_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="cp-filter-field">
                <label>Durum</label>
                <select class="form-select" name="status">
                    <option value="">Tümü</option>
                    <option value="pending"     {{ request('status')==='pending'     ? 'selected' : '' }}>⏱ Bekleyen</option>
                    <option value="approved"    {{ request('status')==='approved'    ? 'selected' : '' }}>✓ Onaylanmış</option>
                    <option value="rejected"    {{ request('status')==='rejected'    ? 'selected' : '' }}>✗ Reddedilmiş</option>
                    <option value="withdrawing" {{ request('status')==='withdrawing' ? 'selected' : '' }}>↔ Ödeniyor</option>
                    <option value="paid"        {{ request('status')==='paid'        ? 'selected' : '' }}>✓ Ödendi</option>
                </select>
            </div>
            <div style="display:flex;gap:.5rem;align-items:flex-end;flex-shrink:0;">
                <button type="submit" class="btn-cp-filter">
                    <i class="bi bi-funnel-fill"></i> Uygula
                </button>
                @if(request()->hasAny(['affiliate_id','domain_id','status']))
                    <a href="{{ route('admin.affiliate.commissions') }}" class="btn-cp-filter" style="background:#f1f5f9;color:#64748b;">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Tabs ── --}}
    <div class="cp-tabs">
        <button class="cp-tab active" onclick="switchTab('detail', this)" id="tab-detail">
            <i class="bi bi-list-ul"></i> Detaylı Liste
        </button>
        <button class="cp-tab" onclick="switchTab('report', this)" id="tab-report">
            <i class="bi bi-file-earmark-spreadsheet"></i> Ödeme Raporu
            <span style="background:rgba(255,255,255,.25);color:inherit;font-size:.65rem;padding:.1rem .4rem;border-radius:10px;margin-left:.2rem;">{{ $summaryQuery->count() }} kişi</span>
        </button>
    </div>

    {{-- ══════════════════════════════════
         TAB 1: DETAIL
    ══════════════════════════════════ --}}
    <div class="cp-panel active" id="panel-detail">
        <div class="cp-card">
            <div class="cp-table-wrap">
                <table class="cp-table">
                    <thead>
                        <tr>
                            <th>Affiliate</th>
                            <th>Sipariş / Site</th>
                            <th>Brüt</th>
                            <th>Stopaj / KDV</th>
                            <th class="text-success">Net Hakediş</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th style="text-align:right;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $comm)
                            <tr>
                                {{-- Affiliate --}}
                                <td>
                                    <div class="aff-cell-name">{{ $comm->affiliate?->name ?? 'Silinmiş Üye' }}</div>
                                    <div class="aff-cell-code">{{ $comm->affiliate?->affiliate_code }}</div>
                                </td>

                                {{-- Order --}}
                                <td>
                                    <div class="order-cell">
                                        <i class="bi bi-receipt" style="color:#94a3b8;font-size:.72rem;"></i>
                                        #{{ $comm->order?->id ?? '—' }}
                                        <small class="d-block">{{ $comm->domain?->domain_name }}</small>
                                        <small style="color:#94a3b8;">{{ number_format($comm->order_total,2,',','.') }} TL sipariş</small>
                                    </div>
                                </td>

                                {{-- Brüt --}}
                                <td><span class="amount-gross">{{ number_format($comm->gross_commission,2,',','.') }} TL</span></td>

                                {{-- Vergi --}}
                                <td>
                                    @if($comm->tax_type === 'company')
                                        <span class="amount-tax add">+{{ number_format($comm->vat_amount,2,',','.') }} TL</span>
                                        <small class="d-block" style="color:#94a3b8;font-size:.68rem;">KDV</small>
                                    @elseif($comm->tax_type === 'individual')
                                        <span class="amount-tax deduct">-{{ number_format($comm->withholding_amount,2,',','.') }} TL</span>
                                        <small class="d-block" style="color:#94a3b8;font-size:.68rem;">Stopaj</small>
                                    @else
                                        <span class="amount-zero">—</span>
                                    @endif
                                </td>

                                {{-- Net --}}
                                <td><span class="amount-net">{{ number_format($comm->net_amount,2,',','.') }} TL</span></td>

                                {{-- Status --}}
                                <td>
                                    @php $stMap = [
                                        'pending'     => ['st-pending',    '⏱ Bekliyor'],
                                        'approved'    => ['st-approved',   '✓ Onaylandı'],
                                        'rejected'    => ['st-rejected',   '✗ Reddedildi'],
                                        'withdrawing' => ['st-withdrawing','↔ Ödeniyor'],
                                        'paid'        => ['st-paid',       '✓ Ödendi'],
                                    ]; [$stCls,$stLbl] = $stMap[$comm->status] ?? ['st-pending','?']; @endphp
                                    <span class="st-badge {{ $stCls }}">{{ $stLbl }}</span>
                                </td>

                                {{-- Date --}}
                                <td style="font-size:.75rem;color:#64748b;white-space:nowrap;">
                                    {{ $comm->created_at->format('d.m.Y') }}<br>
                                    <span style="color:#94a3b8;">{{ $comm->created_at->format('H:i') }}</span>
                                </td>

                                {{-- Manage --}}
                                <td style="text-align:right;">
                                    @if(!in_array($comm->status, ['withdrawing','paid']))
                                        <div class="manage-grp" style="justify-content:flex-end;">
                                            <form method="POST" action="{{ route('admin.affiliate.commissions.status', $comm) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="manage-btn mb-approve" title="Onayla">
                                                    <i class="bi bi-check-circle-fill"></i> Onayla
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.affiliate.commissions.status', $comm) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="manage-btn mb-reject" title="Reddet">
                                                    <i class="bi bi-x-circle-fill"></i> Reddet
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.affiliate.commissions.status', $comm) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="manage-btn mb-pending" title="Beklet">
                                                    <i class="bi bi-clock-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span style="font-size:.7rem;color:#94a3b8;">İşlem yok</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8">
                                <div class="cp-empty">
                                    <i class="bi bi-wallet2"></i>
                                    <p>Komisyon kaydı bulunamadı.</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($commissions->hasPages())
                <div class="cp-pager">{{ $commissions->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>{{-- /panel-detail --}}


    {{-- ══════════════════════════════════
         TAB 2: PAYMENT REPORT
    ══════════════════════════════════ --}}
    <div class="cp-panel" id="panel-report">
        <div class="cp-card">
            <div class="rp-header">
                <div>
                    <div class="rp-title">Bankaya Ödeme Talimatı Raporu</div>
                    <div class="rp-sub">Affiliate başına gruplu hakediş özeti — Ad Soyad · Vergi · IBAN · Brüt · Kesinti · Net</div>
                </div>
                <a href="{{ route('admin.affiliate.commissions.export', request()->query()) }}"
                   class="btn-export">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    Excel / CSV İndir
                </a>
            </div>

            <div class="cp-table-wrap">
                <table class="cp-table rp-table">
                    <thead>
                        <tr>
                            <th class="col-name">Ad Soyad</th>
                            <th>Vergi Türü</th>
                            <th>T.C. / Vergi No</th>
                            <th class="col-iban">IBAN</th>
                            <th>Adres</th>
                            <th style="text-align:right;">İşlem</th>
                            <th style="text-align:right;">Brüt Hakediş</th>
                            <th style="text-align:right;">Stopaj / KDV</th>
                            <th style="text-align:right;color:#059669;">Net Ödenecek</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryQuery as $row)
                            @php
                                $aff = $row->affiliate;
                                $taxLabel = match($row->tax_type) {
                                    'company'    => 'Kurumsal',
                                    'individual' => 'Bireysel',
                                    default      => 'Muaf',
                                };
                                $taxCls = match($row->tax_type) {
                                    'company'    => 'rp-company',
                                    'individual' => 'rp-individual',
                                    default      => 'rp-none',
                                };
                                $deductSign = $row->tax_type === 'company' ? '+' : '-';
                                $deductAmt  = $row->tax_type === 'company'
                                    ? $row->total_vat
                                    : $row->total_withholding;
                                $deductClass = $row->tax_type === 'company' ? 'add' : 'deduct';
                            @endphp
                            <tr>
                                <td>
                                    <div class="rp-name">{{ $aff?->name ?? '—' }}</div>
                                    <div class="rp-code">{{ $aff?->affiliate_code }}</div>
                                </td>
                                <td><span class="rp-tax-badge {{ $taxCls }}">{{ $taxLabel }}</span></td>
                                <td style="font-family:monospace;font-size:.76rem;color:#334155;">
                                    {{ $aff?->tax_number ?? '<span style="color:#94a3b8;">—</span>' }}
                                </td>
                                <td class="col-iban">
                                    @if($aff?->iban)
                                        <span style="font-size:.75rem;color:#0f172a;">{{ $aff->iban }}</span>
                                    @else
                                        <span style="color:#e11d48;font-size:.75rem;font-weight:700;">
                                            <i class="bi bi-exclamation-triangle-fill"></i> IBAN Yok
                                        </span>
                                    @endif
                                </td>
                                <td style="font-size:.73rem;color:#475569;max-width:180px;white-space:normal;line-height:1.2;">
                                    {{ $aff?->address ?? '—' }}
                                </td>
                                <td style="text-align:right;font-size:.78rem;color:#64748b;">
                                    {{ $row->total_orders }} işlem
                                </td>
                                <td style="text-align:right;">
                                    <span class="amount-gross">{{ number_format($row->total_gross,2,',','.') }} TL</span>
                                </td>
                                <td style="text-align:right;">
                                    <span class="amount-tax {{ $deductClass }}">
                                        {{ $deductSign }}{{ number_format($deductAmt,2,',','.') }} TL
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <span class="amount-net">{{ number_format($row->total_net,2,',','.') }} TL</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9">
                                <div class="cp-empty">
                                    <i class="bi bi-file-earmark-x"></i>
                                    <p>Rapor verisi bulunamadı.</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                    @if($summaryQuery->count() > 1)
                        @php
                            $grandGross  = $summaryQuery->sum('total_gross');
                            $grandDeduct = $summaryQuery->sum(fn($r) => $r->tax_type === 'company' ? $r->total_vat : $r->total_withholding);
                            $grandNet    = $summaryQuery->sum('total_net');
                        @endphp
                        <tfoot>
                            <tr class="totals-row">
                                <td colspan="5" style="font-size:.82rem;">TOPLAM</td>
                                <td style="text-align:right;font-size:.78rem;">{{ $summaryQuery->sum('total_orders') }} işlem</td>
                                <td style="text-align:right;"><span class="amount-gross">{{ number_format($grandGross,2,',','.') }} TL</span></td>
                                <td style="text-align:right;"><span class="amount-tax">{{ number_format($grandDeduct,2,',','.') }} TL</span></td>
                                <td style="text-align:right;" class="net-total"><span class="amount-net">{{ number_format($grandNet,2,',','.') }} TL</span></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>{{-- /panel-report --}}

</div>{{-- /cp-page --}}

<script>
function switchTab(name, btn) {
    document.querySelectorAll('.cp-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.cp-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
    btn.classList.add('active');
}
</script>
@endsection
