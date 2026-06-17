# Teksat — Harici sipariş entegrasyonu (firma dokümanı)

Bu doküman, siparişlerini Teksat paneline aktaracak **iş ortağı teknik ekibi** içindir. Üretim adresi ve API anahtarı Teksat tarafından ayrıca iletilir.

---

## 1. Genel

| Öğe | Açıklama |
|------|----------|
| **Base URL** | `{TEKSAT_BASE}/api` — örnek: `https://panel.teksat.com.tr/api` (Teksat’ın verdiği kök + `/api`). **Alternatif (302 alıyorsanız):** kök + `/api` olmadan `https://PANEL/api` yerine `https://PANEL` kullanıp aşağıdaki **Sürüm yolu** ile birleştirin; uçlar aynı şekilde `/v1/integration/...` altında da yayınlanır. |
| **İçerik tipi** | `Content-Type: application/json` |
| **Kodlama** | UTF-8 |
| **Sürüm yolu** | `/v1/integration/...` |

### 1.1 302 yönlendirme — neden JSON yerine `teksat.com.tr` HTML’i görüyorum?

Bu **Laravel’in entegrasyon cevabı değildir**; istek büyük olasılıkla **API’ye hiç düşmeden** önce sunucu / CDN katmanında başka bir adrese yönlendiriliyor.

- **302 / 301** yanıtında `Location` başlığı genelde **ana site kökü**ne (`https://teksat.com.tr/` gibi) işaret eder.
- **Postman**, tarayıcı veya bazı HTTP istemcileri yönlendirmeyi **otomatik takip** eder; son adımda **200** ve **HTML** (tanıtım / welcome sayfası) döner; `http_code` 200 görünür ama gövde JSON değildir.
- **Doğru taban adres:** Teksat’ın verdiği **panel / uygulama kökü** + `/api` (ör. `https://PANEL-ALT-ALAN.teksat.com.tr/api`). Kurumsal kök domain (`teksat.com.tr`) çoğu ortamda yalnızca vitrin sitesidir; `/api/v1/integration/...` burada yoksa veya CDN kuralı `/api` isteğini köke atıyorsa 302 alırsınız.

**Ne yapmalı:** Teksat’tan net **API host** alın; `curl -v` ile ilk yanıt kodunu ve `Location`’ı kontrol edin ( `-L` kullanmayın; yönlendirmeyi gizler). Postman’da **“Automatically follow redirects”** kapatıp 302’yi doğrudan görün. PHP örnek istemci `docs/integrations/TeksatIntegrationClient.php` yanıtta **`error_message`** (açıklama) ve **`location`** (hedef URL) doldurur; hata ayıklamada: `echo $r['error_message'], PHP_EOL, $r['location'];`

---

## 2. Teksat’ın sizin tarafınızda yapılandırması

Teksat sunucusunda sizin çağrı atacağınız **host(lar)** allowlist’e eklenir. Bu liste **sizin backend’inizin hostname’i**dir (müşteri sitesi `domain_name` değil).

Örnek allowlist değerleri: `drclinic.com.tr`, `www.drclinic.com.tr`

İstek doğrulamasında şu sıra kullanılır:

1. HTTP başlığı **`X-Teksat-Source-Host`** (önerilen; sunucudan `curl` vb. için)
2. Yoksa **`Origin`** veya **`Referer`** içindeki host (tarayıcı / bazı istemciler)

Host, allowlist’teki bir kayıtla **www / kök (apex)** eşlemesiyle uyumlu olmalıdır (`www.ornek.com` ↔ `ornek.com`).

### 2.1 `X-Teksat-Source-Host` (örnekteki `callerHost`) ne?

İstek Teksat’ın API URL’sine gittiği için tarayıcı/sunucu otomatik olarak gönderdiği **`Host`** başlığı **Teksat’ın makinesidir**; müşteri siteniz veya sizin backend’iniz orada görünmez.

