<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mental_health_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users');
            $table->boolean('mental_health_conditions');
            $table->text('mental_health_details')->nullable();
            $table->boolean('suicidal_thoughts');
            $table->boolean('receiving_therapy');
            $table->boolean('benefit_from_counseling');
            $table->boolean('worried_about_counseling');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mental_health_assessments');
    }
};
