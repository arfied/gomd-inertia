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
        Schema::create('health_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('has_chronic_conditions')->default(false);
            $table->text('chronic_conditions')->nullable();
            $table->boolean('uses_tobacco_alcohol_drugs')->default(false);
            $table->text('substance_use_frequency')->nullable();
            $table->boolean('is_pregnant')->default(false);
            $table->boolean('had_recent_surgeries')->default(false);
            $table->text('recent_surgeries_details')->nullable();
            $table->boolean('has_health_concerns')->default(false);
            $table->text('health_concerns_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_questions');
    }
};