Bu yüzden Teksat’a şunu söylemeniz gerekir: **“Bu isteği benim şu sunucum atıyor.”** Bunun için `X-Teksat-Source-Host` başlığına **sizin entegrasyonu çalıştırdığınız sunucunun alan adını** yazarsınız. Teksat tarafında bu ad, `TEKSAT_INTEGRATION_ALLOWED_HOSTS` içinde **önceden tanımlanmış** olmalıdır.

Örnek kod satırındaki `api.sizin-sirketiniz.com` **sadece örnektir**; gerçek değer Teksat ile birlikte belirlenir (ör. `backend.sirketiniz.com`, `hooks.drclinic.com.tr` gibi).

---

## 3. Kimlik doğrulama

### 3.1 API anahtarı (`api_key`)

Teksat size tek bir **API anahtarı** verir. Bu anahtar:

- **Domain listesi** isteğinde: `Authorization: Bearer {api_key}`
- **Sipariş gönderimi** isteğinde: aşağıdaki **HMAC imzası** için gizli anahtar olarak kullanılır (Bearer zorunlu değildir; imza yeterlidir).

Anahtarı URL’de, log’da veya istemci tarafında saklamayın; yalnızca sunucu tarafında tutun.

### 3.2 İmza (yalnızca `POST /orders`)

| Başlık | Zorunlu | Açıklama |
|--------|---------|----------|
| `X-Teksat-Timestamp` | Evet | Unix zamanı (**saniye**), tam sayı string |
| `X-Teksat-Signature` | Evet | Küçük harf **hex** SHA-256 HMAC |

**İmza metni (byte olarak birebir aynı JSON gövde ile):**

```text
{timestamp}\n{raw_json_body}
```

**Algoritma:** `HMAC-SHA256(api_key, imza_metni)` çıktısı hex (küçük harf) olarak `X-Teksat-Signature` başlığına yazılır.

**Zaman penceresi:** Sunucu saati ile `X-Teksat-Timestamp` farkı çok büyükse istek reddedilir (replay önlemi; varsayılan tolerans Teksat tarafında yapılandırılır, tipik birkaç dakika).

---

## 4. Uç noktalar

Her uç, **iki taban** ile aynıdır: `{TEKSAT_BASE}/api/v1/integration/...` (klasik) ve `{TEKSAT_HOST}/v1/integration/...` (`/api` olmadan; bazı sunucularda `/api` isteği 302 ile köke gittiğinde bunu kullanın).

### 4.1 Domain listesi

Sipariş oluştururken kullanacağınız `domain_id` veya `api_domain_id` ile site adreslerini almak için.

```http
GET {TEKSAT_BASE}/api/v1/integration/domains
GET {TEKSAT_HOST}/v1/integration/domains
```

`{TEKSAT_HOST}` = `/api` **olmadan** panel kökü (örn. `https://panel.teksat.com.tr`). İkinci satır, sunucunun `/api` yolunu köke veya vitrine yönlendirdiği kurulumlarda kullanılır.

**Zorunlu başlıklar**

| Başlık | Değer |
|--------|--------|
| `Authorization` | `Bearer {api_key}` |
| `X-Teksat-Source-Host` | Allowlist’te tanımlı hostlardan biri (sunucu çağrıları için önerilir) |

**Örnek yanıt (200)**

```json
{
  "success": true,
  "domains": [
    { "id": 12, "domain_name": "www.ornek-funnel.com.tr", "api_domain_id": "FUNNEL-TR-01" },
    { "id": 15, "domain_name": "kampanya.baska-site.com", "api_domain_id": null }
  ]
}
```

`domain_id` (sayısal iç kimlik) veya panelde tanımlı **`api_domain_id`** (harici kimlik) sipariş ve liste isteklerinde kullanılabilir; ikisi birden gönderilirse aynı domaine işaret etmelidir.

---

### 4.2 Ürün listesi

Belirtilen funnel domainine bağlı ürünler (domain–ürün ilişkisi + paket kalemlerinde geçen ürünler, birleşik tekil liste).

```http
GET {TEKSAT_BASE}/api/v1/integration/products?domain_id={id}
GET {TEKSAT_BASE}/api/v1/integration/products?api_domain_id={harici_kimlik}
```

