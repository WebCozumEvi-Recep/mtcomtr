<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('info'); // success, warning, danger, info
            $table->string('title')->nullable();
            $table->text('message');
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->json('data')->nullable(); // For details like deleted item name, etc.
            $table->timestamps();

            $table->foreign('causer_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('user_alert_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('system_alert_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('system_alert_id')->references('id')->on('system_alerts')->onDelete('cascade');
            
            $table->unique(['user_id', 'system_alert_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_alert_statuses');
        Schema::dropIfExists('system_alerts');
    }
};
