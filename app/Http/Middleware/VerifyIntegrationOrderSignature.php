<?php

namespace App\Http\Middleware;

use App\Models\Domain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIntegrationOrderSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('teksat.integration_api_key');
        if ($apiKey === null || $apiKey === '') {
            return response()->json([
                'success' => false,
                'message' => 'Entegrasyon API anahtarı yapılandırılmamış (TEKSAT_INTEGRATION_API_KEY).',
            ], 503);
        }

        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true);
        if (! is_array($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçerli JSON gövdesi gerekli.',
            ], 400);
        }

        $domain = Domain::forIntegrationPayload($payload);

        if (! $domain) {
            return response()->json([
                'success' => false,
                'message' => 'domain_id veya api_domain_id gerekli; geçersiz değer veya domain_id ile api_domain_id çelişiyor.',
            ], 422);
        }

        $timestamp = $request->header('X-Teksat-Timestamp');
        $signature = $request->header('X-Teksat-Signature');
        if ($timestamp === null || $timestamp === '' || $signature === null || $signature === '') {
            return response()->json([
                'success' => false,
                'message' => 'X-Teksat-Timestamp ve X-Teksat-Signature başlıkları zorunludur.',
            ], 401);
        }

        if (! ctype_digit((string) $timestamp)) {
            return response()->json([
                'success' => false,
                'message' => 'X-Teksat-Timestamp unix saniye olmalıdır.',
            ], 422);
        }

        $ts = (int) $timestamp;
        $tolerance = max(60, (int) config('teksat.integration_signature_tolerance_seconds', 300));
        if (abs(time() - $ts) > $tolerance) {
            return response()->json([
                'success' => false,
                'message' => 'İstek zaman damgası geçersiz veya süresi dolmuş (replay koruması).',
            ], 401);
        }

        $expected = hash_hmac('sha256', $timestamp . "\n" . $rawBody, $apiKey);
        if (! hash_equals($expected, strtolower(trim($signature)))) {
            return response()->json([
                'success' => false,
                'message' => 'İmza doğrulanamadı (api_key ile HMAC).',
            ], 401);
        }

        $request->attributes->set('integration_domain', $domain);

        return $next($request);
    }
}
