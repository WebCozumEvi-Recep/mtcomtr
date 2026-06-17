<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upsell_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('offer_type'); // add_same_product, upgrade_package, complementary_product
            $table->foreignId('target_product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('target_package_id')->nullable()->constrained('offers')->onDelete('set null');
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2);
            $table->string('display_timing')->default('both'); // thank_you, operator, both
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upsell_offers');
    }
};
