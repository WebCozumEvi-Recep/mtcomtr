<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cloudflare_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('cloudflare_accounts', 'account_identifier')) {
                $table->string('account_identifier', 64)->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cloudflare_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('cloudflare_accounts', 'account_identifier')) {
                $table->dropColumn('account_identifier');
            }
        });
    }
};
