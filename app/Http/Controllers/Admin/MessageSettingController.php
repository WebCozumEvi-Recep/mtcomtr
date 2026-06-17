<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageSettingController extends Controller
{
    public function index()
    {
        try { \Artisan::call('view:clear'); } catch(\Exception $e) {}
        abort_if(!auth()->user()->hasPermission('settings.view'), 403);
        $templates = MessageTemplate::all();
        
        if ($templates->isEmpty()) {
            \DB::table('message_templates')->insert([
                [
                    'name' => 'Sipariş Onay Mesajı',
                    'key' => 'order_confirmation',
                    'content' => "Merhaba [CUSTOMER_NAME], [DOMAIN_NAME] siparişiniz için teşekkür ederiz.\n\nSiparişinizi kargoya hazırlamadan önce teyit rica ederiz:\n\n[PRODUCT_LIST]\n\nPaket: [PACKAGE_NAME]\nToplam: [TOTAL_PRICE] TL\nÖdeme: [PAYMENT_METHOD]\n\nAdres: [ADDRESS]\n\nBilgiler doğruysa lütfen “Onaylıyorum” yazınız.\nÖdemenizi teslimat sırasında kargo görevlisine yapabilirsiniz.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Kargo Bilgilendirme Mesajı',
                    'key' => 'shipping_info',
                    'content' => "Merhaba [CUSTOMER_NAME], [ORDER_NUMBER] numaralı siparişiniz kargoya verilmiştir.\n\nKargo Firması: [CARGO_FIRM]\nTakip No: [TRACKING_NUMBER]\n\nTakip Linki: [TRACKING_URL]\n\nBizi tercih ettiğiniz için teşekkür ederiz.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Kargo Teslim Alınmadıysa Uyarı',
                    'key' => 'no_delivery_warning',
                    'content' => "Merhaba [CUSTOMER_NAME], [ORDER_NUMBER] numaralı siparişiniz kargo şubesinde beklemektedir.\n\nLütfen en kısa sürede teslim alınız, aksi takdirde iade süreci başlayacaktır.\n\nTakip No: [TRACKING_NUMBER]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
            $templates = MessageTemplate::all();
        }

        return view('admin.settings.messages', compact('templates'));
    }

    public function update(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('settings.edit'), 403);
        
        $request->validate([
            'templates' => 'required|array',
            'templates.*.id' => 'required|exists:message_templates,id',
            'templates.*.content' => 'required|string',
        ]);

        foreach ($request->templates as $templateData) {
            MessageTemplate::where('id', $templateData['id'])->update([
                'content' => $templateData['content']
            ]);
        }

        return back()->with('success', 'Mesaj kalıpları başarıyla güncellendi.');
    }
}
