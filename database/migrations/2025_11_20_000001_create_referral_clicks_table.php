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
        Schema::create('referral_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_link_id')->constrained('referral_links')->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referrer_url')->nullable();
            $table->string('session_id')->nullable();
            $table->boolean('converted')->default(false);
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->index('referral_link_id');
            $table->index('converted');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_clicks');
    }
};

