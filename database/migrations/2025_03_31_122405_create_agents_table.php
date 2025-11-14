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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('company')->nullable();
            $table->string('agent_type'); // individual_agent, agency_owner, call_center, fmo
            $table->string('experience'); // new, 1-3, 3-5, 5+
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('agent_level')->default('field_agent'); // field_agent, agency, fmo, etc.
            $table->decimal('commission_rate', 5, 2)->default(50.00); // Default 50% commission
            $table->string('referral_code')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
