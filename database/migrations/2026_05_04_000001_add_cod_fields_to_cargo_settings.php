<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargo_settings', function (Blueprint $table) {
            $table->string('api_cod_username')->nullable()->after('api_password');
            $table->string('api_cod_password')->nullable()->after('api_cod_username');
        });
    }

    public function down(): void
    {
        Schema::table('cargo_settings', function (Blueprint $table) {
            $table->dropColumn(['api_cod_username', 'api_cod_password']);
        });
    }
};
