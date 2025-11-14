<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->boolean('referral_notifications')->default(true);
            $table->boolean('commission_notifications')->default(true);
            $table->boolean('payment_notifications')->default(true);
            $table->boolean('status_notifications')->default(true);
            $table->enum('notification_frequency', ['immediate', 'daily', 'weekly'])->default('immediate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
