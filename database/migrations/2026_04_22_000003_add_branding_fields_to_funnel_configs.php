<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_configs', function (Blueprint $table) {
            $table->string('favicon_path')->nullable()->after('domain_id');
            $table->string('og_image_path')->nullable()->after('favicon_path');
        });
    }

    public function down(): void
    {
        Schema::table('funnel_configs', function (Blueprint $table) {
            $table->dropColumn(['favicon_path', 'og_image_path']);
        });
    }
};
