@extends('admin.layout')

@section('title', 'Ödeme Talepleri - ' . config('app.name') . ' Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-0 text-gray-800 fw-bold">Ödeme Talepleri</h2>
        <p class="text-muted mb-0">Affiliate üyelerinden gelen para çekme/fatura ödeme talepleri.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <h5 class="alert-heading fw-bold small mb-2">Hata Oluştu!</h5>
        <ul class="mb-0 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.affiliate.withdrawals') }}" class="row g-3">
            <div class="col-md-9">
                <label for="status" class="form-label text-secondary fw-semibold small">Talep Durumu</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Tümü</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Onay Bekleyenler (Pending)</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Ödenenler (Paid)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Reddedilenler (Rejected)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-filter"></i>
                    <span>Süzgeçten Geçir</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 1100px;">
                <thead class="table-light text-uppercase font-monospace small" style="background-color: #f8fafc;">
                    <tr>
                        <th class="border-0 px-4 py-3">Üye</th>
                        <th class="border-0 py-3">Banka IBAN</th>
                        <th class="border-0 py-3">Vergi Hesap Kesimi</th>
                        <th class="border-0 py-3 text-primary fw-bold">Ödeme Tutarı (Net)</th>
                        <th class="border-0 py-3 text-center">Durum</th>
                        <th class="border-0 py-3">Talep Tarihi</th>
                        <th class="border-0 py-3">Dekont / Not</th>
                        <th class="border-0 px-4 py-3 text-end">Yönetim</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($withdrawals as $with)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="fw-semibold text-dark">{{ $with->affiliate?->name ?: 'Silinmiş Üye' }}</div>
                                <div class="small font-monospace text-muted">{{ $with->affiliate?->affiliate_code }}</div>
                                <div class="small text-muted" style="font-size: 0.75rem;">{{ $with->affiliate?->email }}</div>
                            </td>
                            <td class="py-3 font-monospace small">
                                <div class="d-flex align-items-center gap-1 text-dark">
                                    <i class="bi bi-bank text-secondary"></i>
                                    <span>{{ $with->iban }}</span>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="small text-dark font-monospace">Brüt: {{ number_format($with->gross_amount, 2, ',', '.') }} TL</div>
                                @if($with->withholding_amount > 0)
                                    <div class="small text-warning font-monospace" style="font-size: 0.75rem;">Stopaj: -{{ number_format($with->withholding_amount, 2, ',', '.') }} TL</div>
                                @elseif($with->vat_amount > 0)
                                    <div class="small text-info font-monospace" style="font-size: 0.75rem;">KDV: +{{ number_format($with->vat_amount, 2, ',', '.') }} TL</div>
                                @else
                                    <div class="small text-muted font-monospace" style="font-size: 0.75rem;">Kesintisiz</div>
                                @endif
                            </td>
                            <td class="py-3 font-monospace fw-bold text-primary">
                                {{ number_format($with->net_payment, 2, ',', '.') }} TL
                            </td>
                            <td class="py-3 text-center">
                                @if($with->status === 'pending')
                                    <span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-pill"><i class="bi bi-clock me-1"></i>Onay Bekliyor</span>
                                @elseif($with->status === 'paid')
                                    <span class="badge bg-success px-2.5 py-1.5 rounded-pill"><i class="bi bi-check-circle me-1"></i>Ödendi</span>
                                @elseif($with->status === 'rejected')
                                    <span class="badge bg-danger px-2.5 py-1.5 rounded-pill"><i class="bi bi-x-circle me-1"></i>Reddedildi</span>
                                @endif
                            </td>
                            <td class="py-3 small text-muted">
                                <div>Talep: {{ $with->created_at->format('d.m.Y H:i') }}</div>
                                @if($with->paid_at)
                                    <div class="text-success fs-7" style="font-size: 0.7rem;"><i class="bi bi-check me-0.5"></i>Ödeme: {{ $with->paid_at->format('d.m.Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($with->payment_receipt)
                                    <a href="{{ asset('uploads/affiliate_receipts/' . $with->payment_receipt) }}" target="_blank" class="btn btn-outline-secondary btn-xs py-1 px-2 d-inline-flex align-items-center gap-1 small">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                        <span>Dekontu Aç</span>
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                                
                                @if($with->admin_note)
                                    <div class="small text-muted mt-1 text-truncate" style="max-width: 150px;" title="{{ $with->admin_note }}">
                                        <strong>Not:</strong> {{ $with->admin_note }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end">
                                @if($with->status === 'pending')
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <button type="button" class="btn btn-success btn-sm px-2.5 py-1.5 d-inline-flex align-items-center gap-1 fw-medium" data-bs-toggle="modal" data-bs-target="#payModal{{ $with->id }}">
                                            <i class="bi bi-cash"></i> Öde
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm px-2.5 py-1.5 d-inline-flex align-items-center gap-1 fw-medium" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $with->id }}">
                                            <i class="bi bi-x-lg"></i> Reddet
                                        </button>
                                    </div>

                                    <!-- Ödeme Modal -->
                                    <div class="modal fade" id="payModal{{ $with->id }}" tabindex="-1" aria-labelledby="payModalLabel{{ $with->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-bottom bg-light">
                                                    <h5 class="modal-title fw-bold" id="payModalLabel{{ $with->id }}">Ödeme Onayı</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.affiliate.withdrawals.status', $with) }}" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="status" value="paid">
                                                    <div class="modal-body text-start p-4">
                                                        <div class="alert alert-info border-0 shadow-xs mb-3">
                                                            <div class="fw-semibold">{{ $with->affiliate?->name }}</div>
                                                            <div class="small font-monospace mb-1">{{ $with->iban }}</div>
                                                            <div class="fw-bold mt-1">Ödenecek Net Tutar: {{ number_format($with->net_payment, 2, ',', '.') }} TL</div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="payment_receipt{{ $with->id }}" class="form-label text-secondary fw-semibold small">Banka Dekontu Yükle</label>
                                                            <input class="form-control" type="file" id="payment_receipt{{ $with->id }}" name="payment_receipt" accept=".jpg,.jpeg,.png,.pdf" required>
                                                            <div class="form-text small">JPEG, PNG veya PDF formatında banka dekontu (Maks. 5MB)</div>
                                                        </div>

                                                        <div class="mb-0">
                                                            <label for="admin_note_pay{{ $with->id }}" class="form-label text-secondary fw-semibold small">Yönetici Notu (Opsiyonel)</label>
                                                            <textarea class="form-control" id="admin_note_pay{{ $with->id }}" name="admin_note" rows="3" placeholder="Ödeme ile ilgili not yazabilirsiniz..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                        <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-1">
                                                            <i class="bi bi-wallet2"></i> Ödemeyi Tamamla
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reddetme Modal -->
                                    <div class="modal fade" id="rejectModal{{ $with->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $with->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-bottom bg-light">
                                                    <h5 class="modal-title fw-bold text-danger" id="rejectModalLabel{{ $with->id }}">Ödeme Talebi Reddedilsin mi?</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.affiliate.withdrawals.status', $with) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <div class="modal-body text-start p-4">
                                                        <p class="text-secondary small mb-3">Bu ödeme talebini reddettiğinizde, bu talebe konu olan tüm onaylı komisyonlar otomatik olarak geri yüklenecek ve üye tekrar çekme talebinde bulunabilecektir.</p>
                                                        <div class="mb-0">
                                                            <label for="admin_note_rej{{ $with->id }}" class="form-label text-secondary fw-semibold small">Red Gerekçesi (Zorunlu)</label>
                                                            <textarea class="form-control" id="admin_note_rej{{ $with->id }}" name="admin_note" rows="3" placeholder="Red gerekçesini açıklayan not yazın..." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                                        <button type="submit" class="btn btn-danger">Talebi Reddet</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">İşlem Yapıldı</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-cash-stack mb-2 display-6 d-block text-secondary"></i>
                                <span>Kriterlere uyan herhangi bir çekim talebi bulunamadı.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
            <div class="px-4 py-3 border-top bg-light">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
