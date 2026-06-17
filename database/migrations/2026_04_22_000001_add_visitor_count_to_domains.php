<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->unsignedBigInteger('visitor_count')->default(0)->after('ssl_active');
            $table->unsignedBigInteger('unique_visitor_count')->default(0)->after('visitor_count');
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn(['visitor_count', 'unique_visitor_count']);
        });
    }
};
