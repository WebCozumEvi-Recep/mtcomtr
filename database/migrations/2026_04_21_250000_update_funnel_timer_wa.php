<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_configs', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->after('primary_color');
            $table->integer('countdown_minutes')->default(15)->after('whatsapp_number');
        });
    }

    public function down(): void
    {
        Schema::table('funnel_configs', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'countdown_minutes']);
        });
    }
};
