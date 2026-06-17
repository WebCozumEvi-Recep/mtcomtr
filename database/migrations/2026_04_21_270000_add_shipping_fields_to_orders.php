<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('city')->nullable()->after('customer_id');
            $table->string('district')->nullable()->after('city');
            $table->text('address')->nullable()->after('district');
            $table->foreignId('offer_id')->nullable()->constrained()->onDelete('set null')->after('domain_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('offer_id');
            $table->dropColumn(['city', 'district', 'address']);
        });
    }
};
