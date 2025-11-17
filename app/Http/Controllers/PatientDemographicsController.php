<?php

namespace App\Http\Controllers;

use App\Application\Commands\CommandBus;
use App\Application\Patient\Commands\UpdatePatientDemographics;
use App\Application\Patient\Queries\GetPatientDemographicsByUserId;
use App\Application\Patient\Queries\GetPatientEnrollmentByUserId;
use App\Application\Queries\QueryBus;
use App\Models\PatientEnrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientDemographicsController extends Controller
{
    public function show(Request $request, QueryBus $queryBus): JsonResponse
    {
        /** @var User $authUser */
        $authUser = $request->user();

        /** @var User|null $user */
        $user = $queryBus->ask(
            new GetPatientDemographicsByUserId($authUser->id)
        );

        /** @var PatientEnrollment|null $enrollment */
        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByUserId($authUser->id)
        );

        return $this->formatDemographicsResponse($user, $enrollment);
    }

    public function update(Request $request, QueryBus $queryBus, CommandBus $commandBus): JsonResponse
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $data = $request->validate([
            'fname' => ['sometimes', 'string', 'max:255'],
            'lname' => ['sometimes', 'string', 'max:255'],
            'gender' => ['sometimes', 'string', 'max:50'],
            'dob' => ['sometimes', 'date'],
            'address1' => ['sometimes', 'string', 'max:255'],
            'address2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'state' => ['sometimes', 'string', 'max:255'],
            'zip' => ['sometimes', 'string', 'max:20'],
            'phone' => ['sometimes', 'string', 'max:50'],
            'mobile_phone' => ['sometimes', 'string', 'max:50'],
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

        $command = new UpdatePatientDemographics(
            patientUuid: $enrollment->patient_uuid,
            userId: $authUser->id,
            demographics: $data,
            metadata: [
                'source' => 'manual',
                'actor_user_id' => $authUser->id,
            ],
        );

        $commandBus->dispatch($command);

        /** @var User|null $user */
        $user = $queryBus->ask(
            new GetPatientDemographicsByUserId($authUser->id)
        );

        return $this->formatDemographicsResponse($user, $enrollment);
    }

    private function formatDemographicsResponse(?User $user, ?PatientEnrollment $enrollment, int $status = 200): JsonResponse
    {
        return response()->json([
            'demographics' => $user ? [
                'patient_uuid' => $enrollment?->patient_uuid,
                'user_id' => $user->id,
                'fname' => $user->fname,
                'lname' => $user->lname,
                'gender' => $user->gender,
                'dob' => optional($user->dob)?->toDateString(),
                'address1' => $user->address1,
                'address2' => $user->address2,
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip,
                'phone' => $user->phone,
                'mobile_phone' => $user->mobile_phone,
            ] : null,
        ], $status);
    }
}

