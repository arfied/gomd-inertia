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
        Schema::create('dispensed_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispense_record_id')->constrained();
            $table->foreignId('prescription_item_id')->constrained('prescription_items');
            $table->integer('quantity_dispensed');
            $table->boolean('fully_dispensed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensed_items');
    }
};
