@extends('layouts.admin')

@inject('auth', 'Illuminate\Support\Facades\Auth')
@inject('session', 'Illuminate\Support\Facades\Session')
@inject('url', 'Illuminate\Support\Facades\URL')

@section('title', 'Marka Yönetimi')

@section('content')
@php 
    $user = $auth::user();
@endphp
<div class="flex flex-col gap-6 fade-in-up">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Markalar</h2>
            <p class="text-sm text-slate-500 font-medium">Sistemdeki tüm markalar ve bağlı domain sayıları</p>
        </div>
        @if($user->hasPermission('brands.edit'))
        <button onclick="openBrandModal()" class="bg-brand-600 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-brand-700 transition shadow-md flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Yeni Marka Ekle
        </button>
        @endif
    </div>

    @if($session::has('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg font-medium shadow-sm">
            {{ $session::get('success') }}
        </div>
    @endif

    @if($session::has('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-lg font-medium shadow-sm">
            {{ $session::get('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($brands as $brand)
            <div class="premium-card p-6 flex flex-col justify-between hover:shadow-lg transition group">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 group-hover:text-brand-600 transition">{{ $brand->name }}</h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $brand->is_active ? 'text-green-500' : 'text-slate-400' }}">
                                {{ $brand->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition">
                        @if($user->hasPermission('brands.edit'))
                        <button onclick="editBrand({{ $brand->id }}, '{{ $brand->name }}', {{ $brand->is_active ? 1 : 0 }})" class="p-2 bg-slate-100 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="Düzenle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        @endif

                        @if($user->hasPermission('brands.delete'))
                        <form action="{{ $url::route('admin.brands.destroy', $brand) }}" method="POST" onsubmit="return confirm('Bu markayı silmek istediğinize emin misiniz?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 bg-red-50 text-red-400 hover:text-red-600 hover:bg-red-100 rounded-lg transition" title="Sil">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-50 flex justify-between items-center">
                    <div class="text-xs text-slate-500 font-medium">
                        Bağlı Domain Sayısı: <span class="font-black text-slate-900 ml-1">{{ $brand->domains_count }}</span>
                    </div>
                    <a href="{{ $url::route('admin.orders') }}?brand_id={{ $brand->id }}" class="text-[10px] font-black uppercase text-brand-600 hover:underline">Siparişleri Gör</a>
                </div>
            </div>
        @empty
            <div class="col-span-full premium-card p-12 text-center text-slate-500 font-medium">
                Sistemde kayıtlı marka bulunamadı.
            </div>
        @endforelse
    </div>
</div>

<!-- Brand Modal -->
<div id="brandModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in duration-200">
        <form id="brandForm" action="{{ $url::route('admin.brands.store') }}" method="POST">
            @csrf
            <input type="hidden" id="brandMethod" name="_method" value="POST">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 id="modalTitle" class="text-xl font-black text-slate-900 tracking-tight">Yeni Marka Ekle</h3>
                <button type="button" onclick="closeBrandModal()" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Marka Adı</label>
                    <input type="text" name="name" id="brandName" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition shadow-sm" placeholder="Örn. Nippon, New Well">
                </div>
                <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="brandStatus" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                    </label>
                    <span class="text-xs font-black text-slate-600 uppercase tracking-wider">MARKA AKTİF</span>
                </div>
            </div>
            <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex gap-3">
                <button type="button" onclick="closeBrandModal()" class="flex-1 px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-white border border-slate-200 transition">İptal</button>
                <button type="submit" class="flex-1 px-6 py-4 rounded-2xl text-xs font-black uppercase tracking-widest text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-600/20 transition">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('brandModal');
    const form = document.getElementById('brandForm');
    const title = document.getElementById('modalTitle');
    const nameInput = document.getElementById('brandName');
    const statusInput = document.getElementById('brandStatus');
    const methodInput = document.getElementById('brandMethod');

    function openBrandModal() {
        title.innerText = 'Yeni Marka Ekle';
        form.action = '{{ $url::route('admin.brands.store') }}';
        methodInput.value = 'POST';
        nameInput.value = '';
        statusInput.checked = true;
        modal.classList.remove('hidden');
    }

    function editBrand(id, name, isActive) {
        title.innerText = 'Markayı Düzenle';
        form.action = `/admin/brands/${id}`;
        methodInput.value = 'PUT';
        nameInput.value = name;
        statusInput.checked = isActive;
        modal.classList.remove('hidden');
    }

    function closeBrandModal() {
        modal.classList.add('hidden');
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            closeBrandModal();
        }
    }
</script>
@endsection
