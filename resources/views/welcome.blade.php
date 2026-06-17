<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MagixTouch — Tekli Ürün Satış Platformu</title>
    <meta name="description" content="MagixTouch, her ürün için özel tekli satış sitesi oluşturan modern satış platformu.">
    <link rel="icon" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#0b1020; --bg2:#111936; --card:rgba(255,255,255,.04);
            --border:rgba(255,255,255,.10); --text:#eef2ff; --muted:#9aa6c7;
            --primary:#6d5efc; --primary2:#a855f7; --accent:#22d3ee;
        }
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Inter',sans-serif}
        html{scroll-behavior:smooth}
        body{background:radial-gradient(1200px 600px at 70% -10%,#23306b 0%,transparent 60%),
              radial-gradient(900px 500px at 0% 100%,#3b1d63 0%,transparent 55%),var(--bg);
              color:var(--text);line-height:1.6;min-height:100vh}
        a{text-decoration:none;color:inherit}
        .wrap{max-width:1080px;margin:0 auto;padding:0 22px}
        header{display:flex;align-items:center;justify-content:space-between;padding:22px 0}
        .logo{font-weight:900;font-size:1.35rem;letter-spacing:-.5px;display:flex;align-items:center;gap:10px}
        .logo .dot{width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,var(--primary),var(--primary2));
              display:grid;place-items:center;font-size:1rem;box-shadow:0 8px 24px rgba(109,94,252,.5)}
        .btn{display:inline-flex;align-items:center;gap:9px;padding:12px 22px;border-radius:12px;font-weight:700;
              font-size:.95rem;transition:.25s;border:1px solid transparent;cursor:pointer}
        .btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;
              box-shadow:0 10px 30px rgba(109,94,252,.45)}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 16px 40px rgba(109,94,252,.6)}
        .btn-ghost{background:var(--card);border-color:var(--border);color:var(--text)}
        .btn-ghost:hover{border-color:var(--primary);color:#fff}
        .hero{text-align:center;padding:70px 0 50px}
        .badge{display:inline-flex;align-items:center;gap:8px;padding:7px 15px;border-radius:999px;
              background:var(--card);border:1px solid var(--border);font-size:.82rem;color:var(--muted);margin-bottom:26px}
        .badge .pulse{width:8px;height:8px;border-radius:50%;background:var(--accent);box-shadow:0 0 0 0 rgba(34,211,238,.7);
              animation:pulse 2s infinite}
        @keyframes pulse{0%{box-shadow:0 0 0 0 rgba(34,211,238,.6)}70%{box-shadow:0 0 0 12px rgba(34,211,238,0)}100%{box-shadow:0 0 0 0 rgba(34,211,238,0)}}
        h1{font-size:clamp(2.2rem,6vw,4rem);font-weight:900;letter-spacing:-1.5px;line-height:1.05;margin-bottom:20px}
        h1 .grad{background:linear-gradient(135deg,var(--accent),var(--primary2));-webkit-background-clip:text;
              background-clip:text;-webkit-text-fill-color:transparent}
        .sub{font-size:clamp(1rem,2.4vw,1.22rem);color:var(--muted);max-width:640px;margin:0 auto 36px}
        .cta{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;padding:30px 0 70px}
        .feat{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:26px;transition:.25s}
        .feat:hover{transform:translateY(-4px);border-color:rgba(168,85,247,.5)}
        .feat .ic{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;font-size:1.3rem;
              background:linear-gradient(135deg,rgba(109,94,252,.25),rgba(168,85,247,.25));margin-bottom:16px}
        .feat h3{font-size:1.08rem;margin-bottom:8px}
        .feat p{color:var(--muted);font-size:.94rem}
        footer{border-top:1px solid var(--border);padding:26px 0;text-align:center;color:var(--muted);font-size:.88rem}
        @media(max-width:560px){header{flex-wrap:wrap;gap:14px}.hero{padding:44px 0 30px}}
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <div class="logo"><span class="dot">✦</span> MagixTouch</div>
            <a href="{{ route('login') }}" class="btn btn-ghost">🔐 Yönetim Girişi</a>
        </header>

        <section class="hero">
            <span class="badge"><span class="pulse"></span> Tekli Ürün Satış Platformu</span>
            <h1>Her ürün için<br><span class="grad">özel satış sitesi.</span></h1>
            <p class="sub">MagixTouch ile her ürününüze ait yüksek dönüşümlü tekli satış sayfalarını tek panelden
               yönetin. Sipariş, kargo ve ödeme akışlarınız tek yerde.</p>
            <div class="cta">
                <a href="{{ route('login') }}" class="btn btn-primary">Panele Giriş Yap →</a>
                <a href="#ozellikler" class="btn btn-ghost">Özellikler</a>
            </div>
        </section>

        <section class="grid" id="ozellikler">
            <div class="feat"><div class="ic">🛒</div><h3>Tekli Satış Sayfaları</h3>
                <p>Her ürün için ayrı alan adı ve optimize edilmiş satış hunisi.</p></div>
            <div class="feat"><div class="ic">📦</div><h3>Sipariş & Kargo</h3>
                <p>Siparişleri tek ekrandan yönetin, kargo entegrasyonlarıyla hızlanın.</p></div>
            <div class="feat"><div class="ic">💳</div><h3>Esnek Ödeme</h3>
                <p>Kapıda ödeme, havale ve kredi kartı sağlayıcılarını destekler.</p></div>
            <div class="feat"><div class="ic">📊</div><h3>Tek Panel Yönetim</h3>
                <p>Tüm ürün siteleriniz, ziyaretçi ve dönüşüm verileriniz tek yerde.</p></div>
        </section>
    </div>

    <footer>
        <div class="wrap">© {{ date('Y') }} MagixTouch · Tüm hakları saklıdır ·
            <a href="{{ route('login') }}" style="color:var(--accent)">Yönetim Paneli</a>
        </div>
    </footer>
</body>
</html>
