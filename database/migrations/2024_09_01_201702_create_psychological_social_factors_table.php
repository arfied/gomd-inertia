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
        Schema::create('psychological_social_factors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users'); 
            $table->integer('stress_levels'); // 1-10
            $table->boolean('support_system')->default(false);
            $table->boolean('work_environment')->default(false);
            $table->text('mental_health_changes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psychological_social_factors');
    }
};
