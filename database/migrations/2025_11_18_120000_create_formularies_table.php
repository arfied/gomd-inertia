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
        Schema::create('formularies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('organization_id', 255)->nullable()->index();
            $table->string('type', 100)->nullable(); // e.g., 'insurance', 'hospital', 'clinical_protocol'
            $table->string('status', 50)->default('active')->index(); // active, inactive, archived
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formularies');
    }
};

