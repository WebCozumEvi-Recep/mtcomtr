<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Domains Table
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain_name')->unique();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 2. Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Domain Product Pivot
        Schema::create('domain_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
        });

        // 4. Customers Table (Fraud Check Data)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->decimal('risk_score', 5, 2)->default(0.00); // Fraud score
            $table->boolean('is_blacklisted')->default(false);
            $table->timestamps();
        });

        // 5. Orders Table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->decimal('grand_total', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'cancelled', 'returned'])->default('pending');
            $table->decimal('fraud_score', 5, 2)->default(0.00);
            $table->text('order_notes')->nullable(); // Call center notes
            $table->timestamps();
        });

        // 6. Order Items Table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });

        // 7. Shipments Table (Kargo Mutabakatı)
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('carrier_company'); // Aras, Sendeo, PTT vb.
            $table->string('tracking_code')->nullable();
            $table->enum('status', ['prepared', 'in_transit', 'delivered', 'returned'])->default('prepared');
            $table->enum('reconciliation_status', ['unpaid', 'paid'])->default('unpaid'); // Tahsilat durumu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('domain_product');
        Schema::dropIfExists('products');
        Schema::dropIfExists('domains');
    }
};
