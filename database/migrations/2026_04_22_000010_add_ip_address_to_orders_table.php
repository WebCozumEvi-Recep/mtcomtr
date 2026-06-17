<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $col) {
            $col->string('ip_address')->nullable()->after('customer_id');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $col) {
            $col->dropColumn('ip_address');
        });
    }
};
