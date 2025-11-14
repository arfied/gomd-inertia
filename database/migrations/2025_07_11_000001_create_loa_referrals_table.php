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
        Schema::create('loa_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loa_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('target_agent_id')->nullable()->constrained('agents')->onDelete('set null');
            $table->string('referral_type');
            $table->string('referral_email');
            $table->string('referral_name')->nullable();
            $table->string('referral_phone')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->string('tracking_code')->unique();
            $table->string('referral_url')->nullable();
            $table->timestamp('follow_up_date')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->json('conversion_data')->nullable();
            $table->timestamps();

            $table->index(['loa_user_id', 'status']);
            $table->index(['referral_type', 'status']);
            $table->index('tracking_code');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loa_referrals');
    }
};
