@extends('admin.layout')

@section('title', 'Affiliate İstatistikleri - Teksat Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-0 text-gray-800 fw-bold">Affiliate Genel İstatistikleri</h2>
        <p class="text-muted mb-0">Affiliate programının genel performansı, üye aktiviteleri ve finansal özetler.</p>
    </div>
</div>

<!-- Ana Metrik Kartları -->
<div class="row g-3 mb-4">
    <!-- Tıklama Kartı -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <span class="text-secondary fw-semibold small d-block mb-1">Toplam Tıklama</span>
                    <h3 class="fw-bold mb-0 text-dark font-monospace">{{ number_format($totalClicks, 0, ',', '.') }}</h3>
                </div>
                <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-cursor-fill fs-3"></i>
                </div>
            </div>
            <div class="text-muted small">
                <i class="bi bi-info-circle me-1"></i>Affiliate bağlantılarına yapılan tekil/çoğul tıklamalar.
            </div>
        </div>
    </div>

    <!-- Dönüşüm Kartı -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <span class="text-secondary fw-semibold small d-block mb-1">Dönüşüm Oranı</span>
                    <h3 class="fw-bold mb-0 text-success font-monospace">%{{ number_format($conversionRate, 2, ',', '.') }}</h3>
                </div>
                <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success">
                    <i class="bi bi-graph-up-arrow fs-3"></i>
                </div>
            </div>
            <div class="text-muted small">
                <span class="fw-bold text-dark font-monospace">{{ $totalAttributions }}</span> sipariş ilişkilendirildi.
            </div>
        </div>
    </div>

    <!-- Toplam Ciro Kartı -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <span class="text-secondary fw-semibold small d-block mb-1">Toplam Ciro (Satış)</span>
                    <h3 class="fw-bold mb-0 text-dark font-monospace">{{ number_format($totalSalesAmount, 2, ',', '.') }} TL</h3>
                </div>
                <div class="rounded-3 p-2 bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-currency-exchange fs-3"></i>
                </div>
            </div>
            <div class="text-muted small">
                <i class="bi bi-check-circle-fill text-success me-1"></i>Onaylı & Ödenmiş sipariş ciroları.
            </div>
        </div>
    </div>

    <!-- Toplam Hakediş Kartı -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <span class="text-secondary fw-semibold small d-block mb-1">Toplam Dağıtılan Komisyon</span>
                    <h3 class="fw-bold mb-0 text-primary font-monospace">{{ number_format($totalCommissions, 2, ',', '.') }} TL</h3>
                </div>
                <div class="rounded-3 p-2 bg-info bg-opacity-10 text-info">
                    <i class="bi bi-wallet2 fs-3"></i>
                </div>
            </div>
            <div class="text-muted small">
                Ödenen: <span class="fw-semibold text-dark font-monospace">{{ number_format($paidCommissions, 2, ',', '.') }} TL</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Finansal Durum & Dağılım -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header border-bottom bg-transparent py-3 px-4">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Komisyon Dağılımı</h5>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small fw-medium">Ödenmiş Komisyonlar</span>
                        <span class="font-monospace fw-bold text-dark">{{ number_format($paidCommissions, 2, ',', '.') }} TL</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 10px;">
                        @php
                            $paidPercent = $totalCommissions > 0 ? ($paidCommissions / $totalCommissions) * 100 : 0;
                            $approvedPercent = $totalCommissions > 0 ? ($approvedCommissions / $totalCommissions) * 100 : 0;
                            $pendingPercent = $totalCommissions > 0 ? ($pendingCommissions / $totalCommissions) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: {{ $paidPercent }}%" aria-valuenow="{{ $paidPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small fw-medium">Onaylanmış (Ödeme Bekleyen)</span>
                        <span class="font-monospace fw-bold text-dark">{{ number_format($approvedCommissions, 2, ',', '.') }} TL</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 10px;">
                        <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: {{ $approvedPercent }}%" aria-valuenow="{{ $approvedPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small fw-medium">Bekleyen (İncelemedeki)</span>
                        <span class="font-monospace fw-bold text-dark">{{ number_format($pendingCommissions, 2, ',', '.') }} TL</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 10px;">
                        <div class="progress-bar bg-warning rounded-pill" role="progressbar" style="width: {{ $pendingPercent }}%" aria-valuenow="{{ $pendingPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="border-top pt-3 mt-2 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-secondary">Genel Toplam:</span>
                    <span class="fs-4 fw-bold text-primary font-monospace">{{ number_format($totalCommissions, 2, ',', '.') }} TL</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Üye İstatistikleri -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header border-bottom bg-transparent py-3 px-4">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Affiliate Üye İstatistikleri</h5>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div class="row g-3 text-center my-auto">
                    <div class="col-6">
                        <div class="bg-light rounded-4 p-3 border-0">
                            <span class="d-block text-secondary small mb-1">Toplam Üye</span>
                            <span class="h3 fw-bold text-dark font-monospace">{{ $userStats['total'] }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-success bg-opacity-10 rounded-4 p-3 border-0">
                            <span class="d-block text-success small mb-1">Aktif Üye</span>
                            <span class="h3 fw-bold text-success font-monospace">{{ $userStats['active'] }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-warning bg-opacity-10 rounded-4 p-3 border-0">
                            <span class="d-block text-warning small mb-1">Onay Bekleyen</span>
                            <span class="h3 fw-bold text-warning font-monospace">{{ $userStats['pending'] }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-danger bg-opacity-10 rounded-4 p-3 border-0">
                            <span class="d-block text-danger small mb-1">Askıya Alınan</span>
                            <span class="h3 fw-bold text-danger font-monospace">{{ $userStats['suspended'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <a href="{{ route('admin.affiliate.users') }}" class="btn btn-outline-primary btn-sm rounded-3 py-2 px-4 w-100 fw-semibold">
                        <i class="bi bi-people me-1"></i> Üyeleri Yönet & İncele
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- En Çok Kazanan Pazarlamacılar -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header border-bottom bg-transparent py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-trophy-fill me-2 text-warning"></i>En Çok Kazanan Affiliate Üyeleri</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase font-monospace small">
                            <tr>
                                <th class="border-0 px-4 py-3">Üye Adı</th>
                                <th class="border-0 py-3 text-center">Satış (Adet)</th>
                                <th class="border-0 py-3 text-end">Ciro</th>
                                <th class="border-0 px-4 py-3 text-end text-primary">Kazanç</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topAffiliates as $index => $aff)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-secondary bg-opacity-25 text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.8rem;">
                                                {{ $index + 1 }}
                                            </span>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $aff->name }}</div>
                                                <div class="small font-monospace text-muted" style="font-size: 0.75rem;">{{ $aff->affiliate_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center fw-medium text-dark font-monospace">
                                        {{ $aff->total_sales }}
                                    </td>
                                    <td class="py-3 text-end font-monospace text-muted small">
                                        {{ number_format($aff->total_sales_amount, 2, ',', '.') }} TL
                                    </td>
                                    <td class="px-4 py-3 text-end fw-bold text-primary font-monospace">
                                        {{ number_format($aff->total_earnings, 2, ',', '.') }} TL
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-trophy mb-2 display-6 text-secondary d-block"></i>
                                        <span>Aktif kazancı olan üye bulunmuyor.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Satış Sitelerine Göre Performans -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header border-bottom bg-transparent py-3 px-4">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-globe me-2 text-primary"></i>Sitelere Göre Performans</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase font-monospace small">
                            <tr>
                                <th class="border-0 px-4 py-3">Satış Sitesi (Domain)</th>
                                <th class="border-0 py-3 text-center">Sipariş Sayısı</th>
                                <th class="border-0 py-3 text-end">Toplam Ciro</th>
                                <th class="border-0 px-4 py-3 text-end">Komisyon</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($domainPerformance as $dom)
                                <tr>
                                    <td class="px-4 py-3 fw-semibold text-dark">
                                        <i class="bi bi-globe me-2 text-secondary"></i>{{ $dom->domain_name }}
                                    </td>
                                    <td class="py-3 text-center font-monospace fw-semibold text-dark">
                                        {{ $dom->total_sales }}
                                    </td>
                                    <td class="py-3 text-end font-monospace text-muted small">
                                        {{ number_format($dom->total_sales_amount, 2, ',', '.') }} TL
                                    </td>
                                    <td class="px-4 py-3 text-end fw-bold text-primary font-monospace">
                                        {{ number_format($dom->total_commissions, 2, ',', '.') }} TL
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-globe-americas mb-2 display-6 text-secondary d-block"></i>
                                        <span>Henüz satış ortaklığı yapılmış site yok.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Son Tıklamalar -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header border-bottom bg-transparent py-3 px-4">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-cursor-fill me-2 text-primary"></i>Son Affiliate Tıklamaları</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light text-uppercase font-monospace small">
                            <tr>
                                <th class="border-0 px-4 py-2.5">Tarih</th>
                                <th class="border-0 py-2.5">Üye</th>
                                <th class="border-0 py-2.5">Site</th>
                                <th class="border-0 px-4 py-2.5">Kanal / IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentClicks as $click)
                                <tr>
                                    <td class="px-4 py-2.5 text-muted">
                                        {{ \Carbon\Carbon::parse($click->created_at)->format('d.m.Y H:i') }}
                                    </td>
                                    <td class="py-2.5 fw-semibold text-dark">
                                        {{ $click->affiliate_name }}
                                    </td>
                                    <td class="py-2.5 font-monospace text-muted">
                                        {{ $click->domain_name }}
                                    </td>
                                    <td class="px-4 py-2.5 text-secondary font-monospace">
                                        <span class="badge bg-light text-dark border me-1">{{ $click->channel ?: 'Doğrudan' }}</span>
                                        <span class="small">{{ $click->ip_address }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Henüz tıklama kaydı bulunmuyor.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Son Hak Edişler -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header border-bottom bg-transparent py-3 px-4">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-wallet2 me-2 text-primary"></i>Son Kazanılan Komisyonlar</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light text-uppercase font-monospace small">
                            <tr>
                                <th class="border-0 px-4 py-2.5">Tarih</th>
                                <th class="border-0 py-2.5">Üye</th>
                                <th class="border-0 py-2.5 text-end">Hakediş</th>
                                <th class="border-0 px-4 py-2.5 text-center">Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCommissions as $comm)
                                <tr>
                                    <td class="px-4 py-2.5 text-muted">
                                        {{ \Carbon\Carbon::parse($comm->created_at)->format('d.m.Y H:i') }}
                                    </td>
                                    <td class="py-2.5 fw-semibold text-dark">
                                        {{ $comm->affiliate_name }}
                                        <div class="font-monospace text-muted" style="font-size: 0.7rem;">{{ $comm->domain_name }}</div>
                                    </td>
                                    <td class="py-2.5 text-end fw-bold text-success font-monospace">
                                        {{ number_format($comm->net_amount, 2, ',', '.') }} TL
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if($comm->status === 'pending')
                                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i>Bekliyor</span>
                                        @elseif($comm->status === 'approved')
                                            <span class="badge bg-success px-2 py-1 rounded-pill" style="font-size: 0.7rem;"><i class="bi bi-check-circle me-1"></i>Onaylandı</span>
                                        @elseif($comm->status === 'rejected')
                                            <span class="badge bg-danger px-2 py-1 rounded-pill" style="font-size: 0.7rem;"><i class="bi bi-x-circle me-1"></i>Reddedildi</span>
                                        @elseif($comm->status === 'withdrawing')
                                            <span class="badge bg-info text-dark px-2 py-1 rounded-pill" style="font-size: 0.7rem;"><i class="bi bi-arrow-right-left me-1"></i>Ödeniyor</span>
                                        @elseif($comm->status === 'paid')
                                            <span class="badge bg-secondary px-2 py-1 rounded-pill" style="font-size: 0.7rem;"><i class="bi bi-wallet2 me-1"></i>Ödendi</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Henüz komisyon kaydı bulunmuyor.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
