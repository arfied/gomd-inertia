<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symptoms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the symptom (e.g., "Cough", "Fever")
            $table->timestamps();
        });
        
        Schema::create('condition_symptom', function (Blueprint $table) {
            $table->foreignId('condition_id');
            $table->foreignId('symptom_id');
            $table->primary(['condition_id', 'symptom_id']);
        });
        
        Schema::create('patient_symptom', function (Blueprint $table) {
            $table->foreignId('patient_id');
            $table->string('symptom');
            $table->primary(['patient_id', 'symptom']);
        });
    }
};
