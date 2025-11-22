<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_read_model', function (Blueprint $table) {
            $table->id();
            $table->string('consent_uuid')->unique();
            $table->string('patient_id');
            $table->string('consent_type');
            $table->unsignedBigInteger('granted_by');
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable();
            $table->string('terms_version')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            // Indexes for common queries
            $table->index('patient_id');
            $table->index('consent_type');
            $table->index('status');
            $table->index('expires_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_read_model');
    }
};

