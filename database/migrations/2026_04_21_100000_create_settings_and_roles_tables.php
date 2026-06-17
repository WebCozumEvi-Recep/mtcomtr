<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Yetki / Rol Tablosu
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Örn: 'Süper Admin', 'Depo Görevlisi', 'Çağrı Merkezi'
            $table->json('permissions')->nullable(); // Hangi modüllere erişebileceği dizisi örn: ["orders.view", "orders.edit", "settings.manage"]
            $table->timestamps();
        });

        // User tablosuna Role ID bağlama
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('custom_role_id')->nullable()->constrained('roles')->nullOnDelete();
        });

        // Gelişmiş Ayarlar (Settings) Tablosu (Mail, Cloudflare, API vs.)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index(); // 'mail', 'cloudflare', 'integrations'
            $table->string('key')->unique();  // 'MAIL_HOST', 'CLOUDFLARE_API_KEY'
            $table->text('value')->nullable();
            $table->boolean('is_sensitive')->default(false); // Şifre veya gizli API anahtarı ise maskelemek için
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['custom_role_id']);
            $table->dropColumn('custom_role_id');
        });
        Schema::dropIfExists('roles');
    }
};
