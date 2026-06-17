<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_upsells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('upsell_offer_id')->constrained('upsell_offers')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, accepted, rejected
            $table->decimal('old_total', 10, 2);
            $table->decimal('new_total', 10, 2);
            $table->decimal('added_amount', 10, 2);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_upsells');
    }
};
