<?php

namespace App\Http\Controllers;

use App\Models\AffiliateClick;
use App\Models\AffiliateLink;
use App\Models\AffiliateUser;
use App\Models\AffiliateDomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    /**
     * Track affiliate click and redirect to destination.
     */
    public function trackLink(Request $request, string $short_code): RedirectResponse
    {
        $link = AffiliateLink::with(['affiliate', 'domain'])
            ->where('short_code', $short_code)
            ->where('status', 'active')
            ->first();

        if (!$link || !$link->affiliate || !$link->affiliate->isActive()) {
            return redirect('/');
        }

        // Get affiliate domain setting or fallback
        $domainSetting = AffiliateDomain::where('domain_id', $link->domain_id)->first();
        if ($domainSetting && !$domainSetting->is_affiliate_active) {
            return redirect($link->target_path ?: '/');
        }

        $cookieDays = $domainSetting ? $domainSetting->cookie_days : 30;

        // Detect device
        $userAgent = $request->userAgent() ?: '';
        $device = 'desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) {
            $device = 'tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            $device = 'mobile';
        }

        // Generate a secure unique click id
        $clickId = (string) Str::uuid();

        // Save Click
        AffiliateClick::create([
            'affiliate_id' => $link->affiliate_id,
            'affiliate_link_id' => $link->id,
            'domain_id' => $link->domain_id,
            'channel' => $link->channel,
            'keyword' => $link->keyword,
            'media_id' => $link->media_id,
            'click_id' => $clickId,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'referer' => $request->headers->get('referer'),
            'device' => $device,
        ]);

        $cookieValue = json_encode([
            'click_id' => $clickId,
            'affiliate_id' => $link->affiliate_id,
            'link_id' => $link->id,
            'domain_id' => $link->domain_id,
            'channel' => $link->channel,
            'keyword' => $link->keyword,
            'media_id' => $link->media_id,
            'timestamp' => time(),
        ]);

        // Redirect with a first-party cookie
        return redirect($link->target_path ?: '/')
            ->cookie(
                'ts_affiliate_click',
                $cookieValue,
                $cookieDays * 24 * 60, // minutes
                '/',
                $request->getHost(), // first-party domain matching
                $request->secure(),
                true // httpOnly
            );
    }

    public function welcome(): \Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        if (\Illuminate\Support\Facades\Auth::guard('affiliate')->check()) {
            return redirect()->route('affiliate.dashboard');
        }
        return view('affiliate.welcome');
    }

    public function showLogin(): View
    {
        if (Auth::guard('affiliate')->check()) {
            return redirect()->route('affiliate.dashboard');
        }
        return view('affiliate.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $guard = Auth::guard('affiliate');

        if (!$guard->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Girdiğiniz bilgiler hatalıdır.'])
                ->onlyInput('email');
        }

        $user = $guard->user();

        if ($user->status !== 'active') {
            $message = $user->status === 'pending'
                ? 'Hesabınız onay beklemektedir. Lütfen yöneticinin hesabınızı onaylamasını bekleyin.'
                : 'Hesabınız askıya alınmıştır. Detaylar için yöneticinizle görüşün.';

            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => $message])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('affiliate.dashboard'));
    }

    public function showRegister(): View
    {
        if (Auth::guard('affiliate')->check()) {
            return redirect()->route('affiliate.dashboard');
        }
        return view('affiliate.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:affiliate_users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'tax_type' => ['required', 'string', 'in:individual,company,none'],
            'company_name' => ['nullable', 'required_if:tax_type,company', 'string', 'max:255'],
            'tax_office' => ['nullable', 'required_if:tax_type,company', 'string', 'max:255'],
            'tax_number' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string'],
            'iban' => ['required', 'string', 'max:50'],
        ], [
            'email.unique' => 'Bu e-posta adresi zaten kullanımda.',
            'password.confirmed' => 'Şifreler uyuşmamaktadır.',
            'password.min' => 'Şifre en az 8 karakterden oluşmalıdır.',
            'company_name.required_if' => 'Şirket adı alanı şirket vergi türü için zorunludur.',
            'tax_office.required_if' => 'Vergi dairesi alanı şirket vergi türü için zorunludur.',
            'tax_number.required' => 'T.C. Kimlik / Vergi numarası alanı zorunludur.',
            'address.required' => 'Adres alanı zorunludur.',
            'iban.required' => 'IBAN alanı ödeme alabilmeniz için zorunludur.'
        ]);

        // Generate unique affiliate code
        do {
            $code = 'AFF-' . strtoupper(Str::random(8));
        } while (AffiliateUser::where('affiliate_code', $code)->exists());

        AffiliateUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password, // hashed automatically by cast in AffiliateUser model
            'affiliate_code' => $code,
            'status' => 'pending', // Registration starts as pending approval
            'tax_type' => $request->tax_type,
            'iban' => $request->iban,
            'tax_office' => $request->tax_office,
            'tax_number' => $request->tax_number,
            'company_name' => $request->company_name,
            'address' => $request->address,
        ]);

        return redirect()->route('affiliate.login')->with('success', 'Hesap başvurunuz başarıyla alınmıştır. Yönetici onayının ardından giriş yapabilirsiniz.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('affiliate')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('affiliate.login');
    }
}
