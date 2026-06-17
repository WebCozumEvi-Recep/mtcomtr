<?php

namespace App\Http\Controllers;

use App\Models\AffiliateClick;
use App\Models\AffiliateCommission;
use App\Models\AffiliateDomain;
use App\Models\AffiliateLink;
use App\Models\AffiliateMedia;
use App\Models\AffiliateUser;
use App\Models\AffiliateWithdrawalRequest;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AffiliatePortalController extends Controller
{
    private function getAffiliateId(): int
    {
        return Auth::guard('affiliate')->id();
    }

    private function getAffiliate(): AffiliateUser
    {
        return Auth::guard('affiliate')->user();
    }

    /**
     * Affiliate Dashboard
     */
    public function dashboard(): View
    {
        $affiliateId = $this->getAffiliateId();

        // 30 Days ranges
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Summary Statistics
        $totalClicks = AffiliateClick::where('affiliate_id', $affiliateId)->count();
        $totalOrders = AffiliateCommission::where('affiliate_id', $affiliateId)->count();
        $conversionRate = $totalClicks > 0 ? round(($totalOrders / $totalClicks) * 100, 2) : 0;

        $pendingBalance = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('status', 'pending')
            ->sum('net_amount');

        $withdrawableBalance = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('status', 'approved')
            ->sum('net_amount');

        $paidBalance = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('status', 'paid')
            ->sum('net_amount');

        // Recent Clicks (last 5)
        $recentClicks = AffiliateClick::with('link')
            ->where('affiliate_id', $affiliateId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent Commissions (last 5)
        $recentCommissions = AffiliateCommission::with(['order', 'domain'])
            ->where('affiliate_id', $affiliateId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart Data (last 15 days)
        $chartData = AffiliateClick::where('affiliate_id', $affiliateId)
            ->where('created_at', '>=', Carbon::now()->subDays(14)->startOfDay())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as clicks'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('clicks', 'date')
            ->toArray();

        $chartCommissions = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('created_at', '>=', Carbon::now()->subDays(14)->startOfDay())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(net_amount) as earnings'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('earnings', 'date')
            ->toArray();

        // Standardize dates for chart
        $dates = [];
        $clicksArray = [];
        $earningsArray = [];
        for ($i = 14; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = Carbon::parse($d)->translatedFormat('d M');
            $clicksArray[] = $chartData[$d] ?? 0;
            $earningsArray[] = (float) ($chartCommissions[$d] ?? 0);
        }

        return view('affiliate.dashboard', compact(
            'totalClicks',
            'totalOrders',
            'conversionRate',
            'pendingBalance',
            'withdrawableBalance',
            'paidBalance',
            'recentClicks',
            'recentCommissions',
            'dates',
            'clicksArray',
            'earningsArray'
        ));
    }

    /**
     * Campaigns / Promotional Sites
     */
    public function campaigns(): View
    {
        $domains = Domain::whereHas('affiliateSetting', function($query) {
            $query->where('is_affiliate_active', true);
        })
        ->with(['affiliateSetting', 'affiliatePackageCommissions.package'])
        ->get();

        $mediaAssets = AffiliateMedia::where('status', 'active')->get();

        return view('affiliate.campaigns', compact('domains', 'mediaAssets'));
    }

    /**
     * Generate unique link
     */
    public function generateLink(Request $request): RedirectResponse
    {
        $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'channel' => 'nullable|string|max:50',
            'keyword' => 'nullable|string|max:50',
            'media_id' => 'nullable|exists:affiliate_media,id',
            'target_path' => 'nullable|string|max:255',
        ]);

        $domain = Domain::findOrFail($request->domain_id);
        $affiliate = $this->getAffiliate();

        // Target path clean up
        $targetPath = '/' . ltrim($request->target_path ?? '/', '/');

        // Short code generation
        do {
            $shortCode = Str::random(6);
        } while (AffiliateLink::where('short_code', $shortCode)->exists());

        // Construct target URL using the sales domain instead of teksat
        // Important: referral links must reside on the domain name itself
        $protocol = $request->secure() ? 'https://' : 'http://';
        $domainName = rtrim($domain->domain_name, '/');
        $fullAffiliateUrl = $protocol . $domainName . '/t/' . $shortCode;

        AffiliateLink::create([
            'affiliate_id' => $affiliate->id,
            'domain_id' => $domain->id,
            'domain_url' => $domainName,
            'channel' => $request->channel,
            'keyword' => $request->keyword,
            'media_id' => $request->media_id,
            'short_code' => $shortCode,
            'full_affiliate_url' => $fullAffiliateUrl,
            'target_path' => $targetPath,
            'status' => 'active'
        ]);

        return redirect()->route('affiliate.links')->with('success', 'Affiliate linkiniz başarıyla oluşturuldu.');
    }

    /**
     * List links
     */
    public function links(): View
    {
        $links = AffiliateLink::with(['domain', 'media'])
            ->where('affiliate_id', $this->getAffiliateId())
            ->withCount('clicks')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('affiliate.links', compact('links'));
    }

    /**
     * Delete/Deactivate link
     */
    public function deleteLink(int $id): RedirectResponse
    {
        $link = AffiliateLink::where('affiliate_id', $this->getAffiliateId())->findOrFail($id);
        $link->delete();

        return back()->with('success', 'Link başarıyla silindi.');
    }

    /**
     * Media Center
     */
    public function media(): View
    {
        $media = AffiliateMedia::with('domain')
            ->where('status', 'active')
            ->paginate(12);

        $domains = Domain::whereHas('affiliateSetting', function($query) {
            $query->where('is_affiliate_active', true);
        })->get();

        return view('affiliate.media', compact('media', 'domains'));
    }

    /**
     * Stats & Reporting
     */
    public function stats(Request $request): View
    {
        $affiliateId = $this->getAffiliateId();

        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        $domainId = $request->input('domain_id');
        $channel = $request->input('channel');

        // Main Query Builders
        $clicksQuery = AffiliateClick::where('affiliate_id', $affiliateId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $commissionsQuery = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($domainId) {
            $clicksQuery->where('domain_id', $domainId);
            $commissionsQuery->where('domain_id', $domainId);
        }

        if ($channel) {
            $clicksQuery->where('channel', $channel);
            $commissionsQuery->where('channel', $channel);
        }

        // Totals
        $clicksCount = $clicksQuery->count();
        $commissionsCount = $commissionsQuery->count();
        $totalNetEarnings = $commissionsQuery->sum('net_amount');
        $totalGrossEarnings = $commissionsQuery->sum('gross_commission');

        // Detailed Clicks list
        $clicks = $clicksQuery->with('link.domain')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'clicks_page');

        // Detailed Commissions list
        $commissions = $commissionsQuery->with(['order', 'domain', 'purchasedPackage'])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'commissions_page');

        $domains = Domain::whereHas('affiliateSetting', function($query) {
            $query->where('is_affiliate_active', true);
        })->get();

        $channels = AffiliateLink::where('affiliate_id', $affiliateId)
            ->whereNotNull('channel')
            ->groupBy('channel')
            ->pluck('channel');

        return view('affiliate.stats', compact(
            'clicks',
            'commissions',
            'clicksCount',
            'commissionsCount',
            'totalNetEarnings',
            'totalGrossEarnings',
            'startDate',
            'endDate',
            'domainId',
            'channel',
            'domains',
            'channels'
        ));
    }

    /**
     * Withdrawals page and submission
     */
    public function withdrawals(): View
    {
        $affiliate = $this->getAffiliate();
        $affiliateId = $affiliate->id;

        // Balance calculations
        $totalPaidOut = AffiliateWithdrawalRequest::where('affiliate_id', $affiliateId)
            ->where('status', 'paid')
            ->sum('net_payment');

        $withdrawableBalance = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('status', 'approved')
            ->sum('net_amount');

        $withdrawingBalance = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('status', 'withdrawing')
            ->sum('net_amount');

        $requests = AffiliateWithdrawalRequest::where('affiliate_id', $affiliateId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('affiliate.withdrawals', compact(
            'affiliate',
            'totalPaidOut',
            'withdrawableBalance',
            'withdrawingBalance',
            'requests'
        ));
    }

    /**
     * Request payout
     */
    public function requestWithdrawal(Request $request): RedirectResponse
    {
        $affiliate = $this->getAffiliate();
        $affiliateId = $affiliate->id;

        // Minimum payout limit
        $minPayout = 500.00;

        // Calculate available approved commissions
        $approvedCommissions = AffiliateCommission::where('affiliate_id', $affiliateId)
            ->where('status', 'approved')
            ->get();

        $withdrawableSum = $approvedCommissions->sum('net_amount');

        if ($withdrawableSum < $minPayout) {
            return back()->with('error', "Minimum ödeme talebi tutarı {$minPayout} TL'dir. Mevcut bakiyeniz: {$withdrawableSum} TL.");
        }

        $request->validate([
            'iban' => 'required|string|max:50',
        ]);

        // Keep IBAN updated in user profile
        $affiliate->update(['iban' => $request->iban]);

        // Sum gross, withholding, vat and net payments
        $grossSum = $approvedCommissions->sum('gross_commission');
        $withholdingSum = $approvedCommissions->sum('withholding_amount');
        $vatSum = $approvedCommissions->sum('vat_amount');
        $netPaymentSum = $approvedCommissions->sum('net_amount');

        DB::transaction(function() use ($affiliateId, $netPaymentSum, $grossSum, $withholdingSum, $vatSum, $request, $approvedCommissions) {
            // Create withdrawal request
            AffiliateWithdrawalRequest::create([
                'affiliate_id' => $affiliateId,
                'requested_amount' => $netPaymentSum,
                'gross_amount' => $grossSum,
                'withholding_amount' => $withholdingSum,
                'vat_amount' => $vatSum,
                'net_payment' => $netPaymentSum,
                'status' => 'pending',
                'iban' => $request->iban,
            ]);

            // Transition approved commissions to withdrawing status
            foreach ($approvedCommissions as $commission) {
                $commission->update(['status' => 'withdrawing']);
            }
        });

        return redirect()->route('affiliate.withdrawals')->with('success', 'Ödeme talebiniz başarıyla alındı. İncelemelerin ardından hesabınıza aktarılacaktır.');
    }

    /**
     * View profile settings
     */
    public function settings(): View
    {
        $affiliate = $this->getAffiliate();
        return view('affiliate.settings', compact('affiliate'));
    }

    /**
     * Update profile settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $affiliate = $this->getAffiliate();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:affiliate_users,email,' . $affiliate->id,
            'phone' => 'nullable|string|max:20',
            'iban' => 'required|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
            'tax_type' => 'required|string|in:individual,company,none',
            'company_name' => 'nullable|required_if:tax_type,company|string|max:255',
            'tax_office' => 'nullable|required_if:tax_type,company|string|max:255',
            'tax_number' => 'required|string|max:50',
            'address' => 'required|string',
        ], [
            'company_name.required_if' => 'Şirket adı alanı şirket vergi türü için zorunludur.',
            'tax_office.required_if' => 'Vergi dairesi alanı şirket vergi türü için zorunludur.',
            'tax_number.required' => 'T.C. Kimlik / Vergi numarası alanı zorunludur.',
            'address.required' => 'Adres alanı zorunludur.',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'iban' => $request->iban,
            'tax_type' => $request->tax_type,
            'company_name' => $request->company_name,
            'tax_office' => $request->tax_office,
            'tax_number' => $request->tax_number,
            'address' => $request->address,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $affiliate->update($data);

        return redirect()->route('affiliate.settings')->with('success', 'Profil ve hesap ayarlarınız başarıyla güncellendi.');
    }
}
