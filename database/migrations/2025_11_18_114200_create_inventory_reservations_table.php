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
        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_id')->unique();
            $table->string('warehouse_id')->nullable();
            $table->string('status')->default('reserved');
            $table->json('medications');
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_reservations');
    }
};