**Zorunlu (biri):** sorgu parametrelerinden **`domain_id`** (sayısal) veya **`api_domain_id`** (panelde tanımlı harici kimlik). İkisi birden gönderilirse aynı domaine işaret etmelidir.

**Başlıklar:** domain listesi ile aynı (`Authorization: Bearer`, `X-Teksat-Source-Host`).

**Örnek yanıt:** kökte `domain_id`, `api_domain_id`; her ürün için `id`, `api_product_id` (yoksa `null`), `name`, `price`, `sku`.

```json
{
  "success": true,
  "domain_id": 12,
  "api_domain_id": "FUNNEL-TR-01",
  "products": [
    {
      "id": 3,
      "api_product_id": "EXT-PROD-88",
      "name": "Ürün adı",
      "price": "199.90",
      "sku": "SKU-001"
    }
  ]
}
```

---

### 4.3 Paket listesi (offers)

Satılabilir paketler / teklifler ve kalemleri (`offer_items`). Yanıtta her paket için panelde tanımlı **`api_offer_id`** (varsa) döner. Siparişte **`api_offer_id`** verilirse doğrudan o paket seçilir; verilmezse aynı `api_product_id` ürününü içeren paketler arasında **`quantity`** (paketteki toplam ürün adedi) sipariş **`quantity`** ile bire bir eşleşiyorsa ve tek aday varsa paket otomatik seçilir; aynı adetten birden fazla paket varsa **`total_price`** veya **`api_offer_id`** ile ayrıştırma gerekir (bölüm 4.4).

```http
GET {TEKSAT_BASE}/api/v1/integration/offers?domain_id={id}
GET {TEKSAT_BASE}/api/v1/integration/offers?api_domain_id={harici_kimlik}
```

**Zorunlu (biri):** `domain_id` veya `api_domain_id` (ürün listesi ile aynı kural).

**Örnek yanıt (özet)**

```json
{
  "success": true,
  "domain_id": 12,
  "api_domain_id": "FUNNEL-TR-01",
  "offers": [
    {
      "id": 34,
      "api_offer_id": "PAKET-3LU",
      "offer_name": "2+1 Paket",
      "quantity": 3,
      "price": "299.00",
      "is_popular": true,
      "product_id": 3,
      "items": [
        {
          "id": 1,
          "product_id": 3,
          "quantity": 2,
          "price": "99.00",
          "product": { "id": 3, "api_product_id": "EXT-PROD-88", "name": "Ürün adı", "price": "99.00", "sku": "SKU-001" }
        }
      ]
    }
  ]
}
```

---

### 4.4 Sipariş oluşturma (ürün + COD)

Kapıda ödeme siparişi: **hangi site** için olduğu `domain_id` ve/veya panelde tanımlı **`api_domain_id`** ile belirtilir (ikisi birden gönderilebilir; aynı domaine işaret etmeli). Ürün **`api_product_id`** ile seçilir; müşteri ve adres; **birim fiyat**, **toplam tutar** ve **adet** (veya eski uyumluluk için yalnızca `price` + `quantity`); isteğe bağlı **`api_offer_id`** ve **`api_order_id`** (ortağın sipariş no’su, benzersiz); ödeme **`cod`**; isteğe bağlı **sipariş tarihi**.

```http
POST {TEKSAT_BASE}/api/v1/integration/orders
POST {TEKSAT_HOST}/v1/integration/orders
```

`{TEKSAT_HOST}` = `/api` olmadan panel kökü. PHP örnek istemci (`TeksatIntegrationClient`): `baseUrl` `.../api` ile bitiyorsa ve ilk POST **3xx** dönerse, **aynı JSON gövdesiyle** bir kez `/api` sızisiz adresi dener (yeni timestamp + imza).

**Zorunlu başlıklar**

| Başlık | Açıklama |
|--------|----------|
| `Content-Type` | `application/json` |
| `X-Teksat-Source-Host` | (veya uygun `Origin` / `Referer`) — allowlist ile uyumlu host |
| `X-Teksat-Timestamp` | Unix saniye |
| `X-Teksat-Signature` | Açıklanan HMAC (ham gövde üzerinden) |

