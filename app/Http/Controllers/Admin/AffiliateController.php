<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\AffiliateMedia;
use App\Models\AffiliateUser;
use App\Models\AffiliateWithdrawalRequest;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    /**
     * Display general affiliate statistics for admins.
     */
    public function stats(Request $request): View
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        // 1. General Metrics
        $totalClicks = \DB::table('affiliate_clicks')->count();
        $totalAttributions = \DB::table('affiliate_order_attributions')->count();
        $conversionRate = $totalClicks > 0 ? ($totalAttributions / $totalClicks) * 100 : 0;

        $totalSalesAmount = \DB::table('affiliate_commissions')
            ->whereIn('status', ['approved', 'paid', 'withdrawing'])
            ->sum('order_total');

        $totalCommissions = \DB::table('affiliate_commissions')
            ->whereIn('status', ['approved', 'paid', 'withdrawing'])
            ->sum('net_amount');

        $paidCommissions = \DB::table('affiliate_commissions')
            ->where('status', 'paid')
            ->sum('net_amount');

        $pendingCommissions = \DB::table('affiliate_commissions')
            ->where('status', 'pending')
            ->sum('net_amount');

        $approvedCommissions = \DB::table('affiliate_commissions')
            ->where('status', 'approved')
            ->sum('net_amount');

        // 2. User Stats
        $userStats = [
            'total' => \DB::table('affiliate_users')->count(),
            'active' => \DB::table('affiliate_users')->where('status', 'active')->count(),
            'pending' => \DB::table('affiliate_users')->where('status', 'pending')->count(),
            'suspended' => \DB::table('affiliate_users')->where('status', 'suspended')->count(),
        ];

        // 3. Top Performing Affiliates (by net commission earnings)
        $topAffiliates = \DB::table('affiliate_commissions')
            ->join('affiliate_users', 'affiliate_commissions.affiliate_id', '=', 'affiliate_users.id')
            ->select(
                'affiliate_users.id',
                'affiliate_users.name',
                'affiliate_users.affiliate_code',
                \DB::raw('COUNT(affiliate_commissions.id) as total_sales'),
                \DB::raw('SUM(affiliate_commissions.order_total) as total_sales_amount'),
                \DB::raw('SUM(affiliate_commissions.net_amount) as total_earnings')
            )
            ->whereIn('affiliate_commissions.status', ['approved', 'paid', 'withdrawing'])
            ->groupBy('affiliate_users.id', 'affiliate_users.name', 'affiliate_users.affiliate_code')
            ->orderByDesc('total_earnings')
            ->limit(5)
            ->get();

        // 4. Performance by Sales Site (Domain)
        $domainPerformance = \DB::table('affiliate_commissions')
            ->join('domains', 'affiliate_commissions.domain_id', '=', 'domains.id')
            ->select(
                'domains.id',
                'domains.domain_name',
                \DB::raw('COUNT(affiliate_commissions.id) as total_sales'),
                \DB::raw('SUM(affiliate_commissions.order_total) as total_sales_amount'),
                \DB::raw('SUM(affiliate_commissions.net_amount) as total_commissions')
            )
            ->whereIn('affiliate_commissions.status', ['approved', 'paid', 'withdrawing'])
            ->groupBy('domains.id', 'domains.domain_name')
            ->orderByDesc('total_sales')
            ->get();

        // 5. Clicks by Channel
        $clicksByChannel = \DB::table('affiliate_clicks')
            ->select('channel', \DB::raw('COUNT(id) as click_count'))
            ->groupBy('channel')
            ->orderByDesc('click_count')
            ->limit(5)
            ->get();

        // 6. Recent Clicks
        $recentClicks = \DB::table('affiliate_clicks')
            ->join('affiliate_users', 'affiliate_clicks.affiliate_id', '=', 'affiliate_users.id')
            ->join('domains', 'affiliate_clicks.domain_id', '=', 'domains.id')
            ->select(
                'affiliate_clicks.created_at',
                'affiliate_clicks.ip_address',
                'affiliate_clicks.channel',
                'affiliate_users.name as affiliate_name',
                'domains.domain_name'
            )
            ->orderByDesc('affiliate_clicks.created_at')
            ->limit(8)
            ->get();

        // 7. Recent Commissions
        $recentCommissions = \DB::table('affiliate_commissions')
            ->join('affiliate_users', 'affiliate_commissions.affiliate_id', '=', 'affiliate_users.id')
            ->join('domains', 'affiliate_commissions.domain_id', '=', 'domains.id')
            ->select(
                'affiliate_commissions.id',
                'affiliate_commissions.created_at',
                'affiliate_commissions.net_amount',
                'affiliate_commissions.status',
                'affiliate_users.name as affiliate_name',
                'domains.domain_name'
            )
            ->orderByDesc('affiliate_commissions.created_at')
            ->limit(8)
            ->get();

        return view('admin.affiliate.stats', compact(
            'totalClicks',
            'totalAttributions',
            'conversionRate',
            'totalSalesAmount',
            'totalCommissions',
            'paidCommissions',
            'pendingCommissions',
            'approvedCommissions',
            'userStats',
            'topAffiliates',
            'domainPerformance',
            'clicksByChannel',
            'recentClicks',
            'recentCommissions'
        ));
    }

    /**
     * List all affiliate users.
     */
    public function users(Request $request): View
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $query = AffiliateUser::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('affiliate_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.affiliate.users', compact('users'));
    }

    /**
     * Update user status (approve, suspend, etc.).
     */
    public function updateUserStatus(Request $request, AffiliateUser $user): RedirectResponse
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $request->validate([
            'status' => 'required|in:pending,active,suspended',
        ]);

        $user->update(['status' => $request->status]);

        return back()->with('success', "Affiliate üye durumu '{$request->status}' olarak güncellendi.");
    }

    /**
     * List all commissions.
     */
    public function commissions(Request $request): View
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $query = AffiliateCommission::with(['affiliate', 'order', 'domain', 'purchasedPackage']);

        if ($request->filled('affiliate_id')) {
            $query->where('affiliate_id', $request->input('affiliate_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->input('domain_id'));
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(20);
        $domains     = Domain::all();
        $affiliates  = AffiliateUser::where('status', 'active')->get();

        // ── Grouped summary for payment report tab ──
        $summaryQuery = AffiliateCommission::with('affiliate')
            ->when($request->filled('affiliate_id'), fn($q) => $q->where('affiliate_id', $request->affiliate_id))
            ->when($request->filled('status'),       fn($q) => $q->where('status', $request->status))
            ->when($request->filled('domain_id'),    fn($q) => $q->where('domain_id', $request->domain_id))
            ->selectRaw('affiliate_id, tax_type,
                COUNT(*) as total_orders,
                SUM(gross_commission) as total_gross,
                SUM(withholding_amount) as total_withholding,
                SUM(vat_amount) as total_vat,
                SUM(net_amount) as total_net')
            ->groupBy('affiliate_id', 'tax_type')
            ->orderByDesc('total_net')
            ->get();

        return view('admin.affiliate.commissions', compact(
            'commissions', 'domains', 'affiliates', 'summaryQuery'
        ));
    }

    /**
     * Export commission summary as CSV for bank payment orders.
     */
    public function exportCommissions(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403);

        $rows = AffiliateCommission::with('affiliate')
            ->when($request->filled('affiliate_id'), fn($q) => $q->where('affiliate_id', $request->affiliate_id))
            ->when($request->filled('status'),       fn($q) => $q->where('status', $request->status))
            ->when($request->filled('domain_id'),    fn($q) => $q->where('domain_id', $request->domain_id))
            ->selectRaw('affiliate_id, tax_type,
                COUNT(*) as total_orders,
                SUM(gross_commission) as total_gross,
                SUM(withholding_amount) as total_withholding,
                SUM(vat_amount) as total_vat,
                SUM(net_amount) as total_net')
            ->groupBy('affiliate_id', 'tax_type')
            ->orderByDesc('total_net')
            ->get();

        $filename = 'affiliate_hakediş_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Ad Soyad',
                'Affiliate Kodu',
                'Vergi Türü',
                'T.C. / Vergi No',
                'IBAN',
                'Adres',
                'İşlem Sayısı',
                'Brüt Hakediş (TL)',
                'Stopaj / KDV (TL)',
                'Net Ödenecek (TL)',
            ], ';');

            foreach ($rows as $row) {
                $aff = $row->affiliate;
                $taxLabel = match($row->tax_type) {
                    'company'    => 'Kurumsal',
                    'individual' => 'Bireysel',
                    default      => 'Muaf',
                };
                $deduction = $row->tax_type === 'company'
                    ? '-' . number_format($row->total_vat, 2, ',', '.')
                    : '-' . number_format($row->total_withholding, 2, ',', '.');

                fputcsv($handle, [
                    $aff?->name            ?? '-',
                    $aff?->affiliate_code  ?? '-',
                    $taxLabel,
                    $aff?->tax_number      ?? '-',
                    $aff?->iban            ?? '-',
                    $aff?->address         ?? '-',
                    $row->total_orders,
                    number_format($row->total_gross, 2, ',', '.'),
                    $deduction,
                    number_format($row->total_net, 2, ',', '.'),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Update commission status manually.
     */
    public function updateCommissionStatus(Request $request, AffiliateCommission $commission): RedirectResponse
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === 'approved') {
            $updateData['approved_at'] = now();
            $updateData['rejected_at'] = null;
        } elseif ($request->status === 'rejected') {
            $updateData['rejected_at'] = now();
            $updateData['approved_at'] = null;
        }

        $commission->update($updateData);

        return back()->with('success', "Komisyon durumu başarıyla güncellendi.");
    }

    /**
     * List all withdrawal requests.
     */
    public function withdrawals(Request $request): View
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $query = AffiliateWithdrawalRequest::with('affiliate');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.affiliate.withdrawals', compact('withdrawals'));
    }

    /**
     * Update withdrawal status (paid with receipt attachment / rejected).
     */
    public function updateWithdrawalStatus(Request $request, AffiliateWithdrawalRequest $withdrawal): RedirectResponse
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $request->validate([
            'status' => 'required|in:paid,rejected',
            'admin_note' => 'nullable|string|max:1000',
            'payment_receipt' => 'nullable|file|mimes:jpeg,png,pdf|max:5120', // Max 5MB
        ]);

        $updateData = [
            'status' => $request->status,
            'admin_note' => $request->admin_note,
        ];

        if ($request->status === 'paid') {
            $updateData['paid_at'] = now();

            if ($request->hasFile('payment_receipt')) {
                $receiptsDir = public_path('uploads/affiliate_receipts');
                if (!File::exists($receiptsDir)) {
                    File::makeDirectory($receiptsDir, 0755, true);
                }

                $file = $request->file('payment_receipt');
                $filename = 'receipt_' . $withdrawal->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($receiptsDir, $filename);
                $updateData['payment_receipt'] = $filename;
            }
        }

        DB::transaction(function() use ($withdrawal, $request, $updateData) {
            $withdrawal->update($updateData);

            // Fetch related withdrawing commissions
            $commissions = AffiliateCommission::where('affiliate_id', $withdrawal->affiliate_id)
                ->where('status', 'withdrawing')
                ->get();

            foreach ($commissions as $commission) {
                if ($request->status === 'paid') {
                    $commission->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                } else {
                    // Rollback to approved if rejected by admin
                    $commission->update([
                        'status' => 'approved',
                    ]);
                }
            }
        });

        return back()->with('success', "Ödeme talebi durumu güncellendi.");
    }

    /**
     * List promotional media assets.
     */
    public function media(Request $request): View
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $media = AffiliateMedia::with('domain')->orderBy('created_at', 'desc')->paginate(15);
        $domains = Domain::all();

        return view('admin.affiliate.media', compact('media', 'domains'));
    }

    /**
     * Upload and store a new media asset.
     */
    public function storeMedia(Request $request): RedirectResponse
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'title' => 'required|string|max:255',
            'media_type' => 'required|in:image,video,banner',
            'channel' => 'nullable|string|max:50',
            'size_label' => 'nullable|string|max:50',
            'share_text' => 'nullable|string|max:2000',
            'media_file' => 'required|file|mimes:jpeg,png,webp,mp4,mov|max:20480', // Max 20MB
        ]);

        $mediaDir = public_path('uploads/affiliate_media');
        if (!File::exists($mediaDir)) {
            File::makeDirectory($mediaDir, 0755, true);
        }

        $file = $request->file('media_file');
        $filename = 'media_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($mediaDir, $filename);

        AffiliateMedia::create([
            'domain_id' => $request->domain_id,
            'title' => $request->title,
            'media_type' => $request->media_type,
            'channel' => $request->channel,
            'size_label' => $request->size_label,
            'share_text' => $request->share_text,
            'file_path' => $filename,
            'status' => 'active',
        ]);

        return back()->with('success', 'Promosyon görseli/video başarıyla yüklendi.');
    }

    /**
     * Delete promotional media asset.
     */
    public function destroyMedia(AffiliateMedia $media): RedirectResponse
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $filePath = public_path('uploads/affiliate_media/' . $media->file_path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $media->delete();

        return back()->with('success', 'Medya materyali başarıyla silindi.');
    }

    /**
     * Impersonate an affiliate user.
     */
    public function impersonate(Request $request, AffiliateUser $user): RedirectResponse
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        \Illuminate\Support\Facades\Auth::guard('affiliate')->login($user);

        return redirect()->route('affiliate.dashboard')->with('success', "{$user->name} olarak giriş yapıldı.");
    }

    /**
     * View affiliate tax and rate settings.
     */
    public function settings(): \Illuminate\View\View
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.affiliate.settings', compact('settings'));
    }

    /**
     * Store affiliate tax and rate settings.
     */
    public function storeSettings(Request $request): RedirectResponse
    {
        abort_if(!auth()->user()->hasPermission('domains.view'), 403, 'Affiliate yönetim yetkiniz yok.');

        $request->validate([
            'affiliate_withholding_rate' => 'required|numeric|min:0|max:100',
            'affiliate_vat_rate' => 'required|numeric|min:0|max:100',
        ]);

        $withholdingRate = $request->input('affiliate_withholding_rate');
        $vatRate = $request->input('affiliate_vat_rate');

        \App\Models\Setting::updateOrCreate(
            ['key' => 'affiliate_withholding_rate'],
            ['value' => $withholdingRate, 'group' => 'affiliate']
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'affiliate_vat_rate'],
            ['value' => $vatRate, 'group' => 'affiliate']
        );

        // Recalculate unpaid commissions
        $commissions = AffiliateCommission::whereIn('status', ['pending', 'approved', 'withdrawing'])->get();
        foreach ($commissions as $commission) {
            $gross = (float) $commission->gross_commission;
            $taxType = $commission->tax_type;

            $withholding = 0.00;
            $vat = 0.00;
            $net = $gross;

            if ($taxType === 'individual') {
                $withholding = round($gross * ($withholdingRate / 100), 2);
                $net = round($gross - $withholding, 2);
            } elseif ($taxType === 'company') {
                $vat = round($gross * ($vatRate / 100), 2);
                $net = round($gross + $vat, 2);
            }

            $commission->update([
                'withholding_amount' => $withholding,
                'vat_amount' => $vat,
                'net_amount' => $net
            ]);
        }

        return back()->with('success', 'Affiliate vergilendirme ve oran ayarları başarıyla güncellendi.');
    }
}
