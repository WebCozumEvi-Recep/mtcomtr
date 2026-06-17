<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Funnel Configs (SEO, Design)
        Schema::create('funnel_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('primary_color')->default('#16a34a'); // Brand Green
            $table->string('secondary_color')->default('#14532d'); // Dark Green
            $table->text('header_scripts')->nullable(); // Pixel, Analytics
            $table->text('footer_scripts')->nullable();
            $table->timestamps();
        });

        // 2. Offers (Pricing Packages)
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->onDelete('cascade');
            $table->string('offer_name'); // e.g. 2 Adet Alana 1 Bedava
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
        Schema::dropIfExists('funnel_configs');
    }
};
