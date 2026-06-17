<?php
/**
 * Teksat — harici sipariş entegrasyonu (PHP 5.6+)
 *
 * Tek dosya; Laravel / Composer gerekmez. Sunucuda json, curl ve hash eklentileri olmalıdır.
 *
 * Test için önerilen sıra:
 *   1) getDomains / getProductsByApiDomainId — api_product_id doğrula
 *   2) getOffersByApiDomainId — her pakette quantity (toplam ürün adedi), price, api_offer_id
 *   3) createOrder — unit_price + total_price + quantity; api_offer_id isteğe bağlı (aynı adette tek paket varsa otomatik offer_id)
 *   api_order_id gönderiyorsanız her denemede benzersiz değer kullanın (dosya sonundaki örnekte $testOrderId).
 *
 * Kullanım özeti:
 *   $c = new TeksatIntegrationClient(
 *       'https://panel.teksat.com.tr/api',
 *       'TEKSAT_INTEGRATION_API_KEY değeri',
 *       'TEKSAT_INTEGRATION_ALLOWED_HOSTS içine yazdığınız kendi hostname (örn. api.sirketiniz.com)'
 *   );
 *   $r = $c->getDomains();
 *   $r = $c->getProducts(12);
 *   $r = $c->getProductsByApiDomainId('FUNNEL-TR-01');
 *   $r = $c->getOffers(12);
 *   $r = $c->getOffersByApiDomainId('FUNNEL-TR-01');
 *   $r = $c->createOrder(array(
 *       'api_domain_id' => 'FUNNEL-TR-01',
 *       'api_product_id' => 'EXT-PROD-88',
 *       'name' => 'Ali Veli',
 *       'phone' => '05551234567',
 *       'city' => 'İstanbul',
 *       'district' => 'Kadıköy',
 *       'address' => 'Adres satırı',
 *       'unit_price' => 99.95,
 *       'total_price' => 299.85,
 *       'quantity' => 3,
 *       // api_offer_id isteğe bağlı: GET /offers ile aynı quantity için tek paket varsa otomatik offer_id
 *       'api_order_id' => 'EXT-ORD-TEST-001', // testte her istekte farklı değer kullanın (benzersiz)
 *       'payment_method' => 'cod',
 *       'order_date' => '2026-05-12 10:00:00',
 *       'email' => 'a@b.com',
 *       // cargo_tracking_no dolu ise: tracking_number + yurtici + kargoya_verildi
 *       // 'cargo_tracking_no' => 'YT...',
 *   ));
 *
 * Dönüş: her metotta array(
 *   http_code, effective_url, location|null, response_headers|null, raw (yalnizca govde),
 *   json|null, curl_error, error_message (ozellikle 3xx/4xx icin ne oldugunu aciklar)
 * )
 * Basarisizda ekrana yaz: echo $r['error_message'], PHP_EOL, $r['location'];
 *
 * Sık sorun — istek "bizim siteye" gidiyor, print_r boş/HTML:
 * - baseUrl mutlaka /api ile bitsin (örn. https://panel.teksat.com.tr/api). /api olmadan
 *   çağrı /v1/integration/... olur; Laravel bu yolu tanımaz, sunucu ana sayfaya 302/HTML dönebilir.
 * - 302 sonrası teksat.com.tr HTML’i: yönlendirme takip edildiği için kök vitrin sayfası gelmiştir; bkz. ENTEGRASYON_API_FIRMA.md §1.1
 * - 302 olmadan 200+HTML (hosgeldin/vitrin): bazi katmanlar /api/v1/... icin de ayni sayfayi dondurur; createOrder baseUrl .../api
 *   ile bitiyorsa bir kez /api'siz .../v1/integration/orders dener (retried_without_api_suffix).
 * - Kontrol: echo $r['error_message'], PHP_EOL, $r['location'], PHP_EOL, $r['http_code'];
 *   json null ve raw HTML ise URL yanlıştır veya orta katman yönlendirmesidir.
 *
 * @see docs/ENTEGRASYON_API_FIRMA.md
 */

class TeksatIntegrationClient
{
    /** @var string */
    private $baseUrl;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $callerHost;

    /** @var bool */
    private $verifySsl;

    /** @var int */
    private $timeoutSeconds;

    /** @var bool HTTP 301/302 yönlendirmelerini takip et (API testinde false önerilir; aksi halde kök site HTML’i dönebilir) */
    private $followRedirects;

