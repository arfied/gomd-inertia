<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_note_read_model', function (Blueprint $table) {
            $table->id();
            $table->string('clinical_note_uuid')->unique();
            $table->string('patient_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->string('note_type');
            $table->longText('content');
            $table->json('attachments')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            // Indexes for common queries
            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('note_type');
            $table->index('recorded_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_note_read_model');
    }
};

