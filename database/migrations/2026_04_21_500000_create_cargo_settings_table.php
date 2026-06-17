<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('carrier_name'); // aras, yurtici, mng etc.
            $table->string('display_name');
            $table->string('api_username')->nullable();
            $table->string('api_password')->nullable();
            $table->string('api_customer_code')->nullable();
            $table->string('api_key')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_test_mode')->default(true);
            $table->timestamps();
        });

        // Insert some defaults
        DB::table('cargo_settings')->insert([
            ['carrier_name' => 'aras', 'display_name' => 'Aras Kargo', 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
            ['carrier_name' => 'yurtici', 'display_name' => 'Yurtiçi Kargo', 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
            ['carrier_name' => 'mng', 'display_name' => 'MNG Kargo', 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
            ['carrier_name' => 'sendeo', 'display_name' => 'Sendeo', 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_settings');
    }
};
