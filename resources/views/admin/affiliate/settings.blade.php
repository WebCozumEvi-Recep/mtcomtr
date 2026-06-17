@extends('admin.layout')

@section('title', 'Affiliate Ayarları - Teksat Admin')

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <!-- Success Alert -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Error Alerts -->
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

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <h5 class="card-title fw-bold text-dark mb-0">
                    <i class="bi bi-gear-fill me-2 text-primary"></i>
                    Affiliate Vergilendirme & Oran Ayarları
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.affiliate.settings.store') }}">
                    @csrf
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label for="affiliate_withholding_rate" class="form-label text-secondary fw-semibold small">Bireysel Stopaj Oranı (%)</label>
                            <input type="number" step="0.01" class="form-control font-monospace" id="affiliate_withholding_rate" name="affiliate_withholding_rate" value="{{ $settings['affiliate_withholding_rate'] ?? '20' }}" required min="0" max="100">
                            <div class="form-text small mt-1">Bireysel vergi mükellefi olmayan üyelerin brüt hak edişlerinden düşülecek stopaj oranı.</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="affiliate_vat_rate" class="form-label text-secondary fw-semibold small">Kurumsal KDV Oranı (%)</label>
                            <input type="number" step="0.01" class="form-control font-monospace" id="affiliate_vat_rate" name="affiliate_vat_rate" value="{{ $settings['affiliate_vat_rate'] ?? '20' }}" required min="0" max="100">
                            <div class="form-text small mt-1">Kurumsal üyelerin ödeme taleplerinde net hak ediş tutarlarına eklenecek KDV oranı.</div>
                        </div>
                    </div>

                    <div class="border-top pt-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            Oranlar değiştirildiğinde, ödenmemiş tüm hak edişler otomatik güncellenir.
                        </div>
                        <button type="submit" class="btn btn-success py-2.5 px-4 fw-bold d-inline-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Ayarları Kaydet</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Column -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-square-fill text-primary me-2"></i>Bilgilendirme</h6>
                <p class="text-secondary small mb-3">
                    Affiliate sistemi için stopaj ve KDV oranları vergilendirme yasaları gereğince tanımlanır.
                </p>
                <ul class="text-secondary small mb-0 ps-3">
                    <li class="mb-2"><strong>Bireysel Üyeler (Stopaj):</strong> Fatura kesemeyen şahıslara yapılan ödemelerde stopaj kesintisi uygulanır.</li>
                    <li><strong>Kurumsal Üyeler (KDV):</strong> Şirket olarak kayıtlı ve fatura kesen üyelere yapılan ödemelerde, fatura tutarına KDV eklenerek ödeme yapılır.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
