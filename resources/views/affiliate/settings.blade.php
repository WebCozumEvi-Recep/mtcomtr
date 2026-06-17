@extends('layouts.affiliate')

@section('title', 'Profil & Ayarlar')

@section('content')
<div class="max-w-4xl mx-auto flex flex-col gap-8 fade-in">
    <!-- Header -->
    <div class="mb-2">
        <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight font-display">Profil & Hesap Ayarları</h2>
        <p class="text-slate-400 text-xs md:text-sm mt-1">Kişisel bilgilerinizi, IBAN numaranızı, vergilendirme detaylarınızı ve şifrenizi buradan güncelleyebilirsiniz.</p>
    </div>

    <!-- Main Settings Form -->
    <form method="POST" action="{{ route('affiliate.settings.update') }}" class="space-y-6">
        @csrf

        <!-- Kişisel Bilgiler -->
        <div class="glassmorphic-card p-6 md:p-8">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-6 font-display border-b border-white/5 pb-3">Kişisel Bilgiler</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Ad Soyad / Ortaklık Adı *</label>
                    <input type="text" name="name" id="name" required value="{{ old('name', $affiliate->name) }}"
                           class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
                </div>

                <div>
                    <label for="email" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">E-Posta Adresi *</label>
                    <input type="email" name="email" id="email" required value="{{ old('email', $affiliate->email) }}"
                           class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all">
                </div>

                <div>
                    <label for="phone" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Telefon Numarası</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $affiliate->phone) }}"
                           class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                           placeholder="0500 000 00 00">
                </div>
            </div>
        </div>

        <!-- Ödeme & Finansal Ayarlar -->
        <div class="glassmorphic-card p-6 md:p-8">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-6 font-display border-b border-white/5 pb-3">Finansal Ayarlar & IBAN</h3>
            
            <div class="space-y-6">
                <div>
                    <label for="iban" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">BANKA IBAN NUMARANIZ *</label>
                    <input type="text" name="iban" id="iban" required value="{{ old('iban', $affiliate->iban) }}"
                           class="w-full py-3 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all font-mono">
                    <p class="text-[9px] text-slate-500 mt-2 font-medium">Lütfen TR ile başlayan 26 haneli IBAN numaranızı aralarında boşluk bırakarak yazabilirsiniz.</p>
                </div>

                <div class="pt-4 border-t border-white/5">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">VERGİ TÜRÜ & FATURALANDIRMA *</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative flex flex-col p-4 rounded-xl bg-slate-950/40 border border-white/10 hover:border-teal-500/50 cursor-pointer transition group">
                            <input type="radio" name="tax_type" value="individual" {{ old('tax_type', $affiliate->tax_type) === 'individual' ? 'checked' : '' }}
                                   class="absolute top-4 right-4 w-4 h-4 text-teal-500 border-white/10 bg-slate-950/60 focus:ring-0"
                                   onchange="toggleTaxFields(this.value)">
                            <span class="text-sm font-bold text-white mb-1">Bireysel</span>
                            <span class="text-[10px] text-slate-400 leading-relaxed">%{{ \App\Models\Setting::val('affiliate_withholding_rate', 20) }} Stopaj kesintisi ile adınıza vergilendirilir. Fatura kesmenize gerek yoktur.</span>
                        </label>

                        <label class="relative flex flex-col p-4 rounded-xl bg-slate-950/40 border border-white/10 hover:border-teal-500/50 cursor-pointer transition group">
                            <input type="radio" name="tax_type" value="company" {{ old('tax_type', $affiliate->tax_type) === 'company' ? 'checked' : '' }}
                                   class="absolute top-4 right-4 w-4 h-4 text-teal-500 border-white/10 bg-slate-950/60 focus:ring-0"
                                   onchange="toggleTaxFields(this.value)">
                            <span class="text-sm font-bold text-white mb-1">Kurumsal (Şirket)</span>
                            <span class="text-[10px] text-slate-400 leading-relaxed">Ödemelerde net hak ediş tutarınıza +%{{ \App\Models\Setting::val('affiliate_vat_rate', 20) }} KDV eklenerek fatura karşılığı ödenir.</span>
                        </label>

                        <label class="relative flex flex-col p-4 rounded-xl bg-slate-950/40 border border-white/10 hover:border-teal-500/50 cursor-pointer transition group">
                            <input type="radio" name="tax_type" value="none" {{ old('tax_type', $affiliate->tax_type) === 'none' ? 'checked' : '' }}
                                   class="absolute top-4 right-4 w-4 h-4 text-teal-500 border-white/10 bg-slate-950/60 focus:ring-0"
                                   onchange="toggleTaxFields(this.value)">
                            <span class="text-sm font-bold text-white mb-1">Muafiyetli</span>
                            <span class="text-[10px] text-slate-400 leading-relaxed">Vergi mükellefi olmayan veya muaf olan yabancı/yerli statüde ödeme.</span>
                        </label>
                    </div>
                </div>

                <!-- Dinamik Vergi ve Adres Bilgileri -->
                <div id="dynamicTaxFields" class="pt-6 border-t border-white/5 space-y-6">
                    <!-- Şirket Bilgileri (Sadece 'company' için) -->
                    <div id="companyFieldsGroup" class="space-y-6 hidden">
                        <h4 class="text-xs font-bold text-teal-400 tracking-wider font-display uppercase">ŞİRKET BİLGİLERİ</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="company_name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Şirket Ünvanı *</label>
                                <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $affiliate->company_name) }}"
                                       class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                                       placeholder="ABC Ltd. Şti.">
                            </div>

                            <div>
                                <label for="tax_office" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Vergi Dairesi *</label>
                                <input type="text" name="tax_office" id="tax_office" value="{{ old('tax_office', $affiliate->tax_office) }}"
                                       class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                                       placeholder="Kadıköy V.D.">
                            </div>
                        </div>
                    </div>

                    <!-- T.C. / Vergi No & Adres Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label id="taxNumberLabel" for="tax_number" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">T.C. KİMLİK NUMARASI *</label>
                            <input type="text" name="tax_number" id="tax_number" required value="{{ old('tax_number', $affiliate->tax_number) }}"
                                   class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                                   placeholder="11111111111">
                        </div>

                        <div>
                            <label for="address" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">AÇIK ADRES *</label>
                            <textarea name="address" id="address" required rows="2"
                                      class="w-full py-2 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all min-h-[42px]"
                                      placeholder="Mahalle, Cadde, Sokak, Daire No, İlçe / İl">{{ old('address', $affiliate->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Şifre Değiştir -->
        <div class="glassmorphic-card p-6 md:p-8">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-6 font-display border-b border-white/5 pb-3">Şifre Değiştir (İsteğe Bağlı)</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Yeni Şifre</label>
                    <input type="password" name="password" id="password"
                           class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                           placeholder="••••••••">
                    <p class="text-[9px] text-slate-500 mt-2 font-medium">Değiştirmek istemiyorsanız boş bırakın. En az 6 karakter olmalıdır.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Yeni Şifre Tekrarı</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full py-2.5 px-4 bg-slate-950/60 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all"
                           placeholder="••••••••">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-2">
            <button type="submit" class="py-3.5 px-10 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs tracking-wider uppercase transition-all hover:shadow-lg hover:shadow-teal-500/10">
                Ayarları Kaydet
            </button>
        </div>
    </form>
</div>
@endsection

@section('extra_js')
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
            taxNumberLabel.textContent = 'Vergi Numarası *';
            taxNumber.placeholder = '1234567890';
        } else {
            companyFieldsGroup.classList.add('hidden');
            companyName.required = false;
            taxOffice.required = false;
            taxNumberLabel.textContent = 'T.C. KİMLİK NUMARASI *';
            taxNumber.placeholder = '11111111111';
        }
    }

    // Apply initial toggle on load
    window.addEventListener('DOMContentLoaded', () => {
        const selectedRadio = document.querySelector('input[name="tax_type"]:checked');
        if (selectedRadio) {
            toggleTaxFields(selectedRadio.value);
        }
    });
</script>
@endsection
