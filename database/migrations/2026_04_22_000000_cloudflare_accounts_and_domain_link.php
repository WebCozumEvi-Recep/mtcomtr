<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cloudflare_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('api_token');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('domains', function (Blueprint $table) {
            if (! Schema::hasColumn('domains', 'cloudflare_account_id')) {
                $table->foreignId('cloudflare_account_id')
                    ->nullable()
                    ->constrained('cloudflare_accounts')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            if (Schema::hasColumn('domains', 'cloudflare_account_id')) {
                $table->dropForeign(['cloudflare_account_id']);
                $table->dropColumn('cloudflare_account_id');
            }
        });

        Schema::dropIfExists('cloudflare_accounts');
    }
};
