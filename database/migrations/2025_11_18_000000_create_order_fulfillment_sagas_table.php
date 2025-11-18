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
        Schema::create('order_fulfillment_sagas', function (Blueprint $table) {
            $table->id();
            $table->uuid('saga_uuid')->unique();
            $table->uuid('order_uuid');
            $table->string('state')->default('PENDING_PRESCRIPTION');
            $table->json('compensation_stack')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('state');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_fulfillment_sagas');
    }
};

