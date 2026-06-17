@extends('admin.layout')

@section('title', 'Affiliate Üyeleri - Admin Panel')

@section('extra_css')
<style>
/* ════════════════════════════════
   LAYOUT
════════════════════════════════ */
.au-page { display:flex; flex-direction:column; gap:1.5rem; }

/* ════════════════════════════════
   HEADER
════════════════════════════════ */
.au-header { display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:.75rem; }
.au-header h1 { font-size:1.45rem; font-weight:800; color:#0f172a; margin:0 0 .2rem; letter-spacing:-.5px; }
.au-header p  { font-size:.82rem; color:#64748b; margin:0; }
.au-total-pill { background:#f1f5f9; color:#475569; font-size:.73rem; font-weight:700; padding:.3rem .8rem; border-radius:20px; border:1px solid #e2e8f0; white-space:nowrap; }

/* ════════════════════════════════
   FLASH
════════════════════════════════ */
.au-flash { border-radius:12px; padding:.8rem 1.1rem; font-size:.875rem; font-weight:500; display:flex; align-items:center; gap:.6rem; }
.au-flash-success { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.au-flash-error   { background:#fff1f2; color:#be123c; border:1px solid #fecdd3; }

/* ════════════════════════════════
   STATS BAR
════════════════════════════════ */
.au-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:.85rem; }
@media(max-width:768px){ .au-stats{ grid-template-columns:repeat(2,1fr); } }
.au-stat {
    background:#fff; border:1px solid #f1f5f9; border-radius:14px;
    padding:1rem 1.1rem; display:flex; align-items:center; gap:.85rem;
    box-shadow:0 1px 3px rgba(15,23,42,.05); transition:box-shadow .2s;
}
.au-stat:hover { box-shadow:0 4px 14px rgba(15,23,42,.1); }
.au-stat-icon { width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.05rem; flex-shrink:0; }
.si-blue   { background:#eff6ff; color:#2563eb; }
.si-green  { background:#f0fdf4; color:#16a34a; }
.si-yellow { background:#fefce8; color:#d97706; }
.si-red    { background:#fff1f2; color:#e11d48; }
.au-stat-num { font-size:1.5rem; font-weight:800; color:#0f172a; line-height:1; }
.au-stat-lbl { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#94a3b8; margin-top:3px; }

/* ════════════════════════════════
   FILTER
════════════════════════════════ */
.au-filter { background:#fff; border:1px solid #f1f5f9; border-radius:16px; padding:1rem 1.25rem; box-shadow:0 1px 3px rgba(15,23,42,.05); }
.au-filter form { display:flex; align-items:flex-end; gap:.75rem; flex-wrap:wrap; }
.au-filter-field { display:flex; flex-direction:column; gap:.3rem; flex:1; min-width:170px; }
.au-filter-field label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#64748b; }
.au-filter-field .form-control,
.au-filter-field .form-select {
    border-radius:10px; border:1px solid #e2e8f0; font-size:.85rem;
    padding:.48rem .85rem; box-shadow:none; background:#fafafa; color:#334155;
    transition:border-color .2s, box-shadow .2s; height:38px;
}
.au-filter-field .form-control:focus,
.au-filter-field .form-select:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); background:#fff; outline:none; }
.btn-au-filter {
    background:#6366f1; color:#fff; border:none; border-radius:10px;
    padding:.48rem 1.2rem; font-size:.82rem; font-weight:700;
    display:inline-flex; align-items:center; gap:.4rem; cursor:pointer;
    transition:background .2s,transform .1s; white-space:nowrap;
    text-decoration:none; height:38px;
}
.btn-au-filter:hover { background:#4f46e5; color:#fff; transform:translateY(-1px); }
.btn-au-clear {
    background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0;
    border-radius:10px; padding:.48rem .9rem; font-size:.82rem; font-weight:600;
    display:inline-flex; align-items:center; gap:.35rem; cursor:pointer;
    transition:background .2s; white-space:nowrap; text-decoration:none; height:38px;
}
.btn-au-clear:hover { background:#e2e8f0; color:#334155; }

/* ════════════════════════════════
   CARD GRID
════════════════════════════════ */
.au-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));
    gap:1rem;
}
@media(max-width:640px){ .au-grid{ grid-template-columns:1fr; } }

/* ════════════════════════════════
   USER CARD
════════════════════════════════ */
.au-card {
    background:#fff;
    border:1px solid #eef2f7;
    border-radius:18px;
    box-shadow:0 2px 8px rgba(15,23,42,.06);
    display:flex;
    flex-direction:column;
    overflow:hidden;
    transition:box-shadow .25s, transform .25s;
}
.au-card:hover { box-shadow:0 12px 36px rgba(15,23,42,.13); transform:translateY(-3px); }

/* Colour strip */
.au-strip { height:4px; }
.strip-green  { background:linear-gradient(90deg,#22c55e,#86efac); }
.strip-yellow { background:linear-gradient(90deg,#f59e0b,#fde68a); }
.strip-red    { background:linear-gradient(90deg,#f43f5e,#fda4af); }

/* ── Card Head (avatar + name + status) ── */
.au-card-head {
    display:flex;
    align-items:center;
    gap:.9rem;
    padding:1rem 1.15rem .8rem;
}
.au-avatar {
    width:48px; height:48px; border-radius:13px;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    color:#fff; display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:1.2rem; flex-shrink:0;
}
.au-user-meta { flex:1; min-width:0; }
.au-name { font-size:.95rem; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.2; }
.au-email { font-size:.74rem; color:#64748b; margin-top:1px; word-break:break-all; }
.au-status-pill {
    flex-shrink:0;
    font-size:.68rem; font-weight:800;
    padding:.26rem .65rem; border-radius:20px;
    letter-spacing:.3px; white-space:nowrap;
    align-self:flex-start;
}
.sp-active    { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
.sp-pending   { background:#fef9c3; color:#a16207; border:1px solid #fde68a; }
.sp-suspended { background:#ffe4e6; color:#be123c; border:1px solid #fecdd3; }

/* ── Info Chips Grid (2-column) ── */
.au-chips {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:.5rem;
    padding:.1rem 1.15rem .9rem;
}
.au-chip {
    display:flex;
    align-items:center;
    gap:.45rem;
    background:#f8fafc;
    border:1px solid #f1f5f9;
    border-radius:10px;
    padding:.45rem .65rem;
    min-width:0;
}
.au-chip.full-width { grid-column:span 2; }
.chip-icon {
    width:26px; height:26px; border-radius:7px;
    display:flex; align-items:center; justify-content:center;
    font-size:.78rem; flex-shrink:0;
}
.ci-purple { background:#f5f3ff; color:#7c3aed; }
.ci-blue   { background:#eff6ff; color:#2563eb; }
.ci-green  { background:#f0fdf4; color:#16a34a; }
.ci-orange { background:#fff7ed; color:#c2410c; }
.ci-slate  { background:#f1f5f9; color:#475569; }
.ci-red    { background:#fff1f2; color:#e11d48; }
.chip-content { min-width:0; flex:1; }
.chip-label  { font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#94a3b8; line-height:1; }
.chip-value  { font-size:.78rem; font-weight:600; color:#1e293b; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.chip-value.mono { font-family:'SFMono-Regular',Consolas,monospace; font-size:.72rem; letter-spacing:.3px; }
.chip-value.warn { color:#e11d48; }

/* Tax badge inside chip */
.chip-tax {
    font-size:.68rem; font-weight:700; padding:.15rem .45rem; border-radius:20px;
}
.tax-company    { background:#eff6ff; color:#1d4ed8; }
.tax-individual { background:#fef3c7; color:#92400e; }
.tax-none       { background:#f1f5f9; color:#64748b; }

/* Phone inside head (small, below email) */
.au-phone { font-size:.7rem; color:#94a3b8; margin-top:1px; }

/* ── Footer ── */
.au-card-foot {
    margin-top:auto;
    padding:.65rem 1.15rem;
    background:#fafbfc;
    border-top:1px solid #f1f5f9;
    display:flex;
    align-items:center;
    gap:.5rem;
}

/* Hesabına Gir button */
.btn-enter {
    background:#f0fdf4; color:#15803d;
    border:1px solid #bbf7d0; border-radius:9px;
    padding:.38rem .8rem; font-size:.74rem; font-weight:700;
    display:inline-flex; align-items:center; gap:.35rem;
    cursor:pointer; transition:background .15s; white-space:nowrap;
    flex-shrink:0; line-height:1;
}
.btn-enter:hover { background:#dcfce7; border-color:#86efac; }

/* Status inline group */
.au-status-grp {
    flex:1; display:flex;
    border-radius:9px; overflow:hidden;
    border:1px solid #e2e8f0; min-width:0;
}
.au-status-grp form { flex:1; display:flex; margin:0; padding:0; min-width:0; }
.au-status-grp form + form { border-left:1px solid #e2e8f0; }
.au-status-btn {
    flex:1; border:none; background:#f8fafc; color:#64748b;
    font-size:.68rem; font-weight:700;
    padding:.38rem .2rem; cursor:pointer;
    display:flex; align-items:center; justify-content:center; gap:.25rem;
    white-space:nowrap; transition:background .15s,color .15s; width:100%; line-height:1;
}
.au-status-btn:hover { background:#f1f5f9; }
.au-status-btn.s-active    { background:#dcfce7; color:#15803d; }
.au-status-btn.s-pending   { background:#fef9c3; color:#a16207; }
.au-status-btn.s-suspended { background:#ffe4e6; color:#be123c; }

/* ════════════════════════════════
   EMPTY
════════════════════════════════ */
.au-empty { background:#fff; border:1px solid #f1f5f9; border-radius:16px; padding:4rem 2rem; text-align:center; box-shadow:0 1px 3px rgba(15,23,42,.05); }
.au-empty .empty-icon { font-size:2.75rem; display:block; margin-bottom:.85rem; opacity:.4; }
.au-empty p { font-size:.9rem; color:#64748b; margin:0; }

/* ════════════════════════════════
   PAGINATION
════════════════════════════════ */
.au-pager { background:#fff; border:1px solid #f1f5f9; border-radius:14px; padding:.85rem 1.1rem; box-shadow:0 1px 3px rgba(15,23,42,.05); }
.au-pager .pagination { margin:0; }
</style>
@endsection

@section('content')
@php
    use App\Models\AffiliateUser;
    $allStats = [
        'total'     => AffiliateUser::count(),
        'active'    => AffiliateUser::where('status','active')->count(),
        'pending'   => AffiliateUser::where('status','pending')->count(),
        'suspended' => AffiliateUser::where('status','suspended')->count(),
    ];
@endphp

<div class="au-page">

    {{-- ── Header ── --}}
    <div class="au-header">
        <div>
            <h1>Affiliate Üye Yönetimi</h1>
            <p>Sisteme kayıtlı affiliate pazarlamacılar — üyelik onay ve durum yönetimi</p>
        </div>
        <span class="au-total-pill">
            <i class="bi bi-people-fill me-1"></i>
            Toplam {{ $allStats['total'] }} üye
        </span>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
        <div class="au-flash au-flash-success">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="au-flash au-flash-error">
            <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── Stats ── --}}
    <div class="au-stats">
        <div class="au-stat">
            <div class="au-stat-icon si-blue"><i class="bi bi-people-fill"></i></div>
            <div><div class="au-stat-num">{{ $allStats['total'] }}</div><div class="au-stat-lbl">Toplam</div></div>
        </div>
        <div class="au-stat">
            <div class="au-stat-icon si-green"><i class="bi bi-check-circle-fill"></i></div>
            <div><div class="au-stat-num">{{ $allStats['active'] }}</div><div class="au-stat-lbl">Aktif</div></div>
        </div>
        <div class="au-stat">
            <div class="au-stat-icon si-yellow"><i class="bi bi-clock-fill"></i></div>
            <div><div class="au-stat-num">{{ $allStats['pending'] }}</div><div class="au-stat-lbl">Bekleyen</div></div>
        </div>
        <div class="au-stat">
            <div class="au-stat-icon si-red"><i class="bi bi-slash-circle-fill"></i></div>
            <div><div class="au-stat-num">{{ $allStats['suspended'] }}</div><div class="au-stat-lbl">Askıda</div></div>
        </div>
    </div>

    {{-- ── Filter ── --}}
    <div class="au-filter">
        <form method="GET" action="{{ route('admin.affiliate.users') }}">
            <div class="au-filter-field" style="max-width:340px;">
                <label for="au-search">Arama</label>
                <div class="input-group" style="height:38px;">
                    <span class="input-group-text bg-white border-end-0"
                          style="border:1px solid #e2e8f0;border-right:none;border-radius:10px 0 0 10px;padding:0 .7rem;">
                        <i class="bi bi-search" style="color:#94a3b8;font-size:.8rem;"></i>
                    </span>
                    <input type="text" id="au-search" name="search"
                           class="form-control border-start-0 ps-1"
                           placeholder="İsim, e-posta veya affiliate kodu..."
                           value="{{ request('search') }}"
                           style="border-radius:0 10px 10px 0;border:1px solid #e2e8f0;border-left:none;height:38px;">
                </div>
            </div>
            <div class="au-filter-field" style="max-width:200px;">
                <label for="au-status">Durum</label>
                <select class="form-select" id="au-status" name="status">
                    <option value="">Tümü</option>
                    <option value="active"    {{ request('status')==='active'    ? 'selected' : '' }}>✓ Aktif</option>
                    <option value="pending"   {{ request('status')==='pending'   ? 'selected' : '' }}>⏱ Bekleyenler</option>
                    <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>⊘ Askıya Alınmış</option>
                </select>
            </div>
            <div style="display:flex;gap:.5rem;align-items:flex-end;flex-shrink:0;">
                <button type="submit" class="btn-au-filter">
                    <i class="bi bi-funnel-fill"></i> Filtrele
                </button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('admin.affiliate.users') }}" class="btn-au-clear">
                        <i class="bi bi-x-lg"></i> Temizle
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Cards ── --}}
    @if($users->isEmpty())
        <div class="au-empty">
            <i class="bi bi-people empty-icon"></i>
            <p>Arama kriterlerine uygun affiliate üye bulunamadı.</p>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.affiliate.users') }}" class="btn-au-filter d-inline-flex mt-3">
                    <i class="bi bi-arrow-counterclockwise"></i> Filtreyi Temizle
                </a>
            @endif
        </div>
    @else
        <div class="au-grid">
            @foreach($users as $user)
                @php
                    $strip = match($user->status) {
                        'active'    => 'strip-green',
                        'suspended' => 'strip-red',
                        default     => 'strip-yellow',
                    };
                    $spCls = match($user->status) {
                        'active'    => 'sp-active',
                        'suspended' => 'sp-suspended',
                        default     => 'sp-pending',
                    };
                    $spLabel = match($user->status) {
                        'active'    => '● Aktif',
                        'suspended' => '⊘ Askıda',
                        default     => '⏱ Bekliyor',
                    };
                    $taxLabel = match($user->tax_type) {
                        'company'    => 'Kurumsal',
                        'individual' => 'Bireysel',
                        default      => 'Muaf',
                    };
                    $taxCls = match($user->tax_type) {
                        'company'    => 'tax-company',
                        'individual' => 'tax-individual',
                        default      => 'tax-none',
                    };
                @endphp

                <div class="au-card">
                    {{-- colour strip --}}
                    <div class="au-strip {{ $strip }}"></div>

                    {{-- ── Head: avatar / name / status ── --}}
                    <div class="au-card-head">
                        <div class="au-avatar">{{ mb_strtoupper(mb_substr($user->name,0,1)) }}</div>
                        <div class="au-user-meta">
                            <div class="au-name">{{ $user->name }}</div>
                            <div class="au-email">{{ $user->email }}</div>
                            @if($user->phone)
                                <div class="au-phone"><i class="bi bi-telephone" style="font-size:.62rem;"></i> {{ $user->phone }}</div>
                            @endif
                        </div>
                        <span class="au-status-pill {{ $spCls }}">{{ $spLabel }}</span>
                    </div>

                    {{-- ── Info Chips (2-column grid) ── --}}
                    <div class="au-chips">

                        {{-- Affiliate Code --}}
                        <div class="au-chip">
                            <div class="chip-icon ci-purple"><i class="bi bi-tag-fill"></i></div>
                            <div class="chip-content">
                                <div class="chip-label">Kod</div>
                                <div class="chip-value mono">{{ $user->affiliate_code }}</div>
                            </div>
                        </div>

                        {{-- Kayıt Tarihi --}}
                        <div class="au-chip">
                            <div class="chip-icon ci-slate"><i class="bi bi-calendar3"></i></div>
                            <div class="chip-content">
                                <div class="chip-label">Kayıt</div>
                                <div class="chip-value">{{ $user->created_at->format('d.m.Y') }}</div>
                            </div>
                        </div>

                        {{-- Vergi Tipi --}}
                        <div class="au-chip">
                            <div class="chip-icon ci-orange"><i class="bi bi-receipt-cutoff"></i></div>
                            <div class="chip-content">
                                <div class="chip-label">Vergi</div>
                                <div class="chip-value">
                                    <span class="chip-tax {{ $taxCls }}">{{ $taxLabel }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Şirket / TC --}}
                        @if($user->tax_type === 'company' && $user->company_name)
                            <div class="au-chip">
                                <div class="chip-icon ci-blue"><i class="bi bi-building"></i></div>
                                <div class="chip-content">
                                    <div class="chip-label">Şirket</div>
                                    <div class="chip-value" title="{{ $user->company_name }}">{{ $user->company_name }}</div>
                                </div>
                            </div>
                        @elseif(($user->tax_type === 'individual' || $user->tax_type === 'none') && $user->tax_number)
                            <div class="au-chip">
                                <div class="chip-icon ci-blue"><i class="bi bi-person-vcard"></i></div>
                                <div class="chip-content">
                                    <div class="chip-label">T.C.</div>
                                    <div class="chip-value mono">{{ $user->tax_number }}</div>
                                </div>
                            </div>
                        @else
                            <div class="au-chip">
                                <div class="chip-icon ci-blue"><i class="bi bi-info-circle"></i></div>
                                <div class="chip-content">
                                    <div class="chip-label">Detay</div>
                                    <div class="chip-value" style="color:#94a3b8;">—</div>
                                </div>
                            </div>
                        @endif

                        {{-- IBAN (full width) --}}
                        <div class="au-chip full-width">
                            <div class="chip-icon {{ $user->iban ? 'ci-green' : 'ci-red' }}">
                                <i class="bi {{ $user->iban ? 'bi-bank' : 'bi-exclamation-triangle-fill' }}"></i>
                            </div>
                            <div class="chip-content">
                                <div class="chip-label">IBAN</div>
                                @if($user->iban)
                                    <div class="chip-value mono">{{ $user->iban }}</div>
                                @else
                                    <div class="chip-value warn">IBAN Belirtilmemiş</div>
                                @endif
                            </div>
                        </div>

                        {{-- Adres (full width) --}}
                        @if($user->address)
                            <div class="au-chip full-width" style="min-height: auto;">
                                <div class="chip-icon ci-blue">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div class="chip-content" style="flex: 1; min-width: 0;">
                                    <div class="chip-label">Adres</div>
                                    <div class="chip-value" style="font-size: .74rem; white-space: normal; line-height: 1.35; overflow: visible; text-overflow: unset; color: #475569; font-weight: 500;">{{ $user->address }}</div>
                                </div>
                            </div>
                        @endif

                    </div>{{-- /au-chips --}}

                    {{-- ── Footer ── --}}
                    <div class="au-card-foot">

                        <form method="POST"
                              action="{{ route('admin.affiliate.users.impersonate', $user) }}"
                              target="_blank"
                              style="margin:0;padding:0;">
                            @csrf
                            <button type="submit" class="btn-enter" title="Bu affiliate hesabına giriş yap">
                                <i class="bi bi-box-arrow-in-right"></i>
                                Hesabına Gir
                            </button>
                        </form>

                        <div class="au-status-grp">
                            <form method="POST" action="{{ route('admin.affiliate.users.status', $user) }}">
                                @csrf
                                <input type="hidden" name="status" value="active">
                                <button type="submit"
                                        class="au-status-btn {{ $user->status==='active' ? 's-active' : '' }}"
                                        title="Aktif Et">
                                    <i class="bi bi-check-circle-fill"></i> Aktif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.affiliate.users.status', $user) }}">
                                @csrf
                                <input type="hidden" name="status" value="pending">
                                <button type="submit"
                                        class="au-status-btn {{ $user->status==='pending' ? 's-pending' : '' }}"
                                        title="Onay Beklet">
                                    <i class="bi bi-clock-fill"></i> Beklet
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.affiliate.users.status', $user) }}">
                                @csrf
                                <input type="hidden" name="status" value="suspended">
                                <button type="submit"
                                        class="au-status-btn {{ $user->status==='suspended' ? 's-suspended' : '' }}"
                                        title="Askıya Al">
                                    <i class="bi bi-slash-circle-fill"></i> Askı
                                </button>
                            </form>
                        </div>

                    </div>{{-- /au-card-foot --}}

                </div>{{-- /au-card --}}
            @endforeach
        </div>{{-- /au-grid --}}

        @if($users->hasPages())
            <div class="au-pager">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif

    @endif

</div>{{-- /au-page --}}
@endsection
