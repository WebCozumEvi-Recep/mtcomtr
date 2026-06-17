<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('original_total', 10, 2)->nullable()->after('grand_total');
            $table->decimal('upsell_total', 10, 2)->default(0)->after('original_total');
            $table->decimal('final_total', 10, 2)->nullable()->after('upsell_total');
            $table->boolean('has_upsell')->default(false)->after('final_total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['original_total', 'upsell_total', 'final_total', 'has_upsell']);
        });
    }
};