**JSON gövde alanları** (İngilizce anahtarlar; Türkçe eşlemeler parantez içinde — ikisi birden gönderilirse dolu olan İngilizce alan önceliklidir.)

| Alan | Zorunlu | Açıklama |
|------|---------|----------|
| `domain_id` / `api_domain_id` | Evet (biri) | Hangi funnel sitesi: sayısal `domain_id` **veya** panelde tanımlı `api_domain_id`. İkisi birden gönderilirse aynı domaine işaret etmelidir. Türkçe anahtar: `harici_domain_id` → `api_domain_id` |
| `api_product_id` | Evet | Panelde ürüne yazılan harici kimlik; ürün **bu siteye** (seçilen domaine) bağlı olmalı |
| `name` | Evet | Ad soyad (`ad_soyad`) |
| `phone` | Evet | Telefon (`telefon`) |
| `city` | Evet | İl (`il`) |
| `district` | Evet | İlçe (`ilce`) |
| `address` | Evet | Açık adres (`adres`) |
| `quantity` | Evet | Satır adedi, tam sayı ≥ 1 (`adet`) |
| `unit_price` | Koşullu | **Birim fiyat** (₺). `price` ile aynı rol; ikisinden biri veya yalnızca `total_price` + `quantity` yeterlidir (`birim_fiyat`) |
| `price` | Koşullu | Eski alan adı: **birim fiyat** (`fiyat`). `unit_price` doluysa göz ardı edilir |
| `total_price` | Koşullu | **Satır toplamı** (₺). Boşsa `unit_price` (veya `price`) × `quantity` hesaplanır. İkisi birden gönderilirse ayrı ayrı kabul edilir; birim × adet ile bire bir eşit olma zorunluluğu yoktur (`toplam_fiyat`) |
| `api_offer_id` | Hayır | Panelde pakete yazılan harici paket kodu; gönderilirse doğrudan bu pakete bağlanır (`paket_kodu`, `paket_id`) |
| `api_order_id` | Hayır | Ortağın sipariş kimliği (string, en fazla 255 karakter). Gönderilirse **`orders.api_order_id`** alanına yazılır ve **aynı değer iki kez kabul edilmez** (veritabanı + doğrulama). Türkçe: `harici_siparis_id`, `siparis_kodu` |
| `payment_method` | Hayır | Gönderilirse yalnızca `cod` (kapıda ödeme). Boş bırakılırsa `cod` kabul edilir (`odeme_turu`) |
| `order_date` | Hayır | Ortağın sipariş tarihi; gönderilirse Teksat’ta **`orders.created_at` ve `updated_at`** (ve ilgili `order_items` satırının zaman damgaları) bu değere çekilir (`siparis_tarihi`). Boşsa kayıt anı kullanılır. |
| `email` | Hayır | E-posta |
| `id_number` | Hayır | T.C. / kimlik no (gönderilmezse Teksat tarafında varsayılan işlenebilir) |
| `cargo_tracking_no` | Hayır | Dolu string gönderilirse: **`orders.tracking_number`**, **`cargo_firm`** = **`yurtici`**, **`status`** = **`kargoya_verildi`**. Boş / yok: durum **`yeni`**, kargo alanları boş. |

**Başarılı yanıt (201)**

```json
{
  "success": true,
  "message": "Sipariş alındı. Panelden onay verilene kadar api_approved=false.",
  "order_id": 12345,
  "internal_order_no": "TS-20260512-0123",
  "api_order_id": "EXT-ORD-7788"
}
```

