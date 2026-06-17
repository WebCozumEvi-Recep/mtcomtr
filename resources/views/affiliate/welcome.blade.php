<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} Satış Ortaklığı - Kazancınızı Katlayın</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0b0f19;
            --bg-card: rgba(17, 24, 39, 0.7);
            --primary: #ff7a00;
            --primary-glow: rgba(255, 122, 0, 0.15);
            --primary-light: #ff9d42;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --border: rgba(255, 255, 255, 0.08);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 122, 0, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(255, 122, 0, 0.05) 0%, transparent 40%);
            background-attachment: fixed;
        }

        h1, h2, h3, h4 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }

        /* UTILS */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #ff5100 100%);
            color: white;
            box-shadow: 0 8px 20px -6px rgba(255, 122, 0, 0.5);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -4px rgba(255, 122, 0, 0.7);
            filter: brightness(1.1);
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid var(--border);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .section-tag {
            display: inline-block;
            background-color: rgba(255, 122, 0, 0.1);
            color: var(--primary);
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 122, 0, 0.2);
        }

        /* HEADER */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background-color: rgba(11, 15, 25, 0.7);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            transition: var(--transition);
        }

        header.scrolled {
            padding: 12px 0;
            background-color: rgba(11, 15, 25, 0.9);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .logo img {
            height: 36px;
            display: block;
        }

        .header-actions {
            display: flex;
            gap: 16px;
        }

        /* HERO */
        .hero {
            padding: 180px 0 100px 0;
            text-align: center;
            position: relative;
        }

        .hero h1 {
            font-size: 56px;
            line-height: 1.2;
            margin-bottom: 24px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero h1 span {
            background: linear-gradient(135deg, var(--primary) 0%, #ff8800 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 18px;
            color: var(--text-muted);
            max-width: 650px;
            margin: 0 auto 40px auto;
        }

        .hero-btns {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        /* GLASS CARD COMMON */
        .glass-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            backdrop-filter: blur(16px);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            transition: var(--transition);
        }

        .glass-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 122, 0, 0.2);
            box-shadow: 0 12px 40px 0 rgba(255, 122, 0, 0.08);
        }

        /* STATS SECTION */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 80px;
        }

        .stat-card {
            text-align: center;
        }

        .stat-card i {
            font-size: 32px;
            color: var(--primary);
            margin-bottom: 16px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 8px;
            font-family: 'Outfit', sans-serif;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* STEPS SECTION */
        .steps-section {
            padding: 100px 0;
            position: relative;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 40px;
            margin-bottom: 16px;
        }

        .section-header p {
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
        }

        .step-card {
            position: relative;
            overflow: hidden;
        }

        .step-number {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 64px;
            font-weight: 900;
            color: rgba(255, 122, 0, 0.08);
            line-height: 1;
            font-family: 'Outfit', sans-serif;
        }

        .step-card i {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 24px;
        }

        .step-card h3 {
            font-size: 22px;
            margin-bottom: 16px;
        }

        .step-card p {
            color: var(--text-muted);
            font-size: 15px;
        }

        /* CALCULATOR SECTION */
        .calc-section {
            padding: 100px 0;
            background: radial-gradient(circle at 50% 50%, var(--primary-glow) 0%, transparent 60%);
        }

        .calc-container {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 40px;
            align-items: center;
        }

        .calc-controls {
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        .slider-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .slider-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .slider-header label {
            font-weight: 600;
            font-size: 16px;
            color: #ffffff;
        }

        .slider-header .value-badge {
            background-color: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
        }

        .custom-slider {
            -webkit-appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.1);
            outline: none;
            transition: var(--transition);
        }

        .custom-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary);
            cursor: pointer;
            box-shadow: 0 0 10px var(--primary);
            transition: var(--transition);
        }

        .custom-slider::-webkit-slider-thumb:hover {
            transform: scale(1.2);
        }

        .preset-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 8px;
        }

        .preset-btn {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            padding: 10px;
            border-radius: 10px;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: var(--transition);
            text-align: center;
        }

        .preset-btn.active, .preset-btn:hover {
            background: var(--primary-glow);
            color: var(--primary);
            border-color: var(--primary);
        }

        .calc-result {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(135deg, rgba(255, 122, 0, 0.1) 0%, rgba(255, 81, 0, 0.05) 100%);
            border: 1px solid rgba(255, 122, 0, 0.2);
            padding: 48px;
            border-radius: 24px;
        }

        .result-title {
            color: var(--text-muted);
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .result-value {
            font-size: 54px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 8px;
            font-family: 'Outfit', sans-serif;
            text-shadow: 0 0 20px rgba(255, 122, 0, 0.3);
        }

        .result-subtext {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 32px;
        }

        /* FAQ SECTION */
        .faq-section {
            padding: 100px 0;
        }

        .faq-list {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .faq-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            transition: var(--transition);
        }

        .faq-item.active {
            border-color: rgba(255, 122, 0, 0.3);
            background: rgba(255, 255, 255, 0.04);
        }

        .faq-question {
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 600;
            font-size: 17px;
            user-select: none;
        }

        .faq-question i {
            color: var(--primary);
            transition: var(--transition);
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out;
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.7;
        }

        .faq-answer-content {
            padding: 0 24px 24px 24px;
        }

        /* CTA SECTION */
        .cta-section {
            padding: 120px 0;
            text-align: center;
            background: linear-gradient(180deg, transparent 0%, rgba(255, 122, 0, 0.05) 100%);
        }

        .cta-card {
            background: radial-gradient(circle at top right, rgba(255, 122, 0, 0.15) 0%, transparent 60%);
            border: 1px solid rgba(255, 122, 0, 0.2);
            padding: 64px;
            border-radius: 32px;
            max-width: 900px;
            margin: 0 auto;
        }

        .cta-card h2 {
            font-size: 44px;
            margin-bottom: 20px;
        }

        .cta-card p {
            color: var(--text-muted);
            font-size: 17px;
            max-width: 600px;
            margin: 0 auto 40px auto;
        }

        /* FOOTER */
        footer {
            padding: 60px 0;
            border-top: 1px solid var(--border);
            background-color: #070a11;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .copyright {
            color: var(--text-muted);
            font-size: 14px;
        }

        .footer-links {
            display: flex;
            gap: 24px;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .hero h1 { font-size: 44px; }
            .calc-container { grid-template-columns: 1fr; gap: 40px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .steps-grid { grid-template-columns: 1fr; gap: 24px; }
        }

        @media (max-width: 768px) {
            .hero h1 { font-size: 32px; }
            .hero p { font-size: 16px; }
            .hero-btns { flex-direction: column; padding: 0 20px; }
            .stats-grid { grid-template-columns: 1fr; }
            .cta-card { padding: 40px 20px; }
            .cta-card h2 { font-size: 32px; }
            .footer-content { flex-direction: column; gap: 24px; text-align: center; }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header id="header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    <img src="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_logo')) }}" alt="Logo" style="filter: brightness(0) invert(1); max-height: 38px; object-fit: contain;">
                </a>
                <div class="header-actions">
                    <a href="{{ route('affiliate.login') }}" class="btn btn-secondary"><i class="fa-solid fa-right-to-bracket"></i> Giriş Yap</a>
                    <a href="{{ route('affiliate.register') }}" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Başvur</a>
                </div>
            </div>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <span class="section-tag">{{ strtoupper(config('app.name')) }} SATIŞ ORTAKLIĞI</span>
            <h1>Linkini Paylaş, <span>Yüksek Komisyon</span> Oranlarıyla Kazanmaya Başla!</h1>
            <p>{{ config('app.name') }} bünyesindeki yüzlerce yüksek satış hacimli ve popüler ürünü tanıtın. Her başarılı satıştan anında, zahmetsizce hak edişlerinizi alın.</p>
            <div class="hero-btns">
                <a href="{{ route('affiliate.register') }}" class="btn btn-primary"><i class="fa-solid fa-rocket"></i> Hemen Ortak Ol</a>
                <a href="#kazanc" class="btn btn-secondary"><i class="fa-solid fa-calculator"></i> Kazanç Hesapla</a>
            </div>
        </div>
    </section>

    <!-- STATS -->
    <div class="container">
        <div class="stats-grid">
            <div class="glass-card stat-card">
                <i class="fa-solid fa-percent"></i>
                <div class="stat-number">%25+</div>
                <div class="stat-label">Yüksek Komisyon Oranı</div>
            </div>
            <div class="glass-card stat-card">
                <i class="fa-solid fa-circle-check"></i>
                <div class="stat-number">48 Saatte</div>
                <div class="stat-label">Hızlı Onay & Ödeme</div>
            </div>
            <div class="glass-card stat-card">
                <i class="fa-solid fa-cookie-bite"></i>
                <div class="stat-number">30 Gün</div>
                <div class="stat-label">Çerez (Cookie) Ömrü</div>
            </div>
            <div class="glass-card stat-card">
                <i class="fa-solid fa-chart-line"></i>
                <div class="stat-number">100%</div>
                <div class="stat-label">Şeffaf Anlık Takip</div>
            </div>
        </div>
    </div>

    <!-- HOW IT WORKS -->
    <section class="steps-section" id="nasil-calisir">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">SÜREÇ NASIL İŞLER?</span>
                <h2>3 Kolay Adımda Kazanmaya Başlayın</h2>
                <p>Karmaşık teknik süreçler yok. Sadece birkaç dakikada ortaklık profilinizi kurun ve tanıtmaya başlayın.</p>
            </div>
            
            <div class="steps-grid">
                <div class="glass-card step-card">
                    <div class="step-number">01</div>
                    <i class="fa-solid fa-user-plus"></i>
                    <h3>Başvurunu Yap</h3>
                    <p>Satış ortaklığı formunu eksiksiz doldurun. İnceleme sürecimizin ardından hesabınız aktif edilecek ve panele erişim sağlayacaksınız.</p>
                </div>
                <div class="glass-card step-card">
                    <div class="step-number">02</div>
                    <i class="fa-solid fa-link"></i>
                    <h3>Özel Linkini Üret</h3>
                    <p>Paneli kullanarak satmak istediğiniz ürünlere veya web sitelerine ait size özel yönlendirme linklerini ve reklam materyallerini anında oluşturun.</p>
                </div>
                <div class="glass-card step-card">
                    <div class="step-number">03</div>
                    <i class="fa-solid fa-money-bill-trend-up"></i>
                    <h3>Paylaş & Kazan</h3>
                    <p>Linkinizi sosyal medya, blog veya web sitelerinizde paylaşın. Gelen siparişlerden elde ettiğiniz komisyonları panelinizden anlık izleyin.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CALCULATOR -->
    <section class="calc-section" id="kazanc">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">KAZANÇ POTANSİYELİ</span>
                <h2>Ne Kadar Kazanabilirsiniz?</h2>
                <p>Hedeflerinize göre tahmini aylık kazancınızı belirlemek için aşağıdaki kaydırıcıları kullanın.</p>
            </div>

            <div class="glass-card calc-container">
                <div class="calc-controls">
                    <!-- Preset Packages -->
                    <div class="slider-group">
                        <label style="font-weight: 600; font-size: 15px; color: #ffffff; margin-bottom: 8px;">Popüler Ürün Paket Seçimi</label>
                        <div class="preset-buttons">
                            <button type="button" class="preset-btn active" data-commission="150" data-sales="10">Nippon Şampuan (150 TL Komisyon)</button>
                            <button type="button" class="preset-btn" data-commission="200" data-sales="5">Dr. Clinic Seti (200 TL Komisyon)</button>
                            <button type="button" class="preset-btn" data-commission="350" data-sales="3">Premium Mega Paket (350 TL Komisyon)</button>
                        </div>
                    </div>

                    <!-- Daily Sales Slider -->
                    <div class="slider-group">
                        <div class="slider-header">
                            <label for="daily-sales">Günlük Ortalama Satış Adedi</label>
                            <span class="value-badge" id="sales-badge">10 Adet</span>
                        </div>
                        <input type="range" id="daily-sales" class="custom-slider" min="1" max="100" value="10">
                    </div>

                    <!-- Commission Slider -->
                    <div class="slider-group">
                        <div class="slider-header">
                            <label for="package-commission">Paket Başına Komisyon Tutarı</label>
                            <span class="value-badge" id="commission-badge">150 TL</span>
                        </div>
                        <input type="range" id="package-commission" class="custom-slider" min="50" max="1000" step="10" value="150">
                    </div>
                </div>

                <div class="calc-result">
                    <span class="result-title">Aylık Tahmini Kazanç</span>
                    <div class="result-value" id="calc-monthly">45.000 TL</div>
                    <div class="result-subtext" id="calc-yearly">Yıllık Kazanç Potansiyeli: 540.000 TL</div>
                    <a href="{{ route('affiliate.register') }}" class="btn btn-primary" style="width: 100%;"><i class="fa-solid fa-wallet"></i> Bu Kazanca Hemen Ulaş</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq-section" id="sss">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">SÖZLEŞME & SSS</span>
                <h2>Sıkça Sorulan Sorular</h2>
                <p>{{ config('app.name') }} Satış Ortaklığı programı hakkında merak ettiğiniz tüm temel soruların cevapları.</p>
            </div>

            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Satış ortaklığı programına kimler katılabilir?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Sosyal medya hesaplarında takipçisi olan influencerlar, web site sahipleri, içerik üreticileri veya e-ticaret alanında satış tecrübesi olan herkes programa başvurabilir. Başvurular ekibimiz tarafından incelendikten sonra hızlıca onaylanır.
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Ödemelerimi ne zaman ve nasıl alırım?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Onaylanan komisyonlarınız için dilediğiniz zaman paneliniz üzerinden ödeme talebi oluşturabilirsiniz. Minimum ödeme limitine ulaştığınızda talebiniz incelenir ve 48 saat içerisinde kayıtlı IBAN adresinize havale/EFT olarak aktarılır.
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Çerez (Cookie) ömrü nedir ve nasıl çalışır?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Linkinize tıklayan bir kullanıcı o anda satın alım yapmasa bile, 30 gün boyunca sistem tarafından hafızada tutulur. 30 gün içerisinde yapacağı tüm başarılı satın alımlarda komisyon sizin hesabınıza kaydedilir.
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Satış yapabilmek için teknik bilgiye ihtiyacım var mı?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Hayır, kesinlikle gerek yok! Panelimiz son derece sade ve kullanıcı dostudur. Size özel hazırlanan hazır banner, video ve görselleri indirip doğrudan sosyal medya kanallarınızda veya sitelerinizde paylaşarak hemen satış yapmaya başlayabilirsiniz.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card">
                <h2>Hemen Ekibimize Katılın</h2>
                <p>Türkiye'nin en hızlı büyüyen tekli ürün satış sitelerine yönlendirme yaparak, yüksek oranlı pasif gelir akışı oluşturun.</p>
                <div class="hero-btns" style="margin-bottom: 0;">
                    <a href="{{ route('affiliate.register') }}" class="btn btn-primary btn-lg"><i class="fa-solid fa-user-plus"></i> Ücretsiz Kayıt Ol</a>
                    <a href="{{ route('affiliate.login') }}" class="btn btn-secondary btn-lg"><i class="fa-solid fa-sign-in"></i> Giriş Yap</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <a href="/" class="logo">
                    <img src="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::val('site_logo')) }}" alt="Logo" style="filter: brightness(0) invert(1); height: 30px; object-fit: contain;">
                </a>
                <div class="footer-links">
                    <a href="#nasil-calisir">Nasıl Çalışır?</a>
                    <a href="#kazanc">Kazanç Hesapla</a>
                    <a href="#sss">Sıkça Sorulan Sorular</a>
                </div>
                <div class="copyright">© {{ date('Y') }} {{ config('app.name') }} - Akıllı Tekli Ürün Satış Altyapısı.</div>
            </div>
        </div>
    </footer>

    <!-- CALCULATOR & FAQ SCRIPTS -->
    <script>
        // Header Scroll Effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // FAQ Accordion
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            
            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                
                // Close all other items
                faqItems.forEach(otherItem => {
                    otherItem.classList.remove('active');
                    otherItem.querySelector('.faq-answer').style.maxHeight = null;
                });
                
                if (!isActive) {
                    item.classList.add('active');
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                }
            });
        });

        // Calculator Logic
        const dailySalesSlider = document.getElementById('daily-sales');
        const packageCommissionSlider = document.getElementById('package-commission');
        const salesBadge = document.getElementById('sales-badge');
        const commissionBadge = document.getElementById('commission-badge');
        const calcMonthly = document.getElementById('calc-monthly');
        const calcYearly = document.getElementById('calc-yearly');
        const presetBtns = document.querySelectorAll('.preset-btn');

        function formatCurrency(val) {
            return new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(val) + ' TL';
        }

        function calculate() {
            const sales = parseInt(dailySalesSlider.value);
            const commission = parseInt(packageCommissionSlider.value);
            
            const monthly = sales * commission * 30;
            const yearly = monthly * 12;

            salesBadge.innerText = sales + ' Adet';
            commissionBadge.innerText = commission + ' TL';
            calcMonthly.innerText = formatCurrency(monthly);
            calcYearly.innerText = 'Yıllık Kazanç Potansiyeli: ' + formatCurrency(yearly);
        }

        dailySalesSlider.addEventListener('input', () => {
            // Remove active state from preset buttons if user slides manually
            presetBtns.forEach(btn => btn.classList.remove('active'));
            calculate();
        });

        packageCommissionSlider.addEventListener('input', () => {
            presetBtns.forEach(btn => btn.classList.remove('active'));
            calculate();
        });

        presetBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                presetBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const commission = btn.getAttribute('data-commission');
                const sales = btn.getAttribute('data-sales');

                dailySalesSlider.value = sales;
                packageCommissionSlider.value = commission;

                calculate();
            });
        });

        // Initial Calculation
        calculate();
    </script>
</body>
</html>
