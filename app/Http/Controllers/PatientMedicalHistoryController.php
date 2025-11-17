<?php

namespace App\Http\Controllers;

use App\Application\Commands\CommandBus;
use App\Application\Patient\Commands\RecordPatientAllergy;
use App\Application\Patient\Commands\RecordPatientCondition;
use App\Application\Patient\Commands\RecordPatientMedication;
use App\Application\Patient\Commands\RecordPatientVisitSummary;
use App\Application\Patient\PatientMedicalHistoryFinder;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuid;
use App\Application\Queries\QueryBus;
use App\Models\PatientEnrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientMedicalHistoryController extends Controller
{
    public function storeAllergy(
        string $patientUuid,
        Request $request,
        QueryBus $queryBus,
        CommandBus $commandBus,
        PatientMedicalHistoryFinder $historyFinder,
    ): JsonResponse {
        /** @var User|null $authUser */
        $authUser = $request->user();

        abort_unless(
            $authUser && ($authUser->hasAnyRole(['admin', 'staff']) || in_array($authUser->role, ['admin', 'staff'], true)),
            403
        );

        $data = $request->validate([
            'allergen' => ['required', 'string', 'max:255'],
            'reaction' => ['nullable', 'string', 'max:255'],
            'severity' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var PatientEnrollment|null $enrollment */
        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByPatientUuid($patientUuid)
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json([
                'message' => 'Patient not found.',
            ], 404);
        }

        $command = new RecordPatientAllergy(
            patientUuid: $enrollment->patient_uuid,
            userId: $enrollment->user_id,
            allergen: $data['allergen'],
            reaction: $data['reaction'] ?? null,
            severity: $data['severity'] ?? null,
            notes: $data['notes'] ?? null,
            metadata: [
                'source' => 'manual',
                'actor_user_id' => $authUser->id,
            ],
        );

        $commandBus->dispatch($command);

        $snapshot = $historyFinder->findByUserId($enrollment->user_id);

        return response()->json([
            'medical_history' => $snapshot,
        ], 201);
    }

    public function storeCondition(
        string $patientUuid,
        Request $request,
        QueryBus $queryBus,
        CommandBus $commandBus,
        PatientMedicalHistoryFinder $historyFinder,
    ): JsonResponse {
        /** @var User|null $authUser */
        $authUser = $request->user();

        abort_unless(
            $authUser && ($authUser->hasAnyRole(['admin', 'staff']) || in_array($authUser->role, ['admin', 'staff'], true)),
            403
        );

        $data = $request->validate([
            'condition_name' => ['required', 'string', 'max:255'],
            'diagnosed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'had_condition_before' => ['nullable', 'boolean'],
            'is_chronic' => ['nullable', 'boolean'],
        ]);

        /** @var PatientEnrollment|null $enrollment */
        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByPatientUuid($patientUuid)
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json([
                'message' => 'Patient not found.',
            ], 404);
        }

        $command = new RecordPatientCondition(
            patientUuid: $enrollment->patient_uuid,
            userId: $enrollment->user_id,
            conditionName: $data['condition_name'],
            diagnosedAt: isset($data['diagnosed_at']) ? (string) $data['diagnosed_at'] : null,
            notes: $data['notes'] ?? null,
            hadConditionBefore: array_key_exists('had_condition_before', $data) ? (bool) $data['had_condition_before'] : null,
            isChronic: array_key_exists('is_chronic', $data) ? (bool) $data['is_chronic'] : null,
            metadata: [
                'source' => 'manual',
                'actor_user_id' => $authUser->id,
            ],
        );

        $commandBus->dispatch($command);

        $snapshot = $historyFinder->findByUserId($enrollment->user_id);

        return response()->json([
            'medical_history' => $snapshot,
        ], 201);
    }

    public function storeMedication(
        string $patientUuid,
        Request $request,
        QueryBus $queryBus,
        CommandBus $commandBus,
        PatientMedicalHistoryFinder $historyFinder,
    ): JsonResponse {
        /** @var User|null $authUser */
        $authUser = $request->user();

        abort_unless(
            $authUser && ($authUser->hasAnyRole(['admin', 'staff']) || in_array($authUser->role, ['admin', 'staff'], true)),
            403
        );

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
            new GetPatientEnrollmentByPatientUuid($patientUuid)
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json([
                'message' => 'Patient not found.',
            ], 404);
        }

        $command = new RecordPatientMedication(
            patientUuid: $enrollment->patient_uuid,
            userId: $enrollment->user_id,
            medicationId: (int) $data['medication_id'],
            dosage: $data['dosage'],
            frequency: $data['frequency'],
            startDate: isset($data['start_date']) ? (string) $data['start_date'] : null,
            endDate: isset($data['end_date']) ? (string) $data['end_date'] : null,
            notes: $data['notes'] ?? null,
            metadata: [
                'source' => 'manual',
                'actor_user_id' => $authUser->id,
            ],
        );

        $commandBus->dispatch($command);

        $snapshot = $historyFinder->findByUserId($enrollment->user_id);

        return response()->json([
            'medical_history' => $snapshot,
        ], 201);
    }

    public function storeVisitSummary(
        string $patientUuid,
        Request $request,
        QueryBus $queryBus,
        CommandBus $commandBus,
        PatientMedicalHistoryFinder $historyFinder,
    ): JsonResponse {
        /** @var User|null $authUser */
        $authUser = $request->user();

        abort_unless(
            $authUser && ($authUser->hasAnyRole(['admin', 'staff']) || in_array($authUser->role, ['admin', 'staff'], true)),
            403
        );

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
            new GetPatientEnrollmentByPatientUuid($patientUuid)
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json([
                'message' => 'Patient not found.',
            ], 404);
        }

        $command = new RecordPatientVisitSummary(
            patientUuid: $enrollment->patient_uuid,
            userId: $enrollment->user_id,
            pastInjuries: (bool) $data['past_injuries'],
            pastInjuriesDetails: $data['past_injuries_details'] ?? null,
            surgery: (bool) $data['surgery'],
            surgeryDetails: $data['surgery_details'] ?? null,
            chronicConditionsDetails: $data['chronic_conditions_details'] ?? null,
            chronicPain: (bool) $data['chronic_pain'],
            chronicPainDetails: $data['chronic_pain_details'] ?? null,
            familyHistoryConditions: $data['family_history_conditions'] ?? [],
            metadata: [
                'source' => 'manual',
                'actor_user_id' => $authUser->id,
            ],
        );

        $commandBus->dispatch($command);

        $snapshot = $historyFinder->findByUserId($enrollment->user_id);

        return response()->json([
            'medical_history' => $snapshot,
        ]);
    }
}

