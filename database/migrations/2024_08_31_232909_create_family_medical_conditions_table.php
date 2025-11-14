<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('family_medical_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_medical_history_id')->constrained('family_medical_histories')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('family_medical_conditions');
    }
};
