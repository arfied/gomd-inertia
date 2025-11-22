<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_read_model', function (Blueprint $table) {
            $table->id();
            $table->string('questionnaire_uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('questions')->nullable();
            $table->json('responses')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('patient_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index('patient_id');
            $table->index('created_by');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_read_model');
    }
};

