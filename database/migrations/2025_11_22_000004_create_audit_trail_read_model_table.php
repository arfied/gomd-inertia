<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trail_read_model', function (Blueprint $table) {
            $table->id();
            $table->string('audit_uuid')->unique();
            $table->string('patient_id');
            $table->unsignedBigInteger('accessed_by');
            $table->string('access_type');
            $table->string('resource');
            $table->timestamp('accessed_at');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index('patient_id');
            $table->index('accessed_by');
            $table->index('access_type');
            $table->index('accessed_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trail_read_model');
    }
};