    /**
     * @param string $baseUrl  Zorunlu: .../api veya kök panel URL (örn. https://HOST/api veya https://HOST).
     *   Kök kullanırsanız istek https://HOST/v1/integration/... olur (/api olmadan aynı uçlar).
     * @param string $apiKey   TEKSAT_INTEGRATION_API_KEY
     * @param string $callerHost  Allowlist’e yazılan kendi backend hostname’iniz
     * @param bool   $verifySsl   false = sadece geliştirme (güvensiz)
     * @param int    $timeoutSeconds
     * @param bool   $followRedirects true = Location ile takip (varsayılan false)
     */
    public function __construct($baseUrl, $apiKey, $callerHost, $verifySsl = true, $timeoutSeconds = 45, $followRedirects = false)
    {
        $this->baseUrl = rtrim((string) $baseUrl, '/');
        $this->apiKey = (string) $apiKey;
        $this->callerHost = (string) $callerHost;
        $this->verifySsl = (bool) $verifySsl;
        $this->timeoutSeconds = (int) $timeoutSeconds;
        $this->followRedirects = (bool) $followRedirects;
    }

    /**
     * @return array
     */
    public function getDomains()
    {
        return $this->requestGet('/v1/integration/domains');
    }

    /**
     * @param int|string $domainId
     * @return array
     */
    public function getProducts($domainId)
    {
        $q = http_build_query(array('domain_id' => $domainId), '', '&');
        return $this->requestGet('/v1/integration/products?' . $q);
    }

    /**
     * Paket / offer listesi (kampanya paketleri; funnel içi satış için).
     *
     * @param int|string $domainId
     * @return array
     */
    public function getOffers($domainId)
    {
        $q = http_build_query(array('domain_id' => $domainId), '', '&');
        return $this->requestGet('/v1/integration/offers?' . $q);
    }

    /**
     * @param string $apiDomainId panelde tanımlı api_domain_id (domain_id yerine)
     * @return array
     */
    public function getProductsByApiDomainId($apiDomainId)
    {
        $q = http_build_query(array('api_domain_id' => $apiDomainId), '', '&');
        return $this->requestGet('/v1/integration/products?' . $q);
    }

    /**
     * @param string $apiDomainId panelde tanımlı api_domain_id
     * @return array
     */
    public function getOffersByApiDomainId($apiDomainId)
    {
        $q = http_build_query(array('api_domain_id' => $apiDomainId), '', '&');
        return $this->requestGet('/v1/integration/offers?' . $q);
    }

    /**
     * Sipariş oluşturur. İmza: HMAC-SHA256(api_key, timestamp + "\n" + raw_json_body)
     *
     * @param array $order domain_id veya api_domain_id, api_product_id, name, phone, city, district, address, quantity;
     *   Fiyat: unit_price ve/veya price (birim), total_price (satır toplamı) — en az biri; ikisi birden farklı olsa da kabul edilir.
     *   Paket: api_offer_id isteğe bağlı. Gönderilmezse: aynı api_product_id içeren paketlerde sipariş quantity ile
     *   panel paket quantity eşleşen tek aday varsa otomatik atanır; aynı adetten birden fazla paket varsa total_price veya api_offer_id gerekir.
     *   Ortağın sipariş no: api_order_id (isteğe bağlı, benzersiz; tekrar gönderim reddedilir).
     *   POST bazı sunucularda /api yolunu 302 ile köke atıyorsa veya 200 ile vitrin HTML’i dönüyorsa; baseUrl .../api ile bitiyorsa istemci bir kez /api son ekini kaldırıp aynı gövdeyle yeniden dener.
     *   payment_method (cod), order_date; email, id_number isteğe bağlı.
     *   cargo_tracking_no isteğe bağlı: dolu gönderilirse tracking_number + cargo_firm yurtici + status kargoya_verildi.
     *   Türkçe: harici_domain_id → api_domain_id; ad_soyad, telefon, il, ilce, adres, fiyat, birim_fiyat, toplam_fiyat, adet, paket_kodu/paket_id → api_offer_id, harici_siparis_id/siparis_kodu → api_order_id, siparis_tarihi, odeme_turu
     * @return array
     */
    public function createOrder(array $order)
    {
        $body = json_encode($order, JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            return array(
                'http_code' => 0,
                'effective_url' => '',
                'location' => null,
                'response_headers' => null,
                'raw' => '',
                'json' => null,
                'curl_error' => 'json_encode basarisiz',
                'error_message' => 'json_encode basarisiz',
            );
        }

        $primaryUrl = $this->baseUrl . '/v1/integration/orders';
        $result = $this->postSignedOrder($primaryUrl, $body);

        $second = $this->maybeRetryCreateOrderWithoutApiPath($body, $result);
        if ($second !== null) {
            return $second;
        }

        return $result;
    }

