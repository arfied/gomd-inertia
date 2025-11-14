<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalQuestionnaire extends Model
{
    protected $casts = [
        'data' => 'array',
    ];

    protected $fillable = [
        'cart_session_id',
        'user_id',
        'treatment_id',
        'subscription_plan_id',
        'cardiovascular_diagnosis',
        'cardiovascular_symptoms',
        'cardiovascular_medications',
        'cardiovascular_family',
        'cardiovascular_diet',
        'cardiovascular_lifestyle',
        'cardiovascular_monitoring',
        'neuro_diagnosis',
        'neuro_frequency',
        'neuro_triggers',
        'neuro_sleep',
        'neuro_daily_impact',
        'neuro_medications',
        'neuro_side_effects',
        'gi_symptoms',
        'gi_frequency',
        'gi_diet',
        'gi_medications',
        'gi_procedures',
        'gi_weight',
        'endocrine_diagnosis',
        'endocrine_symptoms',
        'endocrine_labs',
        'endocrine_medications',
        'endocrine_monitoring',
        'preventive_risk',
        'preventive_diet',
        'preventive_exercise',
        'preventive_screenings',
        'preventive_falls',
        'prophylaxis_history',
        'prophylaxis_risk',
        'prophylaxis_immunity',
        'prophylaxis_allergies',
        'prophylaxis_current',
        'skin_conditions',
        'skin_symptoms',
        'skin_triggers',
        'skin_treatments',
        'skin_impact',
        'immune_conditions',
        'immune_allergies',
        'immune_symptoms',
        'immune_treatments',
        'immune_triggers',
        'immune_emergency',
        'mh_symptoms_severity',
        'mh_sleep_patterns',
        'mh_concentration',
        'mh_support_system',
        'mh_coping_methods',
        'mh_suicidal_thoughts',
        'mh_treatment_history',
        'pain_location_type',
        'pain_frequency',
        'pain_severity',
        'pain_triggers',
        'pain_relief',
        'pain_impact',
        'pain_associated_symptoms',
        'respiratory_symptoms',
        'respiratory_triggers',
        'respiratory_sleep',
        'respiratory_exercise',
        'respiratory_treatments',
        'respiratory_smoking',
        'prevention_risk_factors',
        'prevention_history',
        'prevention_medications',
        'prevention_lifestyle',
        'prevention_monitoring',
        'prevention_family_history',
        'additional_symptoms',
        'quality_of_life',
        'treatment_goals',
        'treatment_preference',
        'medication_preference',
        'concerns',
        'weight_history',
        'current_weight_goals',
        'lifestyle_factors',
        'underlying_conditions',
        'previous_attempts',
        'weight_medication_history',
        'barriers_to_weight_loss',
        'family_weight_history',
        'status',
        'transaction_id',
        'amount',
        'data',
        'health_concerns',
        'symptoms',
        'symptom_duration',
        'previous_treatments',
        'medical_history',
        'additional_info',
        'medication_effectiveness',
        'medication_adherence',
        'medication_side_effects',
        'medication_interactions',
        'medication_refills',
        'medication_changes',
    ];

    public function cart()
    {
        return $this->hasMany(Cart::class, 'session_id', 'cart_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}
