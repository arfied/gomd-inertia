<?php

namespace App\Application\Patient;

use App\Domain\Patient\Events\PatientAllergyRecorded;
use App\Domain\Patient\Events\PatientConditionRecorded;
use App\Domain\Patient\Events\PatientMedicationAdded;
use App\Domain\Patient\Events\PatientVisitSummaryRecorded;
use App\Models\Allergy;
use App\Models\FamilyMedicalCondition;
use App\Models\FamilyMedicalHistory;
use App\Models\MedicalCondition;
use App\Models\MedicalSurgicalHistory;
use App\Models\MedicationHistory;

class EloquentPatientMedicalHistoryProjector implements PatientMedicalHistoryProjector
{
    public function projectAllergyRecorded(PatientAllergyRecorded $event): void
    {
        $userId = $event->payload['user_id'] ?? null;
        $allergen = $event->payload['allergen'] ?? null;

        if ($userId === null || $allergen === null) {
            return;
        }

        Allergy::create([
            'user_id' => $userId,
            'allergen' => $allergen,
            'reaction' => $event->payload['reaction'] ?? null,
            'severity' => $event->payload['severity'] ?? null,
            'notes' => $event->payload['notes'] ?? null,
        ]);
    }

    public function projectConditionRecorded(PatientConditionRecorded $event): void
    {
        $patientId = $event->payload['patient_id'] ?? null;
        $conditionName = $event->payload['condition_name'] ?? null;

        if ($patientId === null || $conditionName === null) {
            return;
        }

        MedicalCondition::create([
            'patient_id' => $patientId,
            'condition_name' => $conditionName,
            'diagnosed_at' => $event->payload['diagnosed_at'] ?? null,
            'notes' => $event->payload['notes'] ?? null,
            'had_condition_before' => $event->payload['had_condition_before'] ?? null,
            'is_chronic' => $event->payload['is_chronic'] ?? null,
        ]);
    }

    public function projectMedicationAdded(PatientMedicationAdded $event): void
    {
        $userId = $event->payload['user_id'] ?? null;
        $medicationId = $event->payload['medication_id'] ?? null;

        if ($userId === null || $medicationId === null) {
            return;
        }

        $history = new MedicationHistory();
        $history->user_id = $userId;
        $history->medication_id = $medicationId;
        $history->start_date = $event->payload['start_date'] ?? now()->toDateString();
        $history->end_date = $event->payload['end_date'] ?? null;
        $history->dosage = $event->payload['dosage'] ?? '';
        $history->frequency = $event->payload['frequency'] ?? '';
        $history->notes = $event->payload['notes'] ?? null;
        $history->save();
    }

    public function projectVisitSummaryRecorded(PatientVisitSummaryRecorded $event): void
    {
        $patientId = $event->payload['patient_id'] ?? null;

        if ($patientId === null) {
            return;
        }

        MedicalSurgicalHistory::updateOrCreate(
            ['patient_id' => $patientId],
            [
                'past_injuries' => (bool) ($event->payload['past_injuries'] ?? false),
                'past_injuries_details' => $event->payload['past_injuries_details'] ?? null,
                'surgery' => (bool) ($event->payload['surgery'] ?? false),
                'surgery_details' => $event->payload['surgery_details'] ?? null,
                'chronic_conditions_details' => $event->payload['chronic_conditions_details'] ?? null,
            ],
        );

        $family = FamilyMedicalHistory::updateOrCreate(
            ['patient_id' => $patientId],
            [
                'chronic_pain' => (bool) ($event->payload['chronic_pain'] ?? false),
                'chronic_pain_details' => $event->payload['chronic_pain_details'] ?? null,
            ],
        );

        $conditions = $event->payload['family_history_conditions'] ?? null;

        if ($family && is_array($conditions)) {
            $family->familyMedicalConditions()->delete();

            foreach ($conditions as $condition) {
                $name = is_array($condition) ? ($condition['name'] ?? null) : $condition;

                if (! is_string($name) || $name === '') {
                    continue;
                }

                $family->familyMedicalConditions()->create([
                    'name' => $name,
                ]);
            }
        }
    }
}

