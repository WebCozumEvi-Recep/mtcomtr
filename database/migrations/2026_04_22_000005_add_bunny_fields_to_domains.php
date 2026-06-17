<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table): void {
            $table->string('bunny_pullzone_id')->nullable()->after('cloudflare_account_id');
            $table->string('bunny_hostname')->nullable()->after('bunny_pullzone_id');
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table): void {
            $table->dropColumn(['bunny_pullzone_id', 'bunny_hostname']);
        });
    }
};
