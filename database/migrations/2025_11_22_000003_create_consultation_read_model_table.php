<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_read_model', function (Blueprint $table) {
            $table->id();
            $table->string('consultation_uuid')->unique();
            $table->string('patient_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->timestamp('scheduled_at');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('status');
            $table->index('scheduled_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_read_model');
    }
};