    /**
     * Bazi CDN/nginx kurulumlarinda POST .../api/v1/integration/orders 302 yerine dogrudan 200+HTML (vitrin)
     * doner; createOrder yalnizca 3xx iken yedek denedigi icin bu durumda "hosgeldin" kaliyordu.
     * Ayni tabanda /api son ekini bir kez dene; ikinci yaniti yalnizca gercekten daha iyi ise dondur.
     *
     * @param string $body
     * @param array  $first postSignedOrder ciktisi
     * @return array|null
     */
    private function maybeRetryCreateOrderWithoutApiPath($body, array $first)
    {
        if ($this->followRedirects) {
            return null;
        }

        $code = (int) $first['http_code'];
        $raw = isset($first['raw']) ? (string) $first['raw'] : '';
        $firstLooksHtml = $raw !== '' && $this->bodyLooksLikeHtmlDocument($raw);

        $needRetry = ($code >= 300 && $code < 400)
            || ($code >= 200 && $code < 300 && $first['json'] === null && $firstLooksHtml);

        if (! $needRetry) {
            return null;
        }

        $altBase = $this->baseUrlWithoutTrailingApi($this->baseUrl);
        $orig = rtrim((string) $this->baseUrl, '/');
        if ($altBase === null || $altBase === '' || rtrim($altBase, '/') === $orig) {
            return null;
        }

        $fallbackUrl = rtrim($altBase, '/') . '/v1/integration/orders';
        $second = $this->postSignedOrder($fallbackUrl, $body);

        if ($this->createOrderSecondAttemptPreferable($first, $second)) {
            $second['retried_without_api_suffix'] = true;
            $second['first_attempt_http_code'] = $first['http_code'];
            $second['first_attempt_location'] = isset($first['location']) ? $first['location'] : null;

            return $second;
        }

        return null;
    }

    /**
     * @param string $body
     * @return bool
     */
    private function bodyLooksLikeHtmlDocument($body)
    {
        return stripos($body, '<html') !== false || stripos($body, '<!doctype') !== false;
    }

    /**
     * @param array $first
     * @param array $second
     * @return bool
     */
    private function createOrderSecondAttemptPreferable(array $first, array $second)
    {
        if ($second['json'] !== null) {
            return true;
        }

        $fc = (int) $first['http_code'];
        if ($fc < 300 || $fc >= 400) {
            return false;
        }

        $sc = (int) $second['http_code'];
        $raw2 = isset($second['raw']) ? (string) $second['raw'] : '';
        $secondHtml = $raw2 !== '' && $this->bodyLooksLikeHtmlDocument($raw2);

        return $sc < 300 && ! $secondHtml;
    }

