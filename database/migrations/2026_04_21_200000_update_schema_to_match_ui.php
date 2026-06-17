<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'image_url')) {
                $table->string('image_url')->nullable()->after('description');
            }
            if (Schema::hasColumn('products', 'stock')) {
                $table->renameColumn('stock', 'stock_quantity');
            }
            if (Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable()->change();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->nullable()->unique()->after('id');
            }
            // grand_total is handled in core or previous migration
            if (!Schema::hasColumn('orders', 'risk_level')) {
                $table->string('risk_level')->default('low')->after('fraud_score');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'name')) {
                $table->renameColumn('name', 'full_name');
            }
        });
    }

    public function down(): void
    {
    }
};
