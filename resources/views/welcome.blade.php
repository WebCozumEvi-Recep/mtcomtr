<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TekSat - Tekli Ürün Satış Altyapısı</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-light: #ffffff;
            --bg-alt: #f8fafc;
            --bg-card: #ffffff;
            --primary: #ff7a00;
            --primary-light: #ff9d42;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
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
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(255, 122, 0, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(255, 122, 0, 0.4);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--text-main);
            border: 2px solid var(--border);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            background-color: rgba(255, 122, 0, 0.05);
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
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
            color: #000;
        }

        .text-gradient {
            background: linear-gradient(135deg, #0f172a 0%, #64748b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* HEADER */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            padding: 24px 0;
            transition: var(--transition);
        }

        header.scrolled {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 12px 0;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 32px;
        }

        nav a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition);
        }

        nav a:hover {
            color: var(--primary);
        }

        /* HERO */
        .hero {
            padding-top: 180px;
            padding-bottom: 100px;
            text-align: center;
            position: relative;
            background: radial-gradient(circle at 50% 0%, rgba(255, 122, 0, 0.05) 0%, transparent 50%);
        }

        .hero h1 {
            font-size: 64px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 24px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            letter-spacing: -2px;
            color: #000;
        }

        .hero h1 span {
            color: var(--primary);
        }

        .hero p {
            font-size: 18px;
            color: var(--text-muted);
            max-width: 650px;
            margin: 0 auto 40px;
        }

        .hero-btns {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-bottom: 80px;
        }

        .hero-mockup {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            background: white;
            padding: 12px;
        }

        .hero-mockup img {
            width: 100%;
            display: block;
            border-radius: 12px;
        }

        /* PROBLEMS */
        .problems {
            padding: 100px 0;
            background-color: var(--bg-alt);
        }

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 32px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background-color: rgba(255, 122, 0, 0.1);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 20px;
            margin-bottom: 24px;
        }

        .card h3 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .card p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* SOLUTION */
        .solution {
            padding: 120px 0;
        }

        .solution-content {
            display: flex;
            align-items: center;
            gap: 80px;
        }

        .solution-text {
            flex: 1;
        }

        .solution-img {
            flex: 1;
            position: relative;
        }

        .solution-img img {
            width: 100%;
            border-radius: 32px;
            box-shadow: var(--shadow-lg);
        }

        .check-list {
            list-style: none;
            margin-top: 32px;
        }

        .check-list li {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 24px;
        }

        .check-list i {
            color: var(--primary);
            margin-top: 4px;
        }

        .check-list h4 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .check-list p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* HOW IT WORKS */
        .how-it-works {
            padding: 100px 0;
            text-align: center;
            background-color: var(--bg-alt);
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin-top: 60px;
        }

        .step {
            position: relative;
        }

        .step-number {
            font-size: 80px;
            font-weight: 900;
            color: rgba(0,0,0,0.03);
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }

        .step-icon {
            width: 64px;
            height: 64px;
            background-color: var(--primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 24px;
            position: relative;
            z-index: 2;
        }

        .step h3 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .step p {
            color: var(--text-muted);
            font-size: 15px;
        }

        /* STATS */
        .stats {
            padding: 80px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            text-align: center;
        }

        .stat-item h4 {
            font-size: 48px;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .stat-item p {
            font-size: 12px;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* CONTACT FORM */
        .contact-section {
            padding: 100px 0;
            background-color: var(--bg-alt);
        }

        .contact-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 48px;
            border-radius: 32px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            padding: 14px 20px;
            border-radius: 12px;
            border: 2px solid var(--border);
            font-size: 15px;
            transition: var(--transition);
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        /* CTA AREA */
        .final-cta {
            padding: 150px 0;
            text-align: center;
            background: radial-gradient(circle at center, rgba(255, 122, 0, 0.05) 0%, transparent 70%);
        }

        .final-cta h2 {
            font-size: 48px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 40px;
            color: #000;
        }

        /* FOOTER */
        footer {
            padding: 60px 0;
            background-color: var(--bg-alt);
            border-top: 1px solid var(--border);
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
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .hero h1 { font-size: 48px; }
            .solution-content { flex-direction: column; text-align: center; }
            .check-list li { justify-content: center; }
        }

        @media (max-width: 768px) {
            .grid-3, .stats-grid { grid-template-columns: 1fr; }
            .grid-4 { grid-template-columns: 1fr; }
            nav { display: none; }
            .hero h1 { font-size: 36px; }
            .hero-btns { flex-direction: column; }
            .footer-content { flex-direction: column; gap: 24px; text-align: center; }
            .contact-container { padding: 24px; }
        }
    </style>
</head>
<body>

    <header id="header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    <img src="/images/landing/logo_full.png" alt="TEKSAT Logo">
                </a>
                <nav>
                    <ul>
                        <li><a href="#nasil-calisir">Nasıl Çalışır?</a></li>
                        <li><a href="#ozellikler">Özellikler</a></li>
                        <li><a href="#iletisim">İletişim</a></li>
                        <li><a href="{{ route('affiliate.welcome') }}" style="color: var(--primary); font-weight: 600;"><i class="fa-solid fa-handshake" style="margin-right: 4px;"></i> Satış Ortaklığı</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary" style="padding: 10px 24px; font-size: 13px;">PANELE GİT</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline" style="padding: 10px 24px; font-size: 13px;">GİRİŞ YAP</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <span class="section-tag">Aracıları Aradan Kaldırın</span>
            <h1>Tekli Ürün Satış Modelinde Satışı <span>Sistemle</span> Yöneten Altyapı</h1>
            <p>Verimliliğinizi arttırın, satış performansınızı katlayın ve tüm operasyonunuzu tek bir merkezden, yüksek hızla yönetin.</p>
            <div class="hero-btns">
                <a href="#iletisim" class="btn btn-primary">Hemen Başla</a>
                <a href="#" class="btn btn-outline">Demo İste</a>
            </div>
            <div class="hero-mockup">
                <img src="/images/landing/dashboard_actual.jpg" alt="TekSat Dashboard">
            </div>
        </div>
    </section>

    <section class="problems">
        <div class="container text-center">
            <h2 class="text-gradient">Karşılaştığınız Sorunlar</h2>
            <p style="color: var(--text-muted); margin-bottom: 60px;">Satış süreçlerindeki tıkanıklıkları biz çözüyoruz.</p>
            
            <div class="grid-4">
                <div class="card">
                    <div class="card-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <h3>Sıfırdan Satış Sitesi</h3>
                    <p>Proje başlarında her seferinde kodlama ile uğraşmayın. Bizimle saniyeler içinde hazır olun.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-clock"></i></div>
                    <h3>Sipariş Takip Zorluğu</h3>
                    <p>Onlarca farklı panel ve Excel dosyaları arasında siparişlerinizi kaybetmeyin.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-chart-line-down"></i></div>
                    <h3>Düşük Dönüşüm Oranları</h3>
                    <p>Hatalı tasarımlar ve yavaş siteler sebebiyle müşterilerinizi kaçırmayın.</p>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-star-half-alt"></i></div>
                    <h3>Mobil Verimlilik Kaybı</h3>
                    <p>Kullanıcıların çoğunlukla mobilden geldiği bir dünyada, hantal sitelerden kurtulun.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="solution" id="ozellikler">
        <div class="container">
            <div class="solution-content">
                <div class="solution-text">
                    <span class="section-tag">Tek Çözüm</span>
                    <h2>TEKSAT ile Çözüm:<br>Tam Entegre Ekosistem</h2>
                    <p style="color: var(--text-muted);">Hızlı, ölçeklenebilir ve tam kontrollü bir satış altyapısı sunuyoruz. Her aşama sizin için optimize edilmiş bir deneyimdir.</p>
                    
                    <ul class="check-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Merkezi Stok Yönetimi</h4>
                                <p>Tüm siteleriniz için tek bir merkezden stok ve envanter yönetimi.</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Akıllı CRM Entegrasyonu</h4>
                                <p>Müşterilerinizle iletişiminizi otomatiğe bağlayın, hiçbir siparişi kaçırmayın.</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <h4>Gelişmiş Analitikler</h4>
                                <p>Ziyaretçiden siparişe kadar her adımı gerçek zamanlı verilerle takip edin.</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="solution-img">
                    <img src="/images/landing/tech-ring.png" alt="Tech Ecosystem">
                </div>
            </div>
        </div>
    </section>

    <section class="how-it-works" id="nasil-calisir">
        <div class="container">
            <span class="section-tag">Kolay Kurulum</span>
            <h2>Sistem Nasıl Çalışır?</h2>
            <p style="color: var(--text-muted);">Sadece 3 adımda satış operasyonunuzu yayına alın.</p>
            
            <div class="grid-3">
                <div class="step">
                    <div class="step-number">01</div>
                    <div class="step-icon"><i class="fas fa-box"></i></div>
                    <h3>Ürününü Belirle</h3>
                    <p>Satmak istediğin ürünü seç, paketleri gir ve kampanya detaylarını panelden saniyeler içinde oluştur.</p>
                </div>
                <div class="step">
                    <div class="step-number">02</div>
                    <div class="step-icon"><i class="fas fa-link"></i></div>
                    <h3>Trafiği Getir</h3>
                    <p>Domainini sisteme bağla, sosyal medya reklamlarını hazırla ve müşterilerini yüksek dönüşümlü sayfalara yönlendir.</p>
                </div>
                <div class="step">
                    <div class="step-number">03</div>
                    <div class="step-icon"><i class="fas fa-rocket"></i></div>
                    <h3>Panelden Takip Et</h3>
                    <p>Gelen tüm siparişleri, ödemeleri ve kargo durumlarını tek bir merkezi panelden akıllı raporlarla izle.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h4>+%40</h4>
                    <p>Dönüşüm Oranı Artışı</p>
                </div>
                <div class="stat-item">
                    <h4>100%</h4>
                    <p>Mobil Uyumluluk</p>
                </div>
                <div class="stat-item">
                    <h4>5x</h4>
                    <p>Hızlı Kurulum Süreci</p>
                </div>
                <div class="stat-item">
                    <h4>-%60</h4>
                    <p>Operasyonel Maliyet Düşüşü</p>
                </div>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="container">
            <h2>Firmanız için bu sistemi kurmak ister misiniz?</h2>
            <p style="color: var(--text-muted); margin-bottom: 40px;">Hemen iletişime geçin, satış performansınızı bugün artırmaya başlayın.</p>
            <a href="#iletisim" class="btn btn-primary" style="padding: 20px 48px; font-size: 18px;">HEMEN TANIŞIN</a>
            <div style="margin-top: 32px; color: var(--text-muted); font-size: 14px;">
                <span style="margin-right: 24px;"><i class="fas fa-phone-alt"></i> 0 216 606 06 26</span>
                <span><i class="fas fa-envelope"></i> info@teksat.com.tr</span>
            </div>
        </div>
    </section>

    <section class="contact-section" id="iletisim">
        <div class="container">
            <div class="contact-container">
                <div class="text-center" style="text-align: center; margin-bottom: 32px;">
                    <span class="section-tag">İletişime Geçin</span>
                    <h2 style="font-size: 28px;">Başvuru Formu</h2>
                    <p style="color: var(--text-muted);">Bilgilerinizi bırakın, en kısa sürede size ulaşalım.</p>
                </div>

                @if(session('success'))
                    <div style="background-color: #dcfce7; color: #166534; padding: 16px; border-radius: 12px; margin-bottom: 24px; text-align: center; font-weight: 600;">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">AD SOYAD</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Adınız ve Soyadınız" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">GSM</label>
                        <input type="tel" name="phone" id="phone" class="form-control" placeholder="05xx xxx xx xx" required>
                    </div>
                    <div class="form-group">
                        <label for="email">EPOSTA</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="eposta@adresiniz.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 12px; padding: 18px;">GÖNDER</button>
                </form>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <a href="/" class="logo">
                    <img src="/images/landing/logo_full.png" alt="TEKSAT Logo" style="height: 30px;">
                </a>
                <div class="footer-links">
                    <a href="{{ route('affiliate.welcome') }}"><i class="fa-solid fa-handshake" style="color: var(--primary); margin-right: 4px;"></i> Satış Ortaklığı</a>
                </div>
                <div class="copyright">© 2026 TekSat - Akıllı Tekli Ürün Satış Altyapısı.</div>
            </div>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
