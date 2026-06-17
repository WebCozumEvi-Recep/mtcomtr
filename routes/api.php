<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\FunnelTrackingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Teksat Frontend Funnel Sipariş Gönderim Ucu
Route::post('/orders', [OrderController::class, 'store'])->name('api.orders.store');
Route::post('/funnel/track', [FunnelTrackingController::class, 'track'])->name('api.funnel.track');

// Harici entegrasyon: izinli çağıran host + api_key (Bearer / HMAC)
// Aynı uçlar ayrıca bootstrap/app.php içinde /v1/integration (api ön eki olmadan) kayıtlıdır.
Route::prefix('v1/integration')->middleware('throttle:120,1')->name('api.integration.')->group(base_path('routes/integration.php'));
