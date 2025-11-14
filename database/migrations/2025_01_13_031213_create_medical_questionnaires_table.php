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
        Schema::create('medical_questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('cart_session_id');
            $table->foreignId('user_id')->constrained();

            // Cardiovascular
            $table->text('cardiovascular_diagnosis')->nullable();
            $table->text('cardiovascular_symptoms')->nullable();
            $table->text('cardiovascular_medications')->nullable(); 
            $table->text('cardiovascular_family')->nullable();
            $table->text('cardiovascular_diet')->nullable();
            $table->text('cardiovascular_lifestyle')->nullable();
            $table->text('cardiovascular_monitoring')->nullable();

            // Neurological 
            $table->text('neuro_diagnosis')->nullable();
            $table->text('neuro_frequency')->nullable();
            $table->text('neuro_triggers')->nullable();
            $table->text('neuro_sleep')->nullable();
            $table->text('neuro_daily_impact')->nullable();
            $table->text('neuro_medications')->nullable();
            $table->text('neuro_side_effects')->nullable();

            // Gastrointestinal
            $table->text('gi_symptoms')->nullable(); 
            $table->text('gi_frequency')->nullable();
            $table->text('gi_diet')->nullable();
            $table->text('gi_medications')->nullable();
            $table->text('gi_procedures')->nullable();
            $table->text('gi_weight')->nullable();

            // Endocrine
            $table->text('endocrine_diagnosis')->nullable();
            $table->text('endocrine_symptoms')->nullable();
            $table->text('endocrine_labs')->nullable();
            $table->text('endocrine_medications')->nullable();
            $table->text('endocrine_monitoring')->nullable();

            // Preventive
            $table->text('preventive_risk')->nullable();
            $table->text('preventive_diet')->nullable();
            $table->text('preventive_exercise')->nullable();
            $table->text('preventive_screenings')->nullable();
            $table->text('preventive_falls')->nullable();

            // Prophylaxis
            $table->text('prophylaxis_history')->nullable();
            $table->text('prophylaxis_risk')->nullable();
            $table->text('prophylaxis_immunity')->nullable();
            $table->text('prophylaxis_allergies')->nullable();
            $table->text('prophylaxis_current')->nullable();

            // Skin
            $table->text('skin_conditions')->nullable();
            $table->text('skin_symptoms')->nullable();
            $table->text('skin_triggers')->nullable();
            $table->text('skin_treatments')->nullable();
            $table->text('skin_impact')->nullable();

            // Immunology
            $table->text('immune_conditions')->nullable();
            $table->text('immune_allergies')->nullable();
            $table->text('immune_symptoms')->nullable();
            $table->text('immune_treatments')->nullable();
            $table->text('immune_triggers')->nullable();
            $table->text('immune_emergency')->nullable();

            // Mental Health
            $table->text('mh_symptoms_severity')->nullable();
            $table->text('mh_sleep_patterns')->nullable();
            $table->text('mh_concentration')->nullable();
            $table->text('mh_support_system')->nullable();
            $table->text('mh_coping_methods')->nullable();
            $table->text('mh_suicidal_thoughts')->nullable();
            $table->text('mh_treatment_history')->nullable();

            // Pain & Inflammation
            $table->text('pain_location_type')->nullable();
            $table->text('pain_frequency')->nullable();
            $table->text('pain_severity')->nullable();
            $table->text('pain_triggers')->nullable();
            $table->text('pain_relief')->nullable();
            $table->text('pain_impact')->nullable();
            $table->text('pain_associated_symptoms')->nullable();

            // Respiratory
            $table->text('respiratory_symptoms')->nullable();
            $table->text('respiratory_triggers')->nullable();
            $table->text('respiratory_sleep')->nullable();
            $table->text('respiratory_exercise')->nullable();
            $table->text('respiratory_treatments')->nullable();
            $table->text('respiratory_smoking')->nullable();

            // Prevention
            $table->text('prevention_risk_factors')->nullable();
            $table->text('prevention_history')->nullable();
            $table->text('prevention_medications')->nullable();
            $table->text('prevention_lifestyle')->nullable();
            $table->text('prevention_monitoring')->nullable();
            $table->text('prevention_family_history')->nullable();

            // Additional
            $table->text('additional_symptoms')->nullable();
            $table->text('quality_of_life')->nullable();
            $table->text('treatment_goals')->nullable();
            $table->text('concerns')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_questionnaires');
    }
};
