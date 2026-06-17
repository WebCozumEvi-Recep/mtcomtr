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
        Schema::table('funnel_configs', function (Blueprint $blueprint) {
            $blueprint->integer('stock_countdown_start')->nullable()->default(719)->after('countdown_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funnel_configs', function (Blueprint $blueprint) {
            $blueprint->dropColumn('stock_countdown_start');
        });
    }
};
