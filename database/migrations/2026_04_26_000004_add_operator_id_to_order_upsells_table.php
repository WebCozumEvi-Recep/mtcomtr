<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_upsells', function (Blueprint $table) {
            $table->foreignId('operator_id')->nullable()->after('upsell_offer_id')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('order_upsells', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropColumn('operator_id');
        });
    }
};
