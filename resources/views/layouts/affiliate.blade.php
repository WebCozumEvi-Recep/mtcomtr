<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEKSAT | Affiliate Portalı</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        aff: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        },
                        panelBg: '#0f172a',
                        cardBg: 'rgba(30, 41, 59, 0.7)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    boxShadow: {
                        'neon': '0 0 20px rgba(20, 184, 166, 0.2)',
                        'glass': '0 8px 32px 0 rgba(0, 0, 0, 0.37)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: radial-gradient(circle at 50% 50%, #1e293b 0%, #0f172a 100%);
            color: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
        }
        .glassmorphic {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
        }
        .glassmorphic-card {
            background: rgba(30, 41, 59, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 24px;
            box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glassmorphic-card:hover {
            transform: translateY(-4px);
            border-color: rgba(20, 184, 166, 0.3);
            box-shadow: 0 15px 35px 0 rgba(0, 0, 0, 0.25), 0 0 20px rgba(20, 184, 166, 0.1);
        }
        .glassmorphic-input {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            border-radius: 14px;
            transition: all 0.2s;
        }
        .glassmorphic-input:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.15);
        }
        .sidebar-link {
            position: relative;
            transition: all 0.2s ease-in-out;
        }
        .sidebar-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%) scaleY(0);
            width: 4px;
            height: 50%;
            background: #14b8a6;
            border-radius: 0 4px 4px 0;
            transition: transform 0.2s ease-in-out;
        }
        .sidebar-link.active {
            background: rgba(20, 184, 166, 0.12);
            color: #2dd4bf;
            font-weight: 600;
        }
        .sidebar-link.active::before {
            transform: translateY(-50%) scaleY(1);
        }
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.03);
            color: #f1f5f9;
        }
        .fade-in {
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        [x-cloak] { display: none !important; }
    </style>
    @yield('extra_css')
</head>
<body class="antialiased flex h-screen overflow-hidden"
      x-data="{
        isMobileMenuOpen: false,
        toggleMobileMenu() {
            this.isMobileMenuOpen = !this.isMobileMenuOpen;
        }
      }">

    <!-- Mobile Menu Backdrop -->
    <div x-show="isMobileMenuOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="isMobileMenuOpen = false"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 md:hidden"></div>

    <!-- Elegant Sidebar -->
    <aside
        x-cloak
        :class="{
            'translate-x-0': isMobileMenuOpen || window.innerWidth >= 768,
            '-translate-x-full': !isMobileMenuOpen && window.innerWidth < 768,
        }"
        class="w-[280px] bg-slate-900/80 border-r border-white/5 flex flex-col fixed inset-y-0 left-0 z-50 transition-all duration-300 ease-in-out md:static glassmorphic"
    >
        <div class="h-20 flex items-center px-8 border-b border-white/5 flex-shrink-0">
            @php 
                $siteLogo = \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_logo')); 
            @endphp
            <div class="flex flex-col gap-1 w-full">
                <img src="{{ $siteLogo }}" class="h-8 object-contain self-start" style="filter: brightness(0) invert(1);" alt="Site Logo">
                <p class="text-[9px] text-teal-400 font-bold uppercase tracking-widest leading-none mt-1.5">Affiliate Portalı</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 flex flex-col gap-1 overflow-y-auto">
            <a href="{{ route('affiliate.dashboard') }}" class="sidebar-link {{ request()->routeIs('affiliate.dashboard') ? 'active' : '' }} py-3 px-4 rounded-xl flex items-center gap-3 text-slate-400 font-medium" title="Gösterge Paneli">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
                <span class="text-sm font-display">Dashboard</span>
            </a>

            <a href="{{ route('affiliate.campaigns') }}" class="sidebar-link {{ request()->routeIs('affiliate.campaigns') ? 'active' : '' }} py-3 px-4 rounded-xl flex items-center gap-3 text-slate-400 font-medium" title="Kampanyalar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                <span class="text-sm font-display">Satış Siteleri</span>
            </a>

            <a href="{{ route('affiliate.links') }}" class="sidebar-link {{ request()->routeIs('affiliate.links') ? 'active' : '' }} py-3 px-4 rounded-xl flex items-center gap-3 text-slate-400 font-medium" title="Linklerim">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                <span class="text-sm font-display">Linklerim</span>
            </a>

            <a href="{{ route('affiliate.media') }}" class="sidebar-link {{ request()->routeIs('affiliate.media') ? 'active' : '' }} py-3 px-4 rounded-xl flex items-center gap-3 text-slate-400 font-medium" title="Medya Merkezi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="text-sm font-display">Medya Merkezi</span>
            </a>

            <a href="{{ route('affiliate.stats') }}" class="sidebar-link {{ request()->routeIs('affiliate.stats') ? 'active' : '' }} py-3 px-4 rounded-xl flex items-center gap-3 text-slate-400 font-medium" title="İstatistikler">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2a2 2 0 01-2-2zm0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span class="text-sm font-display">İstatistikler</span>
            </a>

            <a href="{{ route('affiliate.withdrawals') }}" class="sidebar-link {{ request()->routeIs('affiliate.withdrawals') ? 'active' : '' }} py-3 px-4 rounded-xl flex items-center gap-3 text-slate-400 font-medium" title="Hak Edişler">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm font-display">Hak Ediş & Ödeme</span>
            </a>
        </nav>

        <!-- Sidebar Footer / Logout -->
        <div class="p-6 border-t border-white/5 bg-slate-950/20 mt-auto">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('affiliate')->user()->name) }}&background=2dd4bf&color=0f172a&rounded=true&bold=true" class="w-9 h-9 rounded-xl border border-white/10" alt="Avatar">
                    <div class="overflow-hidden max-w-[140px]">
                        <p class="text-xs font-bold text-white truncate">{{ Auth::guard('affiliate')->user()->name }}</p>
                        <p class="text-[10px] text-slate-500 font-semibold truncate">{{ Auth::guard('affiliate')->user()->affiliate_code }}</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('affiliate.settings') }}" class="flex-1 py-2.5 px-3 rounded-xl bg-teal-500/10 hover:bg-teal-500/20 text-teal-400 text-xs font-bold flex items-center justify-center gap-1.5 border border-teal-500/10 transition-all {{ request()->routeIs('affiliate.settings') ? 'bg-teal-500/20 text-teal-300' : '' }}" title="Hesap Ayarları">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Ayarlar
                </a>
                <form method="POST" action="{{ route('affiliate.logout') }}" class="flex-1 m-0">
                    @csrf
                    <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-bold flex items-center justify-center gap-1.5 border border-rose-500/10 transition-all" title="Çıkış Yap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Çıkış
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Workspace -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <!-- Top Header -->
        <header class="h-20 flex items-center justify-between px-6 md:px-10 border-b border-white/5 sticky top-0 z-10 glassmorphic">
            <div class="flex items-center gap-4">
                <!-- Hamburger Menu Trigger (Mobile only) -->
                <button @click="toggleMobileMenu()" class="p-2 -ml-2 text-slate-400 hover:text-white transition md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="hidden md:flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                    <span class="text-slate-500 font-display">TEKSAT</span>
                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"></path></svg>
                    <span class="text-teal-400 font-display">@yield('title', 'Dashboard')</span>
                </div>
            </div>

            <!-- Balances pills in top menu -->
            <div class="flex items-center gap-4">
                @php
                    $comQuery = \App\Models\AffiliateCommission::where('affiliate_id', Auth::guard('affiliate')->id());
                    $approvedSum = $comQuery->clone()->where('status', 'approved')->sum('net_amount');
                @endphp
                <div class="flex items-center gap-2 bg-teal-500/10 border border-teal-500/20 px-3.5 py-1.5 rounded-full">
                    <div class="w-1.5 h-1.5 rounded-full bg-teal-400 animate-pulse"></div>
                    <span class="text-[10px] md:text-xs font-bold text-teal-400 font-display">Çekilebilir Bakiye:</span>
                    <span class="text-xs md:text-sm font-extrabold text-white font-display">{{ number_format($approvedSum, 2) }} TL</span>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="flex-1 overflow-auto p-6 md:p-10 fade-in">
            @if(session('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="mb-6 p-4 bg-teal-500/10 border border-teal-500/20 text-teal-300 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span class="text-sm font-semibold">{!! session('success') !!}</span>
                    </div>
                    <button @click="show = false" class="text-teal-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-300 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-sm font-semibold">{!! session('error') !!}</span>
                    </div>
                    <button @click="show = false" class="text-rose-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @yield('extra_js')
</body>
</html>
