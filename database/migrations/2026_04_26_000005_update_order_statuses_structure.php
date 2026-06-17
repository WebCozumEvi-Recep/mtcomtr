<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change enum to string to be flexible
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('yeni')->change();
        });

        // Migrate existing data
        DB::table('orders')->where('status', 'pending')->update(['status' => 'yeni']);
        DB::table('orders')->where('status', 'confirmed')->update(['status' => 'onaylandı']);
        DB::table('orders')->where('status', 'processing')->update(['status' => 'onaylandı']);
        DB::table('orders')->where('status', 'shipped')->update(['status' => 'kargoya_verildi']);
        DB::table('orders')->where('status', 'delivered')->update(['status' => 'teslim_edildi']);
        DB::table('orders')->where('status', 'cancelled')->update(['status' => 'iptal']);
        DB::table('orders')->where('status', 'returned')->update(['status' => 'iade']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled', 'returned'])->default('pending')->change();
        });
    }
};
