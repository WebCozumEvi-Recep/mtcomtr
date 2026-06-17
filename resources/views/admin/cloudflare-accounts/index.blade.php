@extends('layouts.admin')

@section('title', 'Cloudflare Hesapları')

@section('content')
<x-admin.cf-module
    class="max-w-5xl"
    title="Çoklu Cloudflare hesapları"
    subtitle="Her satış domain’i için ayrı Cloudflare hesabı ve API token kullanın. Token’lar veritabanında şifreli saklanır; DNS onboarding bu hesabın yetkileriyle çalışır."
    badge="Hesaplar"
>
    <x-slot name="actions">
        <a href="{{ route('admin.cloudflare-accounts.create') }}" class="cf-btn-primary inline-flex items-center text-white font-black px-6 py-3 rounded-2xl transition text-sm uppercase tracking-wider" style="color:#fff!important;">
            <svg class="w-5 h-5 mr-2 opacity-95" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Yeni hesap
        </a>
    </x-slot>

    @if(session('success'))
        <div class="rounded-2xl border border-orange-200 bg-orange-50 text-orange-900 px-5 py-4 text-sm font-bold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="cf-card premium-card bg-white rounded-[24px] overflow-hidden border border-orange-100/80">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="cf-table-head text-xs uppercase font-black text-orange-900/70 border-b border-orange-100">
                    <tr>
                        <th class="px-6 py-4">Hesap adı</th>
                        <th class="px-6 py-4">Domain</th>
                        <th class="px-6 py-4">Durum</th>
                        <th class="px-6 py-4 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-orange-50">
                    @forelse($accounts as $acc)
                        <tr class="hover:bg-orange-50/40 transition">
                            <td class="px-6 py-4">
                                <span class="font-black text-slate-900">{{ $acc->name }}</span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-700">{{ $acc->domains_count }}</td>
                            <td class="px-6 py-4">
                                @if($acc->is_active)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-orange-100 text-orange-800 border border-orange-200/80">Aktif</span>
                                @else
                                    <span class="status-pill neutral">Pasif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="{{ route('admin.cloudflare-accounts.edit', $acc) }}" class="cf-link font-black text-xs uppercase tracking-wide">Düzenle</a>
                                <form action="{{ route('admin.cloudflare-accounts.destroy', $acc) }}" method="POST" class="inline" onsubmit="return confirmDelete(event, '[target] hesabını silmek istediğinize emin misiniz? İlişkili domainler varsayılan tokena döner.', '{{ $acc->name }}');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 font-black hover:underline text-xs uppercase tracking-wide bg-transparent border-0 p-0 cursor-pointer">Sil</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <p class="text-slate-600 font-medium max-w-md mx-auto">Henüz hesap yok. Domain formunda özel hesap seçebilmek için önce turuncu butonla bir Cloudflare API hesabı ekleyin.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($accounts->hasPages())
        <div class="px-2">{{ $accounts->links() }}</div>
    @endif
</x-admin.cf-module>
@endsection
