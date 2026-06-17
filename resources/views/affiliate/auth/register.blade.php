<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} Affiliate Başvuru</title>
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
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.12) 0%, rgba(20, 184, 166, 0) 70%);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden py-12">
    <!-- Glow effects -->
    <div class="glow -top-40 -left-40 animate-pulse"></div>
    <div class="glow -bottom-40 -right-40 animate-pulse" style="animation-delay: 2s;"></div>

    <div class="w-full max-w-2xl glass rounded-[32px] p-8 md:p-10 relative z-10">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            @php 
                $siteLogo = \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_logo')); 
            @endphp
            <div class="inline-flex justify-center items-center mb-4">
                <img src="{{ $siteLogo }}" class="h-12 object-contain" style="filter: brightness(0) invert(1);" alt="Site Logo">
            </div>
            <p class="text-teal-400 text-xs font-bold uppercase tracking-widest mt-1">Affiliate Başvuru Formu</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('affiliate.register') }}" class="space-y-6" id="registerForm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">AD SOYAD / ORTAKLIK ADI *</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                           class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                           placeholder="Ahmet Yılmaz">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">E-POSTA ADRESİ *</label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}"
                           class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                           placeholder="ahmet@example.com">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">TELEFON NUMARASI</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                           class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                           placeholder="0500 000 00 00">
                </div>

                <!-- IBAN -->
                <div>
                    <label for="iban" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">ÖDEME ALACAĞINIZ IBAN *</label>
                    <input type="text" name="iban" id="iban" required value="{{ old('iban') }}"
                           class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                           placeholder="TR00 0000 0000 0000 0000 0000 00">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">ŞİFRE *</label>
                    <input type="password" name="password" id="password" required
                           class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                           placeholder="••••••••">
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">ŞİFRE TEKRARI *</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                           placeholder="••••••••">
                </div>
            </div>

            <!-- Tax Type Selection -->
            <div class="pt-4 border-t border-white/5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">VERGİ TÜRÜ & FATURALANDIRMA *</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="relative flex flex-col p-4 rounded-2xl bg-slate-950/40 border border-white/10 hover:border-teal-500/50 cursor-pointer transition group">
                        <input type="radio" name="tax_type" value="individual" checked
                               class="absolute top-4 right-4 w-4 h-4 text-teal-500 border-white/10 bg-slate-950/60 focus:ring-0"
                               onchange="toggleTaxFields(this.value)">
                        <span class="text-sm font-bold text-white mb-1">Bireysel</span>
                        <span class="text-xs text-slate-400 leading-relaxed">%{{ \App\Models\Setting::val('affiliate_withholding_rate', 20) }} Stopaj kesintisi ile adınıza vergilendirilir. Fatura kesmenize gerek yoktur.</span>
                    </label>

                    <label class="relative flex flex-col p-4 rounded-2xl bg-slate-950/40 border border-white/10 hover:border-teal-500/50 cursor-pointer transition group">
                        <input type="radio" name="tax_type" value="company"
                               class="absolute top-4 right-4 w-4 h-4 text-teal-500 border-white/10 bg-slate-950/60 focus:ring-0"
                               onchange="toggleTaxFields(this.value)">
                        <span class="text-sm font-bold text-white mb-1">Kurumsal (Şirket)</span>
                        <span class="text-xs text-slate-400 leading-relaxed">Ödemelerde net hak ediş tutarınıza +%{{ \App\Models\Setting::val('affiliate_vat_rate', 20) }} KDV eklenerek fatura karşılığı ödenir.</span>
                    </label>

                    <label class="relative flex flex-col p-4 rounded-2xl bg-slate-950/40 border border-white/10 hover:border-teal-500/50 cursor-pointer transition group">
                        <input type="radio" name="tax_type" value="none"
                               class="absolute top-4 right-4 w-4 h-4 text-teal-500 border-white/10 bg-slate-950/60 focus:ring-0"
                               onchange="toggleTaxFields(this.value)">
                        <span class="text-sm font-bold text-white mb-1">Muafiyetli</span>
                        <span class="text-xs text-slate-400 leading-relaxed">Vergi mükellefi olmayan veya muaf olan yabancı/yerli statüde ödeme.</span>
                    </label>
                </div>
            </div>

            <!-- Dinamik Vergi ve Adres Bilgileri -->
            <div id="dynamicTaxFields" class="pt-6 border-t border-white/5 space-y-6">
                <!-- Şirket Bilgileri (Sadece 'company' için) -->
                <div id="companyFieldsGroup" class="space-y-6 hidden">
                    <h4 class="text-sm font-bold text-teal-400 tracking-wide font-display uppercase">ŞİRKET BİLGİLERİ</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="company_name" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">ŞİRKET ÜNVANI *</label>
                            <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}"
                                   class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                                   placeholder="ABC Pazarlama Ltd. Şti.">
                        </div>

                        <div>
                            <label for="tax_office" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">VERGİ DAİRESİ *</label>
                            <input type="text" name="tax_office" id="tax_office" value="{{ old('tax_office') }}"
                                   class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                                   placeholder="Kadıköy V.D.">
                        </div>
                    </div>
                </div>

                <!-- T.C. / Vergi No & Adres Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label id="taxNumberLabel" for="tax_number" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">T.C. KİMLİK NUMARASI *</label>
                        <input type="text" name="tax_number" id="tax_number" required value="{{ old('tax_number') }}"
                               class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all"
                               placeholder="11111111111">
                    </div>

                    <div>
                        <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">AÇIK ADRES *</label>
                        <textarea name="address" id="address" required rows="2"
                                  class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-2xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all min-h-[46px]"
                                  placeholder="Mahalle, Cadde, Sokak, Daire No, İlçe / İl">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-400 hover:to-emerald-400 text-slate-950 font-bold text-sm tracking-wide shadow-lg shadow-teal-500/15 hover:shadow-teal-500/25 transition-all transform hover:-translate-y-0.5">
                Başvuruyu Tamamla
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-white/5 text-center">
            <p class="text-slate-400 text-sm">
                Zaten affiliate ortağımız mısınız?
                <a href="{{ route('affiliate.login') }}" class="text-teal-400 hover:text-teal-300 font-bold transition">Giriş Yapın</a>
            </p>
        </div>
    </div>

    <script>
        function toggleTaxFields(value) {
            const companyFieldsGroup = document.getElementById('companyFieldsGroup');
            const companyName = document.getElementById('company_name');
            const taxOffice = document.getElementById('tax_office');
            const taxNumber = document.getElementById('tax_number');
            const taxNumberLabel = document.getElementById('taxNumberLabel');

            if (value === 'company') {
                companyFieldsGroup.classList.remove('hidden');
                companyName.required = true;
                taxOffice.required = true;
                taxNumberLabel.textContent = 'VERGİ NUMARASI *';
                taxNumber.placeholder = '1234567890';
            } else {
                companyFieldsGroup.classList.add('hidden');
                companyName.required = false;
                taxOffice.required = false;
                taxNumberLabel.textContent = 'T.C. KİMLİK NUMARASI *';
                taxNumber.placeholder = '11111111111';
            }
        }

        // Apply initial toggle
        window.addEventListener('DOMContentLoaded', () => {
            const selectedRadio = document.querySelector('input[name="tax_type"]:checked');
            if (selectedRadio) {
                toggleTaxFields(selectedRadio.value);
            }
        });
    </script>
</body>
</html>
