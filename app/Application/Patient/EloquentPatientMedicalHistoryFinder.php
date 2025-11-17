<?php

namespace App\Application\Patient;

use App\Models\Allergy;
use App\Models\FamilyMedicalHistory;
use App\Models\MedicalCondition;
use App\Models\MedicalSurgicalHistory;
use App\Models\MedicationHistory;

class EloquentPatientMedicalHistoryFinder implements PatientMedicalHistoryFinder
{
    public function findByUserId(int $userId): array
    {
        $allergies = Allergy::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get()
            ->map(function (Allergy $allergy): array {
                return [
                    'id' => $allergy->id,
                    'allergen' => $allergy->allergen,
                    'reaction' => $allergy->reaction,
                    'severity' => $allergy->severity,
                    'notes' => $allergy->notes,
                ];
            })
            ->all();

        $conditions = MedicalCondition::query()
            ->where('patient_id', $userId)
            ->orderByDesc('diagnosed_at')
            ->orderByDesc('id')
            ->get()
            ->map(function (MedicalCondition $condition): array {
                return [
                    'id' => $condition->id,
                    'condition_name' => $condition->condition_name,
                    'diagnosed_at' => $condition->diagnosed_at,
                    'had_condition_before' => (bool) $condition->had_condition_before,
                    'is_chronic' => (bool) $condition->is_chronic,
                    'notes' => $condition->notes,
                ];
            })
            ->all();

        $medications = MedicationHistory::query()
            ->where('user_id', $userId)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get()
            ->map(function (MedicationHistory $history): array {
                return [
                    'id' => $history->id,
                    'medication_id' => $history->medication_id,
                    'medication_name' => null,
                    'start_date' => $history->start_date,
                    'end_date' => $history->end_date,
                    'dosage' => $history->dosage,
                    'frequency' => $history->frequency,
                    'notes' => $history->notes,
                ];
            })
            ->all();

        $surgical = MedicalSurgicalHistory::query()
            ->where('patient_id', $userId)
            ->orderByDesc('id')
            ->first();

        $surgicalHistory = $surgical ? [
            'past_injuries' => (bool) $surgical->past_injuries,
            'past_injuries_details' => $surgical->past_injuries_details,
            'surgery' => (bool) $surgical->surgery,
            'surgery_details' => $surgical->surgery_details,
            'chronic_conditions_details' => $surgical->chronic_conditions_details,
        ] : null;

        $family = FamilyMedicalHistory::query()
            ->with('familyMedicalConditions')
            ->where('patient_id', $userId)
            ->orderByDesc('id')
            ->first();

        $familyHistory = $family ? [
            'chronic_pain' => (bool) $family->chronic_pain,
            'chronic_pain_details' => $family->chronic_pain_details,
            'conditions' => $family->familyMedicalConditions
                ->map(fn ($condition): array => [
                    'id' => $condition->id,
                    'name' => $condition->name,
                ])
                ->all(),
        ] : null;

        return [
            'allergies' => $allergies,
            'conditions' => $conditions,
            'medications' => $medications,
            'surgical_history' => $surgicalHistory,
            'family_history' => $familyHistory,
        ];
    }
}