Sipariş satırı `order_items` tablosuna yazılır (`unit_price`, `quantity`, `total_price`). **`orders.api_order_id`**: gönderildiyse benzersiz saklanır; tekrar gönderim **422** olur. **`orders.offer_id`**: domainde bu `api_product_id` ürününü içeren paket yoksa boş kalır. Paket varsa **`api_offer_id`** gönderildiyse o paket seçilir. Gönderilmezse önce sipariş **`quantity`** değeri, paketteki toplam ürün adedi (`GET /v1/integration/offers` içindeki `quantity`) ile eşleşen ve **tek** aday varsa o paket atanır; aynı adetten birden fazla paket varsa **`total_price`** (±0,02) ile tek aday kalmalıdır, aksi halde **422** ve `api_offer_id` istenir. Son çare olarak yalnızca tutar eşleşmesi de denenir. Teksat panelinde **API kaynaklı** işaretlenir; `api_approved` ile onay süreci yürütülür.

---

## 5. Örnek: PHP (sipariş + imza)

```php
$base = 'https://ORNEK.teksat.com/api';
$apiKey = getenv('TEKSAT_API_KEY'); // Teksat’ın verdiği api_key
// Teksat allowlist’ine yazdığınız kendi backend hostname’iniz (dokümanda 2.1):
$callerHost = 'drclinic.com.tr';

$payload = [
    'api_domain_id' => 'FUNNEL-TR-01', // alternatif: 'domain_id' => 12
    'api_product_id' => 'EXT-PROD-88',
    'name' => 'Ali Veli',
    'phone' => '05551234567',
    'city' => 'İstanbul',
    'district' => 'Kadıköy',
    'address' => 'Örnek mah. No:1',
    'unit_price' => 99.95,
    'total_price' => 199.90,
    'quantity' => 2,
    'api_offer_id' => 'PAKET-3LU',
    'api_order_id' => 'EXT-ORD-7788',
    'payment_method' => 'cod',
    'order_date' => '2026-05-12 10:00:00',
    'email' => 'musteri@ornek.com',
    // İsteğe bağlı: dolu cargo_tracking_no -> tracking_number + yurtici + kargoya_verildi
    // 'cargo_tracking_no' => 'YT123456789',
];
$body = json_encode($payload, JSON_UNESCAPED_UNICODE);
$ts = (string) time();
$sig = hash_hmac('sha256', $ts . "\n" . $body, $apiKey);

$ch = curl_init($base . '/v1/integration/orders');
curl_setopt_array($ch, array(
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'X-Teksat-Source-Host: ' . $callerHost,
        'X-Teksat-Timestamp: ' . $ts,
        'X-Teksat-Signature: ' . $sig,
    ),
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_RETURNTRANSFER => true,
));
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
```

**Önemli:** `json_encode` çıktısı (boşluk, anahtar sırası, Unicode) imzada kullanılan gövde ile **aynı** olmalıdır; ara adımda gövdeyi yeniden serialize etmeyin.

---

## 5.1 PHP 5.6 uyumlu örnek (ek)

Aşağıdaki kod **PHP 5.6+** ile uyumludur: `array()`, tek tek `curl_setopt`, `getenv` kontrolü; `??` ve benzeri PHP 7+ sözdizimi yoktur. `json`, `hash` ve `curl` eklentileri gerekir. Üretimde SSL (`CURLOPT_SSL_VERIFYPEER` / `CURLOPT_CAINFO`) ve hata günlüğünü ortamınıza göre ayarlayın.

### Sipariş gönderimi (POST)

```php
<?php

$base = 'https://ORNEK.teksat.com/api';
$apiKey = getenv('TEKSAT_API_KEY');
if ($apiKey === false) {
    $apiKey = '';
}

// Teksat allowlist’ine yazılan kendi backend hostname’iniz (bölüm 2.1):
$callerHost = 'api.sizin-sirketiniz.com';

$payload = array(
    'api_domain_id' => 'FUNNEL-TR-01',
    'api_product_id' => 'EXT-PROD-88',
    'name' => 'Ali Veli',
    'phone' => '05551234567',
    'city' => 'İstanbul',
    'district' => 'Kadıköy',
    'address' => 'Örnek mah. No:1',
    'unit_price' => 99.95,
    'total_price' => 199.90,
    'quantity' => 2,
    'api_offer_id' => 'PAKET-3LU',
    'api_order_id' => 'EXT-ORD-7788',
    'payment_method' => 'cod',
    'order_date' => '2026-05-12 10:00:00',
    'email' => 'musteri@ornek.com',
);

$body = json_encode($payload, JSON_UNESCAPED_UNICODE);
if ($body === false) {
    die('json_encode hatasi');
}

$ts = (string) time();
$sig = hash_hmac('sha256', $ts . "\n" . $body, $apiKey);

$url = $base . '/v1/integration/orders';
$ch = curl_init($url);
if ($ch === false) {
    die('curl_init hatasi');
}

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-Teksat-Source-Host: ' . $callerHost,
    'X-Teksat-Timestamp: ' . $ts,
    'X-Teksat-Signature: ' . $sig,
));

$response = curl_exec($ch);
$httpCode = 0;
if ($response !== false) {
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
}
$curlErr = curl_error($ch);
curl_close($ch);

// $httpCode ve $response ile islem yapilir
```

