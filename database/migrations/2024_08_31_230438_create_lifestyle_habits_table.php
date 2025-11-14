<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lifestyle_habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users');
            $table->boolean('alcohol_use');
            $table->text('alcohol_details')->nullable();
            $table->boolean('tobacco_use');
            $table->text('tobacco_details')->nullable();
            $table->boolean('drug_use');
            $table->text('drug_details')->nullable();
            $table->string('exercise_frequency');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lifestyle_habits');
    }
};
