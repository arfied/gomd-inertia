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
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('specialties')->nullable();
            $table->string('primary_license_state')->nullable();
            $table->string('primary_license_number')->nullable();
            $table->string('dea_number')->nullable();
            $table->text('licensed_states')->nullable();
            $table->string('availability')->nullable();
            $table->integer('hourly_rate')->default(0);
            $table->integer('lowest_consult_rate')->default(0);
            $table->text('consultation_types')->nullable();
            $table->json('hours_of_availability')->nullable();
            $table->boolean('interstate_license_registered')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
