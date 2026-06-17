<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            if (!Schema::hasColumn('domains', 'cloudflare_zone_id')) {
                $table->string('cloudflare_zone_id')->nullable()->after('domain_name');
            }
            if (Schema::hasColumn('domains', 'status')) {
                $table->renameColumn('status', 'is_active');
            }
        });
    }

    public function down(): void
    {
    }
};
