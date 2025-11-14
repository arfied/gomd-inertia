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
        Schema::create('additional_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users');
            $table->text('additional_concerns')->nullable();
            $table->text('daily_activities_impact')->nullable();
            $table->text('sleep_impact')->nullable();
            $table->text('mobility_impact')->nullable();
            $table->text('emotional_impact')->nullable();
            $table->text('associated_symptoms')->nullable(); // 'Numbness', 'Tingling', 'Weakness', 'Swelling', 'Redness', 'Stiffness', 'Other'
            $table->boolean('systemic_symptoms')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_information');
    }
};