### Domain listesi (GET)

```php
<?php

$base = 'https://ORNEK.teksat.com/api';
$apiKey = getenv('TEKSAT_API_KEY');
if ($apiKey === false) {
    $apiKey = '';
}
$callerHost = 'api.sizin-sirketiniz.com';

$url = $base . '/v1/integration/domains';
$ch = curl_init($url);
if ($ch === false) {
    die('curl_init hatasi');
}

curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $apiKey,
    'X-Teksat-Source-Host: ' . $callerHost,
));

$response = curl_exec($ch);
curl_close($ch);
```

---

## 6. Örnek: cURL (domain listesi)

```bash
export API_KEY='...'
export CALLER_HOST='api.sizin-sirketiniz.com'
export BASE='https://ORNEK.teksat.com/api'

curl -sS "$BASE/v1/integration/domains" \
  -H "Authorization: Bearer $API_KEY" \
  -H "X-Teksat-Source-Host: $CALLER_HOST"
```

---

## 7. Hata yanıtları (özet)

| HTTP | Tipik anlam |
|------|----------------|
| `401` | İmza veya Bearer api_key hatalı |
| `403` | Kaynak host allowlist’te değil veya telefon kara listede vb. |
| `422` | JSON / alan doğrulama; geçersiz `api_product_id` (ürün domaine bağlı değil), tarih biçimi vb. |
| `503` | Teksat sunucusunda entegrasyon yapılandırması eksik |

Gövde genelde JSON: `{ "success": false, "message": "..." }` şeklindedir.

---

## 8. Limit ve iyi uygulamalar

- İstekler **dakika başına** makul bir sınır ile sınırlanabilir (aşırı trafik `429` ile kesilebilir).
- Aynı siparişi iki kez göndermemek için idempotency anahtarı şu an API’de yok; sizin tarafta iş kuralı veya harici id ile çiftlemeyi önlemeniz önerilir.
- Sorun giderme: önce `X-Teksat-Source-Host` ile allowlist eşleşmesini, sonra sunucu saati ve imza hex’ini kontrol edin.

---

## 9. Postman koleksiyonu

Hazır PHP istemci (PHP 5.6+, tek dosya, Composer yok): `docs/integrations/TeksatIntegrationClient.php` — `TeksatIntegrationClient` sınıfı; `getDomains`, `getProducts`, `getOffers`, `createOrder` metodları.

Postman → **Import** → şu dosyayı seçin: `docs/postman/Teksat-Entegrasyon.postman_collection.json`

Koleksiyon değişkenleri: `baseUrl` (örn. `https://panel.teksat.com.tr/api`), `apiKey`, `callerHost`, `domainId`.

**Sipariş oluştur** isteğinde **Pre-request Script**, Body (raw JSON) ile aynı baytları kullanarak `X-Teksat-Timestamp` ve `X-Teksat-Signature` üretir; gövdeyi değiştirdikten sonra tekrar **Send** edin.

---

## 10. İletişim

Endpoint kökü, **API anahtarı** ve allowlist’e eklenecek **hostname** listesi Teksat operasyon / teknik ekibiyle paylaşılır; bu dokümandaki `{TEKSAT_BASE}` ve anahtarları gerçek değerlerle değiştirin.
