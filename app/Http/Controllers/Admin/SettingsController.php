<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermission('settings.view'), 403, 'Sistem ayarlarını görüntüleme yetkiniz yok.');
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings', compact('settings'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('settings.edit'), 403, 'Ayarları güncelleme yetkiniz yok.');
        $data = $request->except(['_token', 'logo', 'favicon']);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key], 
                ['value' => $value, 'group' => 'seo']
            );
        }

        // Recalculate unpaid commissions if affiliate tax rates changed
        if (isset($data['affiliate_withholding_rate']) || isset($data['affiliate_vat_rate'])) {
            $withholdingRate = (float) Setting::val('affiliate_withholding_rate', 20.0);
            $vatRate = (float) Setting::val('affiliate_vat_rate', 20.0);

            $commissions = \App\Models\AffiliateCommission::whereIn('status', ['pending', 'approved', 'withdrawing'])->get();
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
        }

        // Sıkıntısız Medya Yüklemeleri (Doğrudan public/uploads dizinine atar, symlink gerektirmez)
        $uploadPath = public_path('uploads/settings');
        if(!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);
            
            Setting::updateOrCreate(
                ['key' => 'site_logo'], 
                ['value' => 'uploads/settings/' . $filename, 'group' => 'branding']
            );
        }

        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);
            
            Setting::updateOrCreate(
                ['key' => 'site_favicon'], 
                ['value' => 'uploads/settings/' . $filename, 'group' => 'branding']
            );
        }

        return redirect()->back()->with('success', 'Ayarlar ve görseller başarıyla güncellendi.');
    }

    public function runMigration()
    {
        abort_if(!auth()->user()->hasPermission('settings.edit'), 403, 'Bu işlemi yapma yetkiniz yok.');
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            
            $msg = !empty(trim($output)) ? $output : 'Veritabanı en güncel halinde, yürütülecek yeni bir tablo bulunamadı.';
            
            return redirect()->back()->with('success', 'Migration sonucu: ' . $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Komut hatası: ' . $e->getMessage());
        }
    }

    public function clearCache()
    {
        abort_if(!auth()->user()->hasPermission('settings.edit'), 403, 'Bu işlemi yapma yetkiniz yok.');
        try {
            Artisan::call('optimize:clear');
            return redirect()->back()->with('success', 'Sistem önbelleği başarıyla temizlendi (Cache, Route, Config ve View temizlendi).');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Hata: ' . $e->getMessage());
        }
    }

}
