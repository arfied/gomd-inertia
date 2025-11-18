<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medication_search_index', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained('medications')->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('generic_name')->nullable()->index();
            $table->string('drug_class')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('type')->nullable()->index();
            $table->string('status', 50)->default('active')->index();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->boolean('requires_prescription')->default(true)->index();
            $table->boolean('controlled_substance')->default(false)->index();
            $table->timestamps();

            // Full-text search index for name and description (MySQL only)
            if (DB::getDriverName() === 'mysql') {
                $table->fullText(['name', 'generic_name', 'description']);
            }
            $table->index(['status', 'requires_prescription']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_search_index');
    }
};

