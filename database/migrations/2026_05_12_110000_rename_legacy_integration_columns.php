<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eski sürümde (Türkçe sütun adları + domains.api_secret) migrate edilmiş veritabanları için.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'api_gonderilme_tarihi')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('api_gonderilme_tarihi', 'api_sent_at');
            });
        }

        if (Schema::hasColumn('orders', 'api_onay')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('api_onay', 'api_approved');
            });
        }

        if (Schema::hasColumn('domains', 'api_secret')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->dropColumn('api_secret');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'api_sent_at') && ! Schema::hasColumn('orders', 'api_gonderilme_tarihi')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('api_sent_at', 'api_gonderilme_tarihi');
            });
        }

        if (Schema::hasColumn('orders', 'api_approved') && ! Schema::hasColumn('orders', 'api_onay')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('api_approved', 'api_onay');
            });
        }

        if (! Schema::hasColumn('domains', 'api_secret')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->string('api_secret', 128)->nullable()->after('api_integration_enabled');
            });
        }
    }
};
