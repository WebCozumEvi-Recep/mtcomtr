<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->text('content');
            $table->timestamps();
        });

        // Seed default templates
        \DB::table('message_templates')->insert([
            [
                'name' => 'Sipariş Onay Mesajı',
                'key' => 'order_confirmation',
                'content' => "Merhaba [CUSTOMER_NAME], [DOMAIN_NAME] siparişiniz için teşekkür ederiz.\n\nSiparişinizi kargoya hazırlamadan önce teyit rica ederiz:\n\nÜrün: [PRODUCT_NAME]\nPaket: [PACKAGE_NAME]\nToplam: [TOTAL_PRICE] TL\nÖdeme: [PAYMENT_METHOD]\n\nAdres: [ADDRESS]\n\nBilgiler doğruysa lütfen “Onaylıyorum” yazınız.\nÖdemenizi teslimat sırasında kargo görevlisine yapabilirsiniz.",
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
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
