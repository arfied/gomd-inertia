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
            $table->text('weight_history')->nullable()->after('concerns');
            $table->text('current_weight_goals')->nullable()->after('weight_history');
            $table->text('lifestyle_factors')->nullable()->after('current_weight_goals');
            $table->text('underlying_conditions')->nullable()->after('lifestyle_factors');
            $table->text('previous_attempts')->nullable()->after('underlying_conditions');
            $table->text('weight_medication_history')->nullable()->after('previous_attempts');
            $table->text('barriers_to_weight_loss')->nullable()->after('weight_medication_history');
            $table->text('family_weight_history')->nullable()->after('barriers_to_weight_loss');
        });
    }
};
