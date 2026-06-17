<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAffiliate
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('affiliate');

        if (!$guard->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
            return redirect()->route('affiliate.login')->with('error', 'Lütfen devam etmek için giriş yapın.');
        }

        $user = $guard->user();

        if ($user->status !== 'active') {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->status === 'pending'
                ? 'Hesabınız henüz onaylanmamıştır. Lütfen yöneticinin onaylamasını bekleyin.'
                : 'Hesabınız askıya alınmıştır. Lütfen destek ile iletişime geçin.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('affiliate.login')->with('error', $message);
        }

        return $next($request);
    }
}
