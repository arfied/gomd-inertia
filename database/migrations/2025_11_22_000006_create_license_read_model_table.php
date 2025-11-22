<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_read_model', function (Blueprint $table) {
            $table->id();
            $table->string('license_uuid')->unique();
            $table->unsignedBigInteger('provider_id');
            $table->string('license_number')->unique();
            $table->string('license_type');
            $table->timestamp('verified_at');
            $table->timestamp('expires_at')->nullable();
            $table->string('issuing_body')->nullable();
            $table->string('verification_url')->nullable();
            $table->string('status')->default('verified');
            $table->timestamps();

            // Indexes for common queries
            $table->index('provider_id');
            $table->index('license_type');
            $table->index('status');
            $table->index('expires_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_read_model');
    }
};

