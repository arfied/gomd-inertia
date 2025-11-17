<?php

namespace App\Http\Controllers;

use App\Application\Commands\CommandBus;
use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuid;
use App\Application\Prescription\Commands\CreatePrescription;
use App\Application\Queries\QueryBus;
use App\Models\MedicationOrder;
use App\Models\PatientEnrollment;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoctorPatientPrescriptionsController extends Controller
{
    public function store(
        string $patientUuid,
        int $order,
        Request $request,
        QueryBus $queryBus,
        CommandBus $commandBus,
    ): JsonResponse {
        /** @var User|null $authUser */
        $authUser = $request->user();

        abort_unless(
            $authUser && ($authUser->hasAnyRole(['doctor']) || in_array($authUser->role, ['doctor'], true)),
            403,
        );

        $data = $request->validate([
            'notes' => ['nullable', 'string'],
            'is_non_standard' => ['nullable', 'boolean'],
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

        /** @var MedicationOrder|null $medicationOrder */
        $medicationOrder = MedicationOrder::where('id', $order)
            ->where('patient_id', $enrollment->user_id)
            ->first();

        if (! $medicationOrder instanceof MedicationOrder) {
            return response()->json([
                'message' => 'Order not found for patient.',
            ], 404);
        }

        $command = new CreatePrescription(
            prescriptionUuid: (string) Str::uuid(),
            patientId: $enrollment->user_id,
            doctorId: $authUser->id,
            notes: $data['notes'] ?? null,
            isNonStandard: array_key_exists('is_non_standard', $data) ? (bool) $data['is_non_standard'] : false,
            metadata: [
                'source' => 'doctor',
                'actor_user_id' => $authUser->id,
                'order_id' => $medicationOrder->id,
                'patient_uuid' => $enrollment->patient_uuid,
            ],
        );

        $commandBus->dispatch($command);

        /** @var Prescription|null $prescription */
        $prescription = Prescription::where('user_id', $enrollment->user_id)
            ->where('doctor_id', $authUser->id)
            ->latest('id')
            ->first();

        return response()->json([
            'prescription' => $prescription ? [
                'id' => $prescription->id,
                'user_id' => $prescription->user_id,
                'doctor_id' => $prescription->doctor_id,
                'status' => $prescription->status,
                'notes' => $prescription->notes,
                'is_non_standard' => $prescription->is_non_standard,
            ] : null,
        ], 201);
    }
}

