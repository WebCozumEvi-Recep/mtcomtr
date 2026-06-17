@extends('layouts.admin')

@section('title', $title ?? 'Modül')

@section('content')
<div class="h-full flex flex-col items-center justify-center text-center py-20 fade-in-up">
    <div class="w-24 h-24 bg-brand-50 text-brand-500 rounded-[24px] flex items-center justify-center mb-6 shadow-sm mx-auto border border-brand-100">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
    </div>
    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-3">Çok Yakında!</h2>
    <p class="text-lg text-slate-500 font-medium max-w-md mx-auto leading-relaxed">"{{ $title ?? 'Bu Modül' }}" için altyapı hazırlandı. Arayüz geliştirme süreci listemizde sırasını bekliyor.</p>
</div>
@endsection
