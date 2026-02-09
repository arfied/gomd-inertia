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
        Schema::create('questionnaire_responses', function (Blueprint $table) {
            $table->id();
            $table->string('response_uuid')->unique();
            $table->string('questionnaire_uuid');
            $table->string('patient_id')->nullable();
            $table->json('responses');
            $table->json('metadata')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            // Separate indexes for different query patterns
            $table->index('response_uuid');
            $table->index('questionnaire_uuid');
            $table->index('patient_id');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaire_responses');
    }
};

