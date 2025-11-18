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
        Schema::create('formulary_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulary_id')->constrained('formularies')->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->string('tier', 50)->nullable(); // e.g., 'preferred', 'non-preferred', 'specialty'
            $table->boolean('requires_pre_authorization')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['formulary_id', 'medication_id']);
            $table->index(['formulary_id', 'tier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulary_medications');
    }
};

