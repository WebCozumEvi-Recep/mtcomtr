<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Affiliate Sistemi - Teksat Admin')</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_favicon'), asset('favicon.ico')) }}">
    <!-- Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        /* Sidebar Styling */
        .admin-sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
            box-shadow: 4px 0 25px rgba(15, 23, 42, 0.15);
        }

        .brand-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
        }

        .sidebar-link {
            border-radius: 0.65rem;
            color: rgba(255, 255, 255, 0.75);
            padding: 0.65rem 0.8rem;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .sidebar-link.active {
            background-color: #2563eb;
            color: #fff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .sidebar-link i {
            font-size: 1.1rem;
        }

        /* Content Wrapper */
        .admin-content {
            background: #f8fafc;
            padding: 2.5rem;
        }

        /* Custom Card styles */
        .card {
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            background-color: #ffffff;
        }

        /* Premium Scrollbar */
        .table-responsive {
            overflow-x: auto;
            overflow-y: visible;
        }
        .table-responsive::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: transparent;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Desktop Layout Fixes (No Double Scrollbars, Sidebar always stays on screen) */
        @media (min-width: 768px) {
            .admin-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                width: 240px;
                height: 100vh;
                z-index: 1000;
                overflow-y: auto;
            }
            .admin-content {
                margin-left: 240px;
                min-height: 100vh;
            }
        }
    </style>
    @yield('extra_css')
</head>
<body>
    <div class="container-fluid p-0">
        <div class="d-flex flex-column flex-md-row">
            <!-- Sidebar -->
            <aside class="admin-sidebar text-white p-4 d-flex flex-column">
                <!-- Branding Header -->
                @php 
                    $siteLogo = \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_logo')) ?: asset('/images/landing/logo_full.png'); 
                @endphp
                <div class="mb-4">
                    <div class="d-flex flex-column gap-1">
                        <img src="{{ $siteLogo }}" class="object-contain" style="max-height: 36px; max-width: 160px; filter: brightness(0) invert(1);" alt="Site Logo">
                        <p class="text-white-50 font-monospace uppercase leading-none m-0 mt-1.5" style="font-size: 8px; letter-spacing: 1px;">Yönetim Paneli</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="nav flex-column gap-2 flex-grow-1">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link sidebar-link mb-3" style="background: rgba(255,255,255,0.06); border-radius: 8px;">
                        <i class="bi bi-arrow-left-circle-fill"></i>
                        <span>Ana Panele Dön</span>
                    </a>

                    <div class="text-uppercase text-white-50 font-monospace small mb-2 px-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Affiliate Sistemi</div>

                    <a href="{{ route('admin.affiliate.stats') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.affiliate.stats') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line-fill"></i>
                        <span>Genel İstatistikler</span>
                    </a>

                    <a href="{{ route('admin.affiliate.users') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.affiliate.users*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Affiliate Üyeler</span>
                    </a>

                    <a href="{{ route('admin.affiliate.commissions') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.affiliate.commissions*') ? 'active' : '' }}">
                        <i class="bi bi-wallet2"></i>
                        <span>Hak Edişler</span>
                    </a>

                    <a href="{{ route('admin.affiliate.withdrawals') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.affiliate.withdrawals*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>
                        <span>Ödeme Talepleri</span>
                    </a>

                    <a href="{{ route('admin.affiliate.media.index') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.affiliate.media*') ? 'active' : '' }}">
                        <i class="bi bi-images"></i>
                        <span>Medya & Banner</span>
                    </a>

                    <a href="{{ route('admin.affiliate.settings') }}" class="nav-link sidebar-link {{ request()->routeIs('admin.affiliate.settings') ? 'active' : '' }}">
                        <i class="bi bi-gear-fill"></i>
                        <span>Oran Ayarları</span>
                    </a>
                </nav>

                <!-- Sidebar Footer Pinned to Bottom -->
                <div class="pt-4 border-top border-secondary mt-auto">
                    <div class="rounded-3 p-3 mb-3" style="background-color: rgba(255,255,255,0.06);">
                        <small class="d-block text-white-50">Aktif Kullanici</small>
                        <div class="fw-semibold text-white">{{ auth()->user()->name }}</div>
                        <small class="text-uppercase text-white-50">{{ auth()->user()->role->value }}</small>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2 text-white border-danger hover:bg-danger">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Çıkış Yap</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content area (Scrolls naturally with viewport) -->
            <main class="admin-content flex-grow-1">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
