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
        Schema::create('medical_surgical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users'); 
            $table->boolean('past_injuries')->default(false);
            $table->text('past_injuries_details')->nullable();
            $table->boolean('surgery')->default(false);
            $table->text('surgery_details')->nullable();
            $table->text('chronic_conditions_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_surgical_histories');
    }
};
