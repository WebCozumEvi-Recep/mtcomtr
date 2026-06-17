@extends('admin.layout')

@section('title', 'Kullanicilar')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0">Kullanicilar</h2>
            <small class="text-secondary">Tum hesaplar ve roller</small>
        </div>
        <span class="badge text-bg-dark">Toplam: {{ \App\Models\User::count() }}</span>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ad</th>
                        <th>E-posta</th>
                        <th>Rol</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (\App\Models\User::latest()->get() as $user)
                        <tr>
                            <td class="fw-medium">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->role->value === 'admin' ? 'text-bg-primary' : 'text-bg-secondary' }} text-capitalize">
                                    {{ $user->role->value }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-secondary py-4">Kullanici bulunamadi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
