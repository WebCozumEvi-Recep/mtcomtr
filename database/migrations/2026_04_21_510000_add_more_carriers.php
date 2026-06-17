<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cargo_settings')->insert([
            ['carrier_name' => 'ptt', 'display_name' => 'PTT Kargo', 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
            ['carrier_name' => 'dhl', 'display_name' => 'DHL Express', 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('cargo_settings')->whereIn('carrier_name', ['ptt', 'dhl'])->delete();
    }
};
