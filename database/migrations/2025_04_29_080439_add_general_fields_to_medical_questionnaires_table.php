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
            $table->text('health_concerns')->nullable();
            $table->text('symptoms')->nullable();
            $table->string('symptom_duration')->nullable();
            $table->text('previous_treatments')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('additional_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_questionnaires', function (Blueprint $table) {
            $table->dropColumn([
                'health_concerns',
                'symptoms',
                'symptom_duration',
                'previous_treatments',
                'medical_history',
                'additional_info'
            ]);
        });
    }
};
