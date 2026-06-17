<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIntegrationListToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('teksat.integration_api_key');
        if ($expected === null || $expected === '') {
            return response()->json([
                'success' => false,
                'message' => 'Entegrasyon API anahtarı yapılandırılmamış (TEKSAT_INTEGRATION_API_KEY).',
            ], 503);
        }

        $header = $request->header('Authorization', '');
        if (! preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization: Bearer {api_key} gerekli.',
            ], 401);
        }

        if (! hash_equals($expected, trim($m[1]))) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz API anahtarı.',
            ], 401);
        }

        return $next($request);
    }
}
