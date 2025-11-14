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
        Schema::create('medication_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('medication_id')->nullable()->constrained()->onDelete('set null');
            $table->string('custom_medication_name')->nullable();
            $table->text('custom_medication_details')->nullable();
            $table->string('requested_dosage')->nullable();
            $table->string('requested_quantity')->nullable();
            $table->string('status')->default('pending'); // More flexible than enum for future status additions
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_order_items');
    }
};
