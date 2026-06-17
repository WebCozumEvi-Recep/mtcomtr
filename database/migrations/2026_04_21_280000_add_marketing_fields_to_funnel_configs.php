<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('funnel_configs', 'secondary_color')) {
                $table->string('secondary_color')->nullable()->after('primary_color');
            }
            $table->text('body_scripts')->nullable()->after('header_scripts');
            $table->text('success_scripts')->nullable()->after('footer_scripts');
            $table->string('facebook_pixel_id')->nullable()->after('success_scripts');
            $table->string('google_analytics_id')->nullable()->after('facebook_pixel_id');
            $table->string('google_verification_code')->nullable()->after('google_analytics_id');
        });
    }

    public function down(): void
    {
        Schema::table('funnel_configs', function (Blueprint $table) {
            $table->dropColumn([
                'secondary_color',
                'body_scripts', 
                'success_scripts', 
                'facebook_pixel_id', 
                'google_analytics_id', 
                'google_verification_code'
            ]);
        });
    }
};
