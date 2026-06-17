<?php

use App\Http\Controllers\Api\IntegrationController;
use Illuminate\Support\Facades\Route;

/*
| Harici entegrasyon uçları — bu dosya iki yerde gruplanır:
| - /api/v1/integration/...  (route adı öneki: api.integration.*)
| - /v1/integration/...       (route adı öneki: integration.open.*) — /api ön eki olmayan kurulumlar için
*/

Route::get('domains', [IntegrationController::class, 'domains'])
    ->middleware(['integration.caller_host', 'integration.list'])
    ->name('domains');

Route::get('products', [IntegrationController::class, 'products'])
    ->middleware(['integration.caller_host', 'integration.list'])
    ->name('products');

Route::get('offers', [IntegrationController::class, 'offers'])
    ->middleware(['integration.caller_host', 'integration.list'])
    ->name('offers');

Route::post('orders', [IntegrationController::class, 'storeOrder'])
    ->middleware(['integration.caller_host', 'integration.order_signature'])
    ->name('orders.store');
