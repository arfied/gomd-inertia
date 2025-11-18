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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_id')->unique();
            $table->string('order_uuid');
            $table->text('shipping_address');
            $table->string('shipping_method')->default('standard');
            $table->string('tracking_number')->unique()->nullable();
            $table->string('status')->default('initiated');
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index('status');
            $table->index('order_uuid');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};

