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
        Schema::create('event_store', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('aggregate_uuid');
            $table->string('aggregate_type', 100);
            $table->string('event_type', 100);
            $table->json('event_data');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at', 6);

            $table->index(['aggregate_uuid', 'aggregate_type'], 'idx_aggregate');
            $table->index('event_type', 'idx_event_type');
            $table->index('occurred_at', 'idx_occurred_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_store');
    }
};

