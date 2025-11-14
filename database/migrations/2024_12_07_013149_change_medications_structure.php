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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->integer('level')->notNullable();
        });

        Schema::create('conditions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('therapeutic_use', 200);
        });

        Schema::create('category_conditions', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('condition_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'condition_id']);
        });

        // Schema::create('drug_classes', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name')->unique();
        // });

        // Schema::create('dosage_forms', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name')->unique();
        // });

        Schema::create('medication_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description', 200)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('medication_classes')->nullOnDelete();
        });

        Schema::create('medication_bases', function (Blueprint $table) {
            $table->id();
            $table->string('generic_name', 100)->unique();
            $table->foreignId('medication_class_id')->constrained()->onDelete('cascade');
            $table->string('type', 50)->index();
            $table->text('description')->nullable();
            $table->boolean('requires_prescription');
            $table->boolean('controlled_substance');
            $table->text('contraindications')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('interactions')->nullable();
            $table->char('pregnancy_category', 1)->nullable();
            $table->boolean('breastfeeding_safe');
            $table->text('black_box_warning')->nullable();
            $table->string('status', 20)->index();
        });

        // Create medication_variants table
        Schema::create('medication_variants', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name', 100)->index();
            $table->string('manufacturer', 100)->nullable();
            $table->foreignId('medication_base_id')->constrained()->onDelete('cascade');
            $table->string('strength', 50)->nullable();
            $table->string('dosage_form', 50)->nullable();
            $table->string('route_of_administration', 50)->nullable();
            $table->string('ndc_number', 20)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->text('storage_conditions')->nullable();
            $table->boolean('is_usual_dosage')->default(false);
            $table->integer('order_index')->nullable()->index();
            $table->unique(['brand_name', 'dosage_form', 'strength']);
        });

        // Create primary_uses table
        Schema::create('primary_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_base_id')->constrained()->onDelete('cascade');
            $table->foreignId('condition_id')->constrained()->onDelete('cascade');
            $table->text('primary_treatment')->nullable();
            $table->text('mechanism_of_action')->nullable();
        });

        // Create off_label_uses table
        Schema::create('off_label_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_base_id')->constrained()->onDelete('cascade');
            $table->foreignId('condition_id')->constrained()->onDelete('cascade');
            $table->text('primary_treatment')->nullable();
            $table->text('mechanism_of_action')->nullable();
        });

        // Create dosage_information table
        Schema::create('dosage_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_base_id')->constrained()->onDelete('cascade');
            $table->string('use_type', 50);
            $table->string('starting_dose', 100);
            $table->text('other_dosages')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosage_information');
        Schema::dropIfExists('off_label_uses');
        Schema::dropIfExists('primary_uses');
        Schema::dropIfExists('medication_variants');
        Schema::dropIfExists('medication_bases');
        Schema::dropIfExists('medication_classes');
        // Schema::dropIfExists('dosage_forms');
        // Schema::dropIfExists('drug_classes');
        Schema::dropIfExists('category_conditions');
        Schema::dropIfExists('conditions');
        Schema::dropIfExists('categories');
    }
};
