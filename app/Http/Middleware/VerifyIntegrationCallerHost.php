<?php

namespace App\Http\Middleware;

use App\Support\IntegrationSourceHost;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIntegrationCallerHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $allow = config('teksat.integration_allowed_caller_hosts', []);
        if (! is_array($allow) || $allow === []) {
            return response()->json([
                'success' => false,
                'message' => 'Entegrasyon için izinli çağıran hostlar tanımlı değil (TEKSAT_INTEGRATION_ALLOWED_HOSTS).',
            ], 503);
        }

        $caller = IntegrationSourceHost::fromRequest($request);
        if ($caller === null || $caller === '') {
            return response()->json([
                'success' => false,
                'message' => 'İstek kaynağı gerekli: X-Teksat-Source-Host veya Origin / Referer (ortağın domaini).',
            ], 422);
        }

        if (! IntegrationSourceHost::callerHostMatchesAllowlist($caller, $allow)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu kaynak host entegrasyon için yetkili değil. TEKSAT_INTEGRATION_ALLOWED_HOSTS listesini güncelleyin.',
            ], 403);
        }

        return $next($request);
    }
}
