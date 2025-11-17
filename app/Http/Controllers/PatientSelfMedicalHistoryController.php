<?php

namespace App\Http\Controllers;

use App\Application\Commands\CommandBus;
use App\Application\Patient\Commands\RecordPatientAllergy;
use App\Application\Patient\Commands\RecordPatientCondition;
use App\Application\Patient\Commands\RecordPatientMedication;
use App\Application\Patient\Commands\RecordPatientVisitSummary;
use App\Application\Patient\PatientMedicalHistoryFinder;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserId;
use App\Application\Queries\QueryBus;
use App\Models\PatientEnrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientSelfMedicalHistoryController extends Controller
{
    public function show(Request $request, PatientMedicalHistoryFinder $historyFinder): JsonResponse
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $snapshot = $historyFinder->findByUserId($authUser->id);

        return response()->json([
            'medical_history' => $snapshot,
        ]);
    }

    public function storeAllergy(
        Request $request,
        QueryBus $queryBus,
        CommandBus $commandBus,
        PatientMedicalHistoryFinder $historyFinder,
    ): JsonResponse {
        /** @var User $authUser */
        $authUser = $request->user();

        $data = $request->validate([
            'allergen' => ['required', 'string', 'max:255'],
            'reaction' => ['nullable', 'string', 'max:255'],
            'severity' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var PatientEnrollment|null $enrollment */
        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByUserId($authUser->id)
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json([
                'message' => 'Patient is not enrolled.',
            ], 422);
        }

        $command = new RecordPatientAllergy(
            patientUuid: $enrollment->patient_uuid,
            userId: $authUser->id,
            allergen: $data['allergen'],
            reaction: $data['reaction'] ?? null,
            severity: $data['severity'] ?? null,
            notes: $data['notes'] ?? null,
            metadata: [
                'source' => 'patient',
                'actor_user_id' => $authUser->id,
            ],
        );

        $commandBus->dispatch($command);

        $snapshot = $historyFinder->findByUserId($authUser->id);

        return response()->json([
            'medical_history' => $snapshot,
        ], 201);
    }

    public function storeCondition(
        Request $request,
        QueryBus $queryBus,
        CommandBus $commandBus,
        PatientMedicalHistoryFinder $historyFinder,
    ): JsonResponse {
        /** @var User $authUser */
        $authUser = $request->user();

        $data = $request->validate([
            'condition_name' => ['required', 'string', 'max:255'],
            'diagnosed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'had_condition_before' => ['nullable', 'boolean'],
            'is_chronic' => ['nullable', 'boolean'],
        ]);

        /** @var PatientEnrollment|null $enrollment */
        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByUserId($authUser->id)
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json([
                'message' => 'Patient is not enrolled.',
            ], 422);
        }

        $command = new RecordPatientCondition(
            patientUuid: $enrollment->patient_uuid,
            userId: $authUser->id,
            conditionName: $data['condition_name'],
            diagnosedAt: isset($data['diagnosed_at']) ? (string) $data['diagnosed_at'] : null,
            notes: $data['notes'] ?? null,
            hadConditionBefore: array_key_exists('had_condition_before', $data) ? (bool) $data['had_condition_before'] : null,
            isChronic: array_key_exists('is_chronic', $data) ? (bool) $data['is_chronic'] : null,
            metadata: [
                'source' => 'patient',
                'actor_user_id' => $authUser->id,
            ],
        );

        $commandBus->dispatch($command);

        $snapshot = $historyFinder->findByUserId($authUser->id);

        return response()->json([
            'medical_history' => $snapshot,
        ], 201);
    }

    public function storeMedication(
        Request $request,
        QueryBus $queryBus,
        CommandBus $commandBus,
        PatientMedicalHistoryFinder $historyFinder,
    ): JsonResponse {
        /** @var User $authUser */
        $authUser = $request->user();

        $data = $request->validate([
            'medication_id' => ['required', 'integer'],
            'dosage' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var PatientEnrollment|null $enrollment */
        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByUserId($authUser->id)
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json([
                'message' => 'Patient is not enrolled.',
            ], 422);
        }

        $command = new RecordPatientMedication(
            patientUuid: $enrollment->patient_uuid,
            userId: $authUser->id,
            medicationId: (int) $data['medication_id'],
            dosage: $data['dosage'],
            frequency: $data['frequency'],
            startDate: isset($data['start_date']) ? (string) $data['start_date'] : null,
            endDate: isset($data['end_date']) ? (string) $data['end_date'] : null,
            notes: $data['notes'] ?? null,
            metadata: [
                'source' => 'patient',
                'actor_user_id' => $authUser->id,
            ],
        );

        $commandBus->dispatch($command);

        $snapshot = $historyFinder->findByUserId($authUser->id);

        return response()->json([
            'medical_history' => $snapshot,
        ], 201);
    }

    public function storeVisitSummary(
        Request $request,
        QueryBus $queryBus,
        CommandBus $commandBus,
        PatientMedicalHistoryFinder $historyFinder,
    ): JsonResponse {
        /** @var User $authUser */
        $authUser = $request->user();

        $data = $request->validate([
            'past_injuries' => ['required', 'boolean'],
            'past_injuries_details' => ['nullable', 'string'],
            'surgery' => ['required', 'boolean'],
            'surgery_details' => ['nullable', 'string'],
            'chronic_conditions_details' => ['nullable', 'string'],
            'chronic_pain' => ['required', 'boolean'],
            'chronic_pain_details' => ['nullable', 'string'],
            'family_history_conditions' => ['nullable', 'array'],
            'family_history_conditions.*' => ['string'],
        ]);

        /** @var PatientEnrollment|null $enrollment */
        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByUserId($authUser->id)
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json([
                'message' => 'Patient is not enrolled.',
            ], 422);
        }

        $command = new RecordPatientVisitSummary(
            patientUuid: $enrollment->patient_uuid,
            userId: $authUser->id,
            pastInjuries: (bool) $data['past_injuries'],
            pastInjuriesDetails: $data['past_injuries_details'] ?? null,
            surgery: (bool) $data['surgery'],
            surgeryDetails: $data['surgery_details'] ?? null,
            chronicConditionsDetails: $data['chronic_conditions_details'] ?? null,
            chronicPain: (bool) $data['chronic_pain'],
            chronicPainDetails: $data['chronic_pain_details'] ?? null,
            familyHistoryConditions: $data['family_history_conditions'] ?? [],
            metadata: [
                'source' => 'patient',
                'actor_user_id' => $authUser->id,
            ],
        );

        $commandBus->dispatch($command);

        $snapshot = $historyFinder->findByUserId($authUser->id);

        return response()->json([
            'medical_history' => $snapshot,
        ]);
    }
}

