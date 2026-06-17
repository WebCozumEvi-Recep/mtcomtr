<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'integration_ordered_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('integration_ordered_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'integration_ordered_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('integration_ordered_at');
            });
        }
    }
};
