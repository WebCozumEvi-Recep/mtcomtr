<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cargo_firm')->nullable()->after('status');
            $table->string('tracking_number')->nullable()->after('cargo_firm');
            $table->string('internal_order_no')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cargo_firm', 'tracking_number', 'internal_order_no']);
        });
    }
};
