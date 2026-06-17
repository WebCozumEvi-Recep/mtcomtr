@extends('layouts.admin')

@section('title', 'Sistem Bildirim Geçmişi')

@section('content')
<div class="flex flex-col gap-6 fade-in-up">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Sistem Bildirimleri</h2>
            <p class="text-sm text-slate-500 font-medium">Sistem genelindeki kritik işlemler ve bildirim geçmişi</p>
        </div>
        <div class="flex gap-2">
            <button onclick="markAllAsRead()" class="bg-brand-600 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-brand-700 transition shadow-md flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Tümünü Okundu İşaretle
            </button>
        </div>
    </div>

    <!-- Users Filter -->
    <div class="premium-card p-4">
        <div class="flex flex-wrap gap-2">
            @foreach($users as $user)
                <a href="{{ route('admin.users.alerts', $user->id) }}" class="flex items-center gap-2 px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl hover:bg-white hover:border-brand-500 transition group shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs uppercase group-hover:bg-brand-100 group-hover:text-brand-600">
                        {{ substr($user->name, 0, 2) }}
                    </div>
                    <span class="text-sm font-bold text-slate-700 group-hover:text-brand-600">{{ $user->name }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Data Table -->
    <div class="premium-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500">
                    <tr>
                        <th class="px-6 py-4 border-b border-slate-200">Tür</th>
                        <th class="px-6 py-4 border-b border-slate-200">Başlık</th>
                        <th class="px-6 py-4 border-b border-slate-200">Mesaj</th>
                        <th class="px-6 py-4 border-b border-slate-200">İşlemi Yapan</th>
                        <th class="px-6 py-4 border-b border-slate-200">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-[10px] font-black uppercase tracking-wider
                                    @if($alert->type == 'danger') bg-red-100 text-red-600 
                                    @elseif($alert->type == 'warning') bg-amber-100 text-amber-600 
                                    @else bg-blue-100 text-blue-600 @endif">
                                    @if($alert->type == 'danger') KRİTİK
                                    @elseif($alert->type == 'warning') UYARI
                                    @else BİLGİ @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $alert->title }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {!! $alert->message !!}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                        {{ substr($alert->causer->name ?? 'Sistem', 0, 2) }}
                                    </div>
                                    <span class="font-medium text-slate-700">{{ $alert->causer->name ?? 'Sistem' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-400">
                                {{ $alert->created_at->format('d.m.Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">
                                Henüz bir bildirim kaydı bulunmuyor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($alerts->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function markAllAsRead() {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Tüm bildirimleriniz okundu olarak işaretlenecek.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Evet, İşaretle',
        cancelButtonText: 'İptal',
        background: '#ffffff',
        customClass: {
            popup: 'rounded-3xl border-none shadow-2xl',
            confirmButton: 'rounded-full px-8 py-3 font-bold',
            cancelButton: 'rounded-full px-8 py-3 font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route('admin.alerts.mark-all-read') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Başarılı', 'Tüm bildirimler okundu olarak işaretlendi.', 'success')
                    .then(() => location.reload());
                }
            });
        }
    })
}
</script>
@endpush
@endsection
