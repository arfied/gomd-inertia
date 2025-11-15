<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_enrollments', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->uuid('patient_uuid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('source')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('enrolled_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
            $table->index('patient_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_enrollments');
    }
};

