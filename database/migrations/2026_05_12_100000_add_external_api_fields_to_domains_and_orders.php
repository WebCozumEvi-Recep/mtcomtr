<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->boolean('api_integration_enabled')->default(false)->after('is_active');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_api')->default(false)->after('payment_method');
            $table->timestamp('api_sent_at')->nullable()->after('is_api');
            $table->boolean('api_approved')->default(false)->after('api_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_api', 'api_sent_at', 'api_approved']);
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn(['api_integration_enabled']);
        });
    }
};
