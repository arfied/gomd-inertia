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
        Schema::table('medical_conditions', function (Blueprint $table) {
            $table->boolean('had_condition_before')->default(false)->after('condition_name');
            $table->boolean('is_chronic')->default(false)->after('had_condition_before');
        });
        Schema::table('preferred_medications', function (Blueprint $table) {
            $table->string('dosage')->nullable()->after('medication_name');
        });
        Schema::table('additional_information', function (Blueprint $table) {
            $table->boolean('needs_prescription_today')->default(false)->after('systemic_symptoms');
        });
    }
};
