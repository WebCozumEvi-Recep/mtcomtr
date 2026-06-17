@extends('admin.layout')

@section('title', 'Promosyon Medyaları - ' . config('app.name') . ' Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-0 text-gray-800 fw-bold">Promosyon Materyalleri Yönetimi</h2>
        <p class="text-muted mb-0">Affiliate üyelerinin kampanyalarında kullanabilmesi için hazırlanan görsel, banner ve videolar.</p>
    </div>
    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 py-2" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
        <i class="bi bi-cloud-upload"></i>
        <span>Yeni Materyal Ekle</span>
    </button>
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

<!-- Medya Grid Listesi -->
<div class="row g-4">
    @forelse($media as $m)
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <!-- Medya Dosyası Önizleme -->
                <div class="bg-light d-flex align-items-center justify-content-center overflow-hidden position-relative" style="height: 320px;">
                    @if($m->media_type === 'video')
                        <video class="w-100 h-100" style="object-fit: contain; background-color: #f1f5f9;" controls>
                            <source src="{{ asset('uploads/affiliate_media/' . $m->file_path) }}" type="video/mp4">
                            Tarayıcınız video oynatmayı desteklemiyor.
                        </video>
                    @else
                        <img src="{{ asset('uploads/affiliate_media/' . $m->file_path) }}" class="w-100 h-100" style="object-fit: contain; background-color: #f1f5f9;" alt="{{ $m->title }}">
                    @endif
                    
                    <span class="position-absolute top-2 start-2 badge bg-dark bg-opacity-75 text-uppercase">
                        {{ $m->media_type }}
                    </span>
                    
                    @if($m->size_label)
                        <span class="position-absolute bottom-2 start-2 badge bg-secondary text-uppercase small" style="font-size: 0.7rem;">
                            {{ $m->size_label }}
                        </span>
                    @endif
                </div>
                
                <div class="card-body d-flex flex-column p-3">
                    <h5 class="card-title fw-bold text-dark text-truncate mb-1" title="{{ $m->title }}">{{ $m->title }}</h5>
                    <p class="card-text text-muted mb-2 font-monospace small" style="font-size: 0.75rem;">
                        <i class="bi bi-globe me-1"></i>{{ $m->domain?->domain_name ?: 'Herhangi Sitede' }}
                    </p>
                    
                    @if($m->share_text)
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small mb-1" style="font-size: 0.7rem;">Hazır Paylaşım Metni</label>
                            <textarea class="form-control bg-light font-monospace" style="font-size: 0.75rem; resize: none; height: 60px;" readonly>{{ $m->share_text }}</textarea>
                        </div>
                    @endif
                    
                    <div class="mt-auto d-flex gap-2">
                        <a href="{{ asset('uploads/affiliate_media/' . $m->file_path) }}" download class="btn btn-outline-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-download"></i> İndir
                        </a>
                        
                        <form method="POST" action="{{ route('admin.affiliate.media.destroy', $m) }}" class="w-100" onsubmit="return confirm('Bu promosyon materyalini tamamen silmek istediğinize emin misiniz?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-trash"></i> Sil
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm py-5 text-center text-muted">
                <div class="card-body">
                    <i class="bi bi-images display-4 mb-3 text-secondary"></i>
                    <h4 class="fw-bold text-dark">Promosyon Materyali Bulunmuyor</h4>
                    <p class="mb-4">Affiliate üyelerinizin kampanya oluştururken kullanacağı hiçbir medya görseli henüz yüklenmemiş.</p>
                    <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
                        <i class="bi bi-cloud-upload me-1"></i> Materyal Yükle
                    </button>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($media->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $media->links() }}
    </div>
@endif

<!-- Yeni Materyal Ekleme Modal -->
<div class="modal fade" id="uploadMediaModal" tabindex="-1" aria-labelledby="uploadMediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom bg-light">
                <h5 class="modal-title fw-bold" id="uploadMediaModalLabel"><i class="bi bi-cloud-upload me-2 text-primary"></i>Yeni Materyal Yükle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.affiliate.media.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 row g-3">
                    <div class="col-md-6">
                        <label for="domain_id" class="form-label text-secondary fw-semibold small">İlgili Satış Sitesi (Domain)</label>
                        <select class="form-select" id="domain_id" name="domain_id" required>
                            <option value="">Seçiniz...</option>
                            @foreach($domains as $dom)
                                <option value="{{ $dom->id }}">{{ $dom->domain_name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text small">Bu materyalin hangi web sitesi kampanyası için gösterileceği.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="title" class="form-label text-secondary fw-semibold small">Materyal Adı (Başlık)</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Örn: Nippon Şampuan Story Görseli" required>
                    </div>

                    <div class="col-md-6">
                        <label for="media_type" class="form-label text-secondary fw-semibold small">Medya Türü</label>
                        <select class="form-select" id="media_type" name="media_type" required>
                            <option value="image">Görsel (Image)</option>
                            <option value="banner">Banner Reklamı</option>
                            <option value="video">Tanıtım Videosu</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="size_label" class="form-label text-secondary fw-semibold small">Ebat / Ölçü Etiketi (Opsiyonel)</label>
                        <input type="text" class="form-control" id="size_label" name="size_label" placeholder="Örn: 1080x1920 (Story), 300x250 (Banner)">
                    </div>

                    <div class="col-12">
                        <label for="media_file" class="form-label text-secondary fw-semibold small">Dosya Seçin</label>
                        <input class="form-control" type="file" id="media_file" name="media_file" accept=".jpg,.jpeg,.png,.webp,.mp4,.mov" required>
                        <div class="form-text small">Desteklenen uzantılar: JPEG, PNG, WEBP, MP4, MOV (Maks. 20MB)</div>
                    </div>

                    <div class="col-12">
                        <label for="share_text" class="form-label text-secondary fw-semibold small">Özel Paylaşım Metni (Hazır Şablon - Opsiyonel)</label>
                        <textarea class="form-control font-monospace" id="share_text" name="share_text" rows="4" placeholder="Üyelerin sosyal medyada kopyalayıp doğrudan paylaşabileceği hazır metin şablonu..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-cloud-upload"></i> Materyali Yükle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
