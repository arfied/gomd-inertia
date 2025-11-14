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
        Schema::table('medical_questionnaires', function (Blueprint $table) {
            // Add medication-related fields
            $table->text('medication_effectiveness')->nullable();
            $table->text('medication_adherence')->nullable();
            $table->text('medication_side_effects')->nullable();
            $table->text('medication_interactions')->nullable();
            $table->text('medication_refills')->nullable();
            $table->text('medication_changes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_questionnaires', function (Blueprint $table) {
            $table->dropColumn([
                'medication_effectiveness',
                'medication_adherence',
                'medication_side_effects',
                'medication_interactions',
                'medication_refills',
                'medication_changes'
            ]);
        });
    }
};
