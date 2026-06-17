<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CargoSetting;
use Illuminate\Http\Request;

class CargoSettingController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermission('settings.view'), 403, 'Kargo ayarlarını görüntüleme yetkiniz yok.');
        $settings = CargoSetting::all();
        return view('admin.cargo-settings.index', compact('settings'));
    }

    public function update(Request $request, CargoSetting $cargoSetting)
    {
        abort_if(!auth()->user()->hasPermission('settings.edit'), 403, 'Kargo ayarlarını güncelleme yetkiniz yok.');
        $data = $request->validate([
            'api_username' => 'nullable|string',
            'api_password' => 'nullable|string',
            'api_cod_username' => 'nullable|string',
            'api_cod_password' => 'nullable|string',
            'api_customer_code' => 'nullable|string',
            'api_key' => 'nullable|string',
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $slug = strtolower(str_replace([' ', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ş', 'Ş', 'ö', 'Ö', 'ç', 'Ç'], ['-', 'i', 'i', 'g', 'g', 'u', 'u', 's', 's', 'o', 'o', 'c', 'c'], $cargoSetting->carrier_name));
            $filename = $slug . '.png';
            $path = public_path('uploads/logos/cargo');
            
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            if (!is_writable($path)) {
                return back()->with('error', 'Kargo logoları dizini yazılabilir değil (public/uploads/logos/cargo). Lütfen sunucu izinlerini kontrol edin.');
            }

            $logo->move($path, $filename);
        }

        $data['is_active'] = $request->has('is_active');
        $data['is_test_mode'] = $request->has('is_test_mode');

        $cargoSetting->update($data);

        return back()->with('success', $cargoSetting->display_name . ' ayarları güncellendi.');
    }
}
