<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medical_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users');
            $table->string('condition_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medical_conditions');
    }
};
