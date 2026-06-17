<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | Admin Panel</title>
    <!-- Dynamic Favicon -->
    <link rel="icon" href="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_favicon'), asset('favicon.ico')) }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.confirmDelete = function(event, message, target = '') {
            event.preventDefault();
            const form = event.target.closest('form');
            
            // Replace [target] with bold version if target is provided
            const formattedMessage = target ? message.replace('[target]', `<strong class="text-slate-900 font-black">${target}</strong>`) : message;

            Swal.fire({
                title: 'Emin misiniz?',
                html: `<div class="text-slate-600 mb-4">${formattedMessage}</div>`,
                icon: 'warning',
                showCancelButton: true,
                showCloseButton: true,
                confirmButtonColor: '#22c55e',
                cancelButtonColor: '#f43f5e',
                confirmButtonText: 'Evet, İşleme Devam Et',
                cancelButtonText: 'Hayır, İptal',
                background: '#ffffff',
                customClass: {
                    popup: 'rounded-3xl border-none shadow-2xl',
                    confirmButton: 'rounded-xl px-6 py-3 font-bold order-2',
                    cancelButton: 'rounded-xl px-6 py-3 font-bold order-1',
                    closeButton: 'text-slate-400 hover:text-slate-600 transition'
                },
                reverseButtons: true,
                showClass: {
                    popup: 'animate__animated animate__fadeInUp animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutDown animate__faster'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        };

        window.confirmAction = function(event, message, title = 'Emin misiniz?') {
            event.preventDefault();
            const form = event.target.closest('form');
            const href = event.target.closest('a')?.href;
            
            Swal.fire({
                title: title,
                html: `<div class="text-slate-600 mb-4">${message}</div>`,
                icon: 'question',
                showCancelButton: true,
                showCloseButton: true,
                confirmButtonColor: '#22c55e',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Evet, Devam Et',
                cancelButtonText: 'Hayır, İptal',
                background: '#ffffff',
                customClass: {
                    popup: 'rounded-3xl border-none shadow-2xl',
                    confirmButton: 'rounded-xl px-6 py-3 font-bold order-2',
                    cancelButton: 'rounded-xl px-6 py-3 font-bold order-1',
                    closeButton: 'text-slate-300 hover:text-slate-600 transition'
                },
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    if (form) form.submit();
                    else if (href) window.location.href = href;
                }
            });
            return false;
        };
    </script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        background: '#f8fafc',
                        surface: '#ffffff'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'glass': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
                        'soft': '0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01)',
                        'inner-white': 'inset 0 1px 0 rgba(255, 255, 255, 1)',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
        }
        .main-workspace {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        /* Premium Card Component */
        .premium-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01), inset 0 1px 0 rgba(255, 255, 255, 1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .premium-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.02), inset 0 1px 0 rgba(255, 255, 255, 1);
            transform: translateY(-2px);
        }
        /* Sidebar layout */
        .sidebar-item {
            position: relative;
            transition: all 0.2s ease-out;
        }
        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%) scaleY(0);
            width: 4px;
            height: 60%;
            background: #16a34a;
            border-radius: 0 4px 4px 0;
            transition: transform 0.2s ease-out;
        }
        .sidebar-item:hover, .sidebar-item.active {
            background: #f0fdf4;
            color: #166534;
            font-weight: 600;
        }
        .sidebar-item:hover::before, .sidebar-item.active::before {
            transform: translateY(-50%) scaleY(1);
        }
        .sidebar-item-cf:hover, .sidebar-item-cf.active {
            background: #fff7ed !important;
            color: #9a3412 !important;
        }
        .sidebar-item-cf:hover::before, .sidebar-item-cf.active::before {
            background: #f38020 !important;
        }
        /* Minimal pill */
        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .status-pill.success { background: #dcfce7; color: #166534; }
        .status-pill.warning { background: #fef08a; color: #854d0e; }
        .status-pill.danger { background: #fee2e2; color: #991b1b; }
        .status-pill.neutral { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        
        /* Loading animation for specific elements */
        .fade-in-up {
            animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(15px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #94a3b8 transparent; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
        [x-cloak] { display: none !important; }

        /* Star Button Styling (using standard CSS for CDN compatibility) */
        .favorite-btn {
            padding: 0.375rem;
            border-radius: 0.5rem;
            border: 1px solid #f1f5f9;
            background-color: #ffffff;
            transition: all 0.2s;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .favorite-btn:hover {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            transform: scale(1.05);
        }

        /* Favorite Animation */
        .star-animate { animation: star-bounce 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes star-bounce {
            0% { transform: scale(1); }
            50% { transform: scale(1.5); }
            100% { transform: scale(1); }
        }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('favoriteManager', () => ({
                toggle(id, title, url, icon) {
                    fetch('{{ route("admin.favorites.toggle") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ id, title, url, icon })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'success') {
                            const event = new CustomEvent('favorite-updated', { detail: data });
                            window.dispatchEvent(event);
                            
                            // Visual feedback on the star
                            const star = document.querySelector(`[data-favorite-id="${id}"]`);
                            if(star) {
                                star.classList.add('star-animate');
                                setTimeout(() => star.classList.remove('star-animate'), 400);
                                if(data.favorite_status === 'added') {
                                    star.classList.add('text-amber-400');
                                    star.classList.remove('text-slate-300');
                                } else {
                                    star.classList.remove('text-amber-400');
                                    star.classList.add('text-slate-300');
                                }
                            }
                        }
                    });
                }
            }))
        })
    </script>
    @yield('extra_css')
</head>

{{-- Alpine.js State Management for Sidebar --}}
<body class="antialiased flex h-screen overflow-hidden font-sans" 
      x-data="{ 
        isSidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        sidebarHover: false,
        isMobileMenuOpen: false,
        toggleSidebar() {
            if (window.innerWidth < 768) {
                this.isMobileMenuOpen = !this.isMobileMenuOpen;
            } else {
                this.isSidebarCollapsed = !this.isSidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.isSidebarCollapsed);
            }
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
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 md:hidden"></div>

    <!-- Elegant Sidebar -->
    <aside 
        x-cloak
        :class="{
            'w-[80px]': isSidebarCollapsed && !sidebarHover,
            'w-[280px]': !isSidebarCollapsed || sidebarHover,
            'translate-x-0': isMobileMenuOpen || window.innerWidth >= 768,
            '-translate-x-full': !isMobileMenuOpen && window.innerWidth < 768,
        }"
        @mouseenter="sidebarHover = true" 
        @mouseleave="sidebarHover = false"
        class="bg-white border-r border-slate-200 flex flex-col fixed inset-y-0 left-0 z-50 transition-all duration-300 ease-in-out overflow-x-hidden shadow-soft"
    >
        <!-- Sidebar Toggle Button (Floating on edge - Desktop Only) -->
        <button @click="toggleSidebar()" 
                class="absolute right-0 top-24 z-30 bg-white border border-slate-200 p-1 rounded-full text-slate-400 hover:text-brand-600 shadow-sm transition hover:scale-110 hidden md:flex items-center justify-center translate-x-1/2"
                title="Menüyü Daralt/Aç">
            <svg class="w-4 h-4 transition-transform duration-300" :class="isSidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
        </button>

        <div class="h-20 flex items-center px-6 border-b border-slate-100 flex-shrink-0">
            @php 
                $siteLogo = \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_logo')); 
                $siteFavicon = \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_favicon'), asset('favicon.ico'));
            @endphp
            
            <div class="flex items-center w-full overflow-hidden">
                <template x-if="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen">
                    <img src="{{ $siteLogo }}" class="h-8 object-contain" alt="Logo">
                </template>
                <template x-if="isSidebarCollapsed && !sidebarHover && !isMobileMenuOpen">
                    <img src="{{ $siteFavicon }}" class="h-8 w-8 object-contain mx-auto" alt="Favicon">
                </template>
            </div>
        </div>
        


        <nav class="flex-1 px-3 flex flex-col gap-0.5 overflow-y-auto overflow-x-hidden custom-scrollbar">
            @if(auth()->user()->hasPermission('dashboard.view'))
            <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Genel Bakış">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Genel Bakış</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('orders.view'))
            <a href="{{ route('admin.orders') }}" class="sidebar-item {{ request()->routeIs('admin.orders') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Sipariş Yönetimi">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Sipariş Yönetimi</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('cargo.view'))
            <a href="{{ route('admin.cargo.reconciliation') }}" class="sidebar-item {{ request()->routeIs('admin.cargo.reconciliation') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Kargo Mutabakatı">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Kargo Mutabakatı</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('domains.view'))
            <a href="{{ route('admin.domains') }}" class="sidebar-item {{ request()->routeIs('admin.domains*') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Domain (Ağ)">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Domain (Ağ)</span>
            </a>
            @endif
            
            @if(auth()->user()->hasPermission('products.view'))
            <a href="{{ route('admin.catalog') }}" class="sidebar-item {{ request()->routeIs('admin.catalog') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Ürünler">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Ürünler</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('brands.view'))
            <a href="{{ route('admin.brands.index') }}" class="sidebar-item {{ request()->routeIs('admin.brands.*') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Markalar">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Markalar</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('risk.view'))
            <a href="{{ route('admin.risk') }}" class="sidebar-item {{ request()->routeIs('admin.risk') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Risk Analizi">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Risk Analizi</span>
            </a>
            @endif


            

            
            @if(auth()->user()->hasPermission('users.view'))
            <a href="{{ route('admin.users') }}" class="sidebar-item {{ request()->routeIs('admin.users') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Kullanıcılar">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Kullanıcılar</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('domains.view'))
             <a href="{{ route('admin.affiliate.stats') }}" class="sidebar-item {{ request()->routeIs('admin.affiliate.*') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Affiliate Sistemi">
                 <div class="w-8 h-8 flex items-center justify-center shrink-0">
                     <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 </div>
                 <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Affiliate Sistemi</span>
             </a>
             @endif

            @if(auth()->user()->hasPermission('settings.view'))
            <a href="{{ route('admin.settings.index') }}" class="sidebar-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Ayarlar">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Sistem Ayarları</span>
            </a>

            <a href="{{ route('admin.cargo-settings.index') }}" class="sidebar-item {{ request()->routeIs('admin.cargo-settings.*') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Kargo Ayarları">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Kargo Ayarları</span>
            </a>

            <a href="{{ route('admin.payment-providers.index') }}" class="sidebar-item {{ request()->routeIs('admin.payment-providers.*') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Ödeme Ayarları">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Ödeme Ayarları</span>
            </a>

            <a href="{{ route('admin.settings.messages') }}" class="sidebar-item {{ request()->routeIs('admin.settings.messages') ? 'active' : '' }} py-2 px-3 rounded-xl flex items-center text-slate-600 group/item" title="Mesaj Ayarları">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm whitespace-nowrap transition-all duration-200">Mesaj Ayarları</span>
            </a>
            @endif

        </nav>
        
        <div class="p-4 border-t border-slate-100">
             <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full sidebar-item py-2 px-3 rounded-xl flex items-center text-red-600 hover:text-red-800 hover:bg-red-50 group/item" title="Çıkış Yap">
                    <div class="w-8 h-8 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 opacity-70 group-hover/item:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </div>
                    <span x-show="!isSidebarCollapsed || sidebarHover || isMobileMenuOpen" class="ml-1 text-sm font-bold whitespace-nowrap transition-all duration-200">Güvenli Çıkış</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Workspace -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden main-workspace relative transition-all duration-300"
          :class="{
              'md:ml-[80px]': isSidebarCollapsed && !sidebarHover,
              'md:ml-[280px]': !isSidebarCollapsed || sidebarHover
          }">
        <!-- Top App Bar -->
        <header class="h-20 flex items-center justify-between px-6 md:px-10 bg-white/60 backdrop-blur-md border-b border-slate-200/60 sticky top-0 z-10 fade-in-up" style="animation-delay: 0.1s;">
            <div class="flex items-center gap-4">
                <!-- Hamburger Trigger (Visible on all screens now) -->
                <button @click="toggleSidebar()" class="p-2 -ml-2 text-slate-400 hover:text-brand-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                
                <!-- Breadcrumbs -->
                <nav class="flex items-center text-xs font-bold tracking-widest uppercase">
                    <span class="text-slate-400">Panel</span>
                    <svg class="w-3 h-3 mx-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <span class="text-brand-600">@yield('title', 'Genel Bakış')</span>
                </nav>
            </div>
            
            <div class="flex items-center gap-6">

                <!-- System Notifications -->
                <div class="relative" x-data="{ 
                    open: false, 
                    alerts: [], 
                    unreadCount: 0,
                    fetchAlerts() {
                        fetch('{{ route('admin.alerts.latest') }}')
                            .then(response => response.json())
                            .then(data => {
                                this.alerts = data.alerts;
                                this.unreadCount = data.unread_count;
                            });
                    },
                    markAsRead(id) {
                        fetch(`/admin/alerts/${id}/mark-as-read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                this.fetchAlerts();
                            }
                        });
                    }
                }" x-init="fetchAlerts(); setInterval(() => fetchAlerts(), 30000)">
                    <button @click="open = !open" class="relative p-2 text-slate-400 hover:text-brand-600 transition outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <template x-if="unreadCount > 0">
                            <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full"></span>
                        </template>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-50">
                        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <span class="text-xs font-black text-slate-900 uppercase tracking-widest" x-text="'Bildirimler (' + unreadCount + ')'"></span>
                            <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[10px] font-black rounded-full" x-show="unreadCount > 0">Yeni</span>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <template x-for="alert in alerts" :key="alert.id">
                                <div class="p-4 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer group" @click="markAsRead(alert.id)">
                                    <div class="flex justify-between items-start gap-2">
                                        <p class="text-xs font-bold text-slate-800 leading-snug" x-html="alert.message"></p>
                                        <button class="text-slate-300 hover:text-slate-600 transition" @click.stop="markAsRead(alert.id)">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <span class="text-[10px] font-medium text-slate-400 mt-1 block" x-text="new Date(alert.created_at).toLocaleString('tr-TR')"></span>
                                </div>
                            </template>
                            <template x-if="alerts.length === 0">
                                <div class="p-8 text-center text-slate-400 text-xs font-medium">
                                    Yeni bildirim bulunmuyor.
                                </div>
                            </template>
                        </div>
                        <a href="{{ route('admin.alerts.index') }}" class="block p-3 text-center text-[11px] font-black text-brand-600 uppercase hover:bg-brand-50 transition-colors">Geçmişi Gör</a>
                    </div>
                </div>

                <div class="flex items-center gap-3 cursor-pointer">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-bold text-slate-900">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs font-semibold text-slate-500">
                            @if(auth()->user()->customRole)
                                {{ auth()->user()->customRole->name }}
                            @else
                                {{ auth()->user()->role->value ?? 'Kullanıcı' }}
                            @endif
                        </p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'A' }}&background=14532d&color=fff&rounded=true&bold=true" class="w-10 h-10 rounded-full shadow-sm border-2 border-white" />
                </div>
            </div>
        </header>

        <!-- Dynamic Content Area -->
        <div class="flex-1 overflow-auto p-4 md:p-10">
            @if(session('success'))
                <div x-data="{ show: true }" 
                     x-show="show" 
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="mb-6 p-4 bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-sm animate-in fade-in slide-in-from-top-4 duration-300">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span class="text-sm font-bold">{!! session('success') !!}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-900 transition">
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
                     class="mb-6 p-4 bg-rose-100 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-sm animate-in fade-in slide-in-from-top-4 duration-300">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-sm font-bold">{!! session('error') !!}</span>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-900 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif

            @if(session('warning'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 7000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="mb-6 p-4 bg-amber-100 border border-amber-200 text-amber-800 rounded-2xl flex items-center justify-between shadow-sm animate-in fade-in slide-in-from-top-4 duration-300">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z"></path></svg>
                        <span class="text-sm font-bold">{!! session('warning') !!}</span>
                    </div>
                    <button @click="show = false" class="text-amber-500 hover:text-amber-900 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

</body>
</html>
