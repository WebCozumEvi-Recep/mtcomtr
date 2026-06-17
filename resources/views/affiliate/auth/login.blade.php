<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} Affiliate Giriş</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: radial-gradient(circle at 50% 50%, #1e293b 0%, #0f172a 100%);
            font-family: 'Inter', sans-serif;
        }
        .font-display {
            font-family: 'Outfit', sans-serif;
        }
        .glass {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }
        .glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.15) 0%, rgba(20, 184, 166, 0) 70%);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Glow effects -->
    <div class="glow -top-20 -left-20 animate-pulse"></div>
    <div class="glow -bottom-20 -right-20 animate-pulse" style="animation-delay: 2s;"></div>

    <div class="w-full max-w-md glass rounded-[32px] p-8 md:p-10 relative z-10">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            @php 
                $siteLogo = \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_logo')); 
            @endphp
            <div class="inline-flex justify-center items-center mb-4">
                <img src="{{ $siteLogo }}" class="h-12 object-contain" style="filter: brightness(0) invert(1);" alt="Site Logo">
            </div>
            <p class="text-teal-400 text-xs font-bold uppercase tracking-widest mt-1">Affiliate Giriş</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-teal-500/10 border border-teal-500/20 text-teal-300 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('affiliate.login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">E-POSTA ADRESİ</label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                       class="w-full py-3.5 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                       placeholder="isim@adres.com">
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-400">ŞİFRE</label>
                </div>
                <input type="password" name="password" id="password" required
                       class="w-full py-3.5 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                       placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer group text-slate-400 hover:text-white transition">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-white/10 bg-slate-950/60 text-teal-500 focus:ring-0 focus:ring-offset-0">
                    Beni Hatırla
                </label>
            </div>

            <button type="submit"
                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-slate-950 font-bold text-sm tracking-wide shadow-lg shadow-teal-500/15 hover:shadow-teal-500/25 transition-all transform hover:-translate-y-0.5">
                Giriş Yap
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-white/5 text-center">
            <p class="text-slate-400 text-sm">
                Henüz affiliate ortağımız değil misiniz?
                <a href="{{ route('affiliate.register') }}" class="text-teal-400 hover:text-teal-300 font-bold transition">Başvuru Yap</a>
            </p>
        </div>
    </div>
</body>
</html>