    /**
     * @param string $url tam URL (POST /v1/integration/orders)
     * @param string $body ham JSON (imza ile ayni byte dizisi)
     * @return array
     */
    private function postSignedOrder($url, $body)
    {
        $ts = (string) time();
        $signature = hash_hmac('sha256', $ts . "\n" . $body, $this->apiKey);

        $ch = curl_init($url);
        if ($ch === false) {
            return array(
                'http_code' => 0,
                'effective_url' => '',
                'location' => null,
                'response_headers' => null,
                'raw' => '',
                'json' => null,
                'curl_error' => 'curl_init basarisiz',
                'error_message' => 'curl_init basarisiz',
            );
        }

        $headers = array(
            'Content-Type: application/json',
            'X-Teksat-Source-Host: ' . $this->callerHost,
            'X-Teksat-Timestamp: ' . $ts,
            'X-Teksat-Signature: ' . $signature,
        );

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->followRedirects);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySsl ? 2 : 0);

        $raw = curl_exec($ch);
        $err = curl_error($ch);

        return $this->finalizeCurl($ch, $raw, $err);
    }

    /**
     * .../api veya .../API ile biten taban URL icin /api on ekini kaldirır (ornek https://x.com/api -> https://x.com).
     *
     * @param string $baseUrl
     * @return string|null
     */
    private function baseUrlWithoutTrailingApi($baseUrl)
    {
        $b = rtrim((string) $baseUrl, '/');
        if ($b === '') {
            return null;
        }
        if (preg_match('#/api$#i', $b)) {
            return preg_replace('#/api$#i', '', $b);
        }

        return null;
    }

    /**
     * @param string $path query dahil, / ile başlar
     * @return array
     */
    private function requestGet($path)
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        if ($ch === false) {
            return array(
                'http_code' => 0,
                'effective_url' => '',
                'location' => null,
                'response_headers' => null,
                'raw' => '',
                'json' => null,
                'curl_error' => 'curl_init basarisiz',
                'error_message' => 'curl_init basarisiz',
            );
        }

        $headers = array(
            'Authorization: Bearer ' . $this->apiKey,
            'X-Teksat-Source-Host: ' . $this->callerHost,
        );

        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->followRedirects);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySsl ? 2 : 0);

        $raw = curl_exec($ch);
        $err = curl_error($ch);

        return $this->finalizeCurl($ch, $raw, $err);
    }

    /**
     * @param resource $ch
     * @param string|false $raw
     * @param string $curlError
     * @return array
     */
    private function finalizeCurl($ch, $raw, $curlError)
    {
        if ($raw === false) {
            $raw = '';
        }
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $curlRedirectUrl = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);

        return $this->buildResult($code, $raw, $curlError, $effectiveUrl, $headerSize, $curlRedirectUrl);
    }

    /**
     * @param int $httpCode
     * @param string $rawWithMaybeHeaders CURLOPT_HEADER true iken baslik+govde
     * @param string $curlError
     * @param string $effectiveUrl
     * @param int $headerSize
     * @param string $curlRedirectUrl
     * @return array
     */
    private function buildResult($httpCode, $raw, $curlError, $effectiveUrl = '', $headerSize = 0, $curlRedirectUrl = '')
    {
        $headersRaw = '';
        $body = $raw;
        if ($headerSize > 0 && strlen($body) >= $headerSize) {
            $headersRaw = substr($body, 0, $headerSize);
            $body = substr($body, $headerSize);
        }

        $location = null;
        if ($headersRaw !== '') {
            if (preg_match('/^Location:\s*(.+)$/mi', $headersRaw, $m)) {
                $location = trim($m[1]);
            }
        }
        if (($location === null || $location === '') && $curlRedirectUrl !== '') {
            $location = $curlRedirectUrl;
        }

        $responseHeadersOut = null;
        if ($headersRaw !== '') {
            $responseHeadersOut = strlen($headersRaw) > 12000
                ? substr($headersRaw, 0, 12000) . "\n... (kisaltildi)"
                : $headersRaw;
        }

        $json = null;
        if ($body !== '') {
            $json = json_decode($body, true);
            if (!is_array($json)) {
                $json = null;
            }
        }

        $errorMessage = '';
        if ($curlError !== '') {
            $errorMessage = 'cURL: ' . $curlError;
        } elseif ($httpCode >= 300 && $httpCode < 400) {
            $locShow = ($location !== null && $location !== '') ? $location : '(Location basligi bulunamadi)';
            $snippet = '';
            if ($body !== '') {
                $plain = strip_tags($body);
                $plain = preg_replace('/\s+/', ' ', $plain);
                $snippet = substr($plain, 0, 160);
            }
            $errorMessage = 'Sunucu HTTP ' . $httpCode . ' yonlendirme. Hedef (Location): ' . $locShow
                . '. effective_url = ilk istenen URL (curl bilgisi); gercek yonlendirme location ile basliklarda.'
                . ' Cozum: baseUrl sonunda /api varsa kaldirin (ornek https://panel + /v1/integration/...).'
                . ' Genelde sebep: yanlis host veya /api sunucuda vitrine yonleniyor.'
                . ($snippet !== '' ? (' Govde ozeti: ' . $snippet) : '');
        } elseif ($httpCode >= 400 && $json !== null && isset($json['message'])) {
            $errorMessage = (string) $json['message'];
        } elseif ($httpCode >= 400) {
            $errorMessage = 'HTTP ' . $httpCode;
            if ($body !== '') {
                $plain = strip_tags($body);
                $plain = preg_replace('/\s+/', ' ', $plain);
                $errorMessage .= ': ' . substr($plain, 0, 400);
            }
        } elseif ($httpCode >= 200 && $httpCode < 300 && $json === null && $body !== '') {
            if (stripos($body, '<html') !== false || stripos($body, '<!doctype') !== false) {
                $errorMessage = 'HTTP 200 ama yanit JSON degil (HTML). URL yanlis veya giris sayfasi donuyor olabilir.';
            }
        }

        return array(
            'http_code' => $httpCode,
            'effective_url' => (string) $effectiveUrl,
            'location' => $location,
            'response_headers' => $responseHeadersOut,
            'raw' => $body,
            'json' => $json,
            'curl_error' => (string) $curlError,
            'error_message' => $errorMessage,
        );
    }
}

