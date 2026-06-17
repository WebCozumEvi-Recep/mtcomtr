<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teksat | Premium Yönetim Erişimi</title>
    <!-- Dynamic Favicon -->
    <link rel="icon" href="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_favicon'), asset('favicon.ico')) }}">
    <!-- Inter Font Collection -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
                            900: '#14532d', // Stitch Dark Forest
                            DEFAULT: '#14532d'
                        },
                        surface: '#ffffff',
                        background: '#f8fafc',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 15s ease-in-out infinite',
                        'float-delayed': 'float 15s ease-in-out infinite 5s',
                        'fade-in': 'fadeIn 0.8s ease-out forwards',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translate(0, 0) scale(1)' },
                            '50%': { transform: 'translate(-50px, 40px) scale(1.1)' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f8fafc;
            overflow: hidden;
            position: relative;
        }
        /* Glassmorphism background elements */
        .ambient-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            z-index: -1;
        }
        .shape-1 {
            width: 500px; height: 500px;
            background: linear-gradient(135deg, #dcfce7, #22c55e);
            top: -10%; left: -5%;
        }
        .shape-2 {
            width: 600px; height: 600px;
            background: linear-gradient(135deg, #e2e8f0, #f1f5f9);
            bottom: -20%; right: -10%;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(255,255,255,0.6) inset;
            border-radius: 24px;
        }
        .input-premium {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-premium:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 4px #dcfce7, inset 0 2px 4px 0 rgba(0,0,0,0.01);
            background: #ffffff;
        }
        .btn-premium {
            background: linear-gradient(135deg, #166534, #14532d);
            box-shadow: 0 4px 14px 0 rgba(22, 101, 52, 0.39);
        }
        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(22, 101, 52, 0.23);
        }
    </style>
</head>
<body class="h-screen w-full flex items-center justify-center">

    <!-- Background Orbs -->
    <div class="ambient-shape shape-1 animate-float"></div>
    <div class="ambient-shape shape-2 animate-float-delayed"></div>

    <div class="w-full max-w-[420px] px-6 relative z-10 animate-fade-in">
        
        <div class="text-center mb-10 flex flex-col items-center justify-center">
            @php $siteLogo = \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_logo')); @endphp
            @if($siteLogo)
                <img src="{{ $siteLogo }}" class="h-20 mb-4 object-contain drop-shadow" alt="Site Logo">
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-sm border border-gray-100 mb-6">
                    <svg class="w-8 h-8 text-brand-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Teksat<span class="text-brand-500">.</span></h1>
            @endif
            <p class="text-slate-500 mt-2 font-medium">Satış sistemine güvenli giriş yapın.</p>
        </div>

        <div class="glass-panel p-8">
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg text-sm font-medium shadow-sm flex items-start">
                    <svg class="w-5 h-5 mr-2 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                
                <div class="mb-5">
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">E-Posta Adresi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="input-premium w-full pl-11 pr-4 py-3 rounded-xl text-slate-800 placeholder-slate-400"
                            placeholder="admin@test.local">
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-semibold text-slate-700">Şifre</label>
                        <a href="#" class="text-xs font-semibold text-brand-600 hover:text-brand-800 transition">Şifremi Unuttum</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="input-premium w-full pl-11 pr-4 py-3 rounded-xl text-slate-800 placeholder-slate-400"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="mb-8 flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-brand-600 bg-white border-gray-300 rounded focus:ring-brand-500 cursor-pointer transition">
                    <label for="remember" class="ml-2 text-sm font-medium text-slate-600 cursor-pointer">Beni Hatırla</label>
                </div>

                <button type="submit" class="btn-premium w-full text-white font-bold py-3.5 px-4 rounded-xl transition duration-300 flex justify-center items-center group" style="color: #ffffff !important;">
                    <span style="color: #ffffff;">Panele Giriş Yap</span>
                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ffffff;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
