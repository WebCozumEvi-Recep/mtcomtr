<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_configs', function (Blueprint $table) {
            $table->string('tiktok_pixel_id')->nullable()->after('google_verification_code');
        });
    }

    public function down(): void
    {
        Schema::table('funnel_configs', function (Blueprint $table) {
            $table->dropColumn('tiktok_pixel_id');
        });
    }
};