/*
 * --- Test (bu blogun basindaki ve sonundaki yorum isaretlerini kaldirin, sabitleri doldurun) ---
 *
 * php TeksatIntegrationClient.php ile calistirmak icin: blogu acin ve asagidaki ilk satira require ekleyin;
 * veya bu kodu ayri bir test.php dosyasina tasiyin.
 *
 * require __DIR__ . '/TeksatIntegrationClient.php';
 *
 * $baseUrl    = 'https://PANEL-ADRESINIZ/api';
 * $apiKey     = 'TEKSAT_INTEGRATION_API_KEY';
 * $callerHost = 'allowliste-yazdiginiz-hostname-ornek-api.sirketiniz.com';
 * $apiDomainId = 'FUNNEL-TR-01';
 * $apiProductId = 'EXT-PROD-88';
 *
 * $client = new TeksatIntegrationClient($baseUrl, $apiKey, $callerHost, true);
 *
 * $r = $client->getDomains();
 * echo "domains http=" . $r['http_code'] . "\n";
 * print_r(isset($r['json']) ? $r['json'] : $r['raw']);
 *
 * $r = $client->getProductsByApiDomainId($apiDomainId);
 * echo "products http=" . $r['http_code'] . "\n";
 * print_r(isset($r['json']) ? $r['json'] : $r['raw']);
 *
 * $r = $client->getOffersByApiDomainId($apiDomainId);
 * echo "offers http=" . $r['http_code'] . " — paket quantity ile siparis quantity eslestirmesi icin\n";
 * print_r(isset($r['json']) ? $r['json'] : $r['raw']);
 *
 * $testOrderId = 'TEST-' . gmdate('YmdHis') . '-' . mt_rand(1000, 9999);
 *
 * // Senaryo A: api_offer_id yok — panelde bu urun icin ayni quantity tek paket ise offer_id otomatik dolar
 * $r = $client->createOrder(array(
 *     'api_domain_id' => $apiDomainId,
 *     'api_product_id' => $apiProductId,
 *     'name' => 'Test Musteri',
 *     'phone' => '05550001122',
 *     'city' => 'Istanbul',
 *     'district' => 'Kadikoy',
 *     'address' => 'Test adres',
 *     'unit_price' => 99.95,
 *     'total_price' => 1399.00,
 *     'quantity' => 3,
 *     'api_order_id' => $testOrderId,
 *     'payment_method' => 'cod',
 * ));
 * echo "createOrder A http=" . $r['http_code'] . "\n";
 * echo isset($r['error_message']) ? $r['error_message'] : '';
 * echo "\n";
 * print_r(isset($r['json']) ? $r['json'] : $r['raw']);
 *
 * // Senaryo C: cargo_tracking_no dolu -> tracking + yurtici + kargoya_verildi
 * // $r = $client->createOrder(array(
 * //     'api_domain_id' => $apiDomainId,
 * //     'api_product_id' => $apiProductId,
 * //     'name' => 'Test', 'phone' => '05550001122', 'city' => 'Istanbul', 'district' => 'Kadikoy', 'address' => 'Adr',
 * //     'unit_price' => 99.95, 'total_price' => 299.85, 'quantity' => 3,
 * //     'api_order_id' => $testOrderId . '-C',
 * //     'payment_method' => 'cod',
 * //     'cargo_tracking_no' => 'YT999000111',
 * // ));
 *
 * // Senaryo B: acik paket kodu (paneldeki api_offer_id ile ayni)
 * // $r = $client->createOrder(array(
 * //     'api_domain_id' => $apiDomainId,
 * //     'api_product_id' => $apiProductId,
 * //     'name' => 'Test Musteri',
 * //     'phone' => '05550003333',
 * //     'city' => 'Istanbul',
 * //     'district' => 'Kadikoy',
 * //     'address' => 'Test adres 2',
 * //     'unit_price' => 99.95,
 * //     'total_price' => 299.85,
 * //     'quantity' => 3,
 * //     'api_offer_id' => 'PAKET-3LU',
 * //     'api_order_id' => $testOrderId . '-B',
 * //     'payment_method' => 'cod',
 * // ));
 *
 */
