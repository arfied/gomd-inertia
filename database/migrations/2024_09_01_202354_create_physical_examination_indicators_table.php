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
        Schema::create('physical_examination_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users'); 
            $table->boolean('tenderness')->default(false);
            $table->boolean('difficulty_moving')->default(false);
            $table->boolean('reduced_activity')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_examination_indicators');
    }
};
