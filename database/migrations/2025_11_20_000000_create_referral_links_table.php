<?php

use App\Enums\ReferralType;
use App\Enums\ReferralLinkStatus;
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
        Schema::create('referral_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->string('referral_type')->default(ReferralType::Patient->value);
            $table->string('referral_code')->unique();
            $table->uuid('referral_token')->unique();
            $table->integer('clicks_count')->default(0);
            $table->integer('conversions_count')->default(0);
            $table->float('conversion_rate')->default(0);
            $table->string('status')->default(ReferralLinkStatus::Active->value);
            $table->timestamps();

            $table->index('agent_id');
            $table->index('referral_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_links');
    }
};

