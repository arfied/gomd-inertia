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
        Schema::create('medication_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users'); 
            $table->boolean('celecoxib_bleeding')->default(false);
            $table->boolean('celecoxib_kidney')->default(false);
            $table->boolean('duloxetine_liver')->default(false);
            $table->boolean('duloxetine_maois')->default(false);
            $table->boolean('baclofen_kidney')->default(false);
            $table->boolean('cyclobenzaprine_cardio')->default(false);
            $table->boolean('methocarbamol_liver')->default(false);
            $table->boolean('meloxicam_bleeding')->default(false);
            $table->boolean('meloxicam_kidney')->default(false);
            $table->boolean('amitriptyline_cardio')->default(false);
            $table->boolean('amitriptyline_maois')->default(false);
            $table->boolean('etodolac_bleeding')->default(false);
            $table->boolean('etodolac_liver')->default(false);
            $table->boolean('venlafaxine_liver')->default(false);
            $table->boolean('venlafaxine_maois')->default(false);
            $table->boolean('tizanidine_liver')->default(false);
            $table->boolean('tizanidine_cns')->default(false);
            $table->boolean('orphenadrine_liver')->default(false);
            $table->boolean('nabumetone_bleeding')->default(false);
            $table->boolean('nabumetone_liver')->default(false);
            $table->boolean('nortriptyline_cardio')->default(false);
            $table->boolean('nortriptyline_maois')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_screenings');
    }
};
