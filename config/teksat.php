<?php

return [
    /*
    | Harici ortağın API anahtarı (aynı değer):
    | - GET  → Authorization: Bearer {TEKSAT_INTEGRATION_API_KEY}
    | - POST → HMAC-SHA256(api_key, timestamp + "\n" + raw_json_body)
    |
    | Geriye dönük: TEKSAT_INTEGRATION_SECRET veya TEKSAT_INTEGRATION_LIST_TOKEN.
    */
    'integration_api_key' => env('TEKSAT_INTEGRATION_API_KEY')
        ?: env('TEKSAT_INTEGRATION_SECRET')
        ?: env('TEKSAT_INTEGRATION_LIST_TOKEN'),

    /*
    | Sadece bu hostlardan gelen istekler kabul edilir (virgülle).
    | Örn: www.backend-drclinic.com.tr,backend-drclinic.com.tr
    | İstekte X-Teksat-Source-Host veya Origin / Referer hostu bu listeyle eşleşir (www/kök).
    */
    'integration_allowed_caller_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TEKSAT_INTEGRATION_ALLOWED_HOSTS', ''))
    ))),

    'integration_signature_tolerance_seconds' => (int) env('TEKSAT_INTEGRATION_SIGNATURE_TOLERANCE', 300),
];
