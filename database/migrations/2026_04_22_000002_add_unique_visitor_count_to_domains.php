<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('domains', 'unique_visitor_count')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->unsignedBigInteger('unique_visitor_count')->default(0)->after('visitor_count');
            });
        }
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn('unique_visitor_count');
        });
    }
};
