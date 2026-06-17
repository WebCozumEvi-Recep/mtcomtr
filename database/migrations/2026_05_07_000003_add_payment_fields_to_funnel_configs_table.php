<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('funnel_configs', function (Blueprint $table) {
            $table->boolean('allow_credit_card')->default(false)->after('tiktok_pixel_id');
            $table->foreignId('payment_provider_id')->nullable()->constrained('payment_providers')->nullOnDelete()->after('allow_credit_card');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funnel_configs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_provider_id');
            $table->dropColumn('allow_credit_card');
        });
    }
};
