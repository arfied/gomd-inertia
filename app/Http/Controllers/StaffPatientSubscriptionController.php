<?php

namespace App\Http\Controllers;

use App\Application\Patient\Queries\GetPatientEnrollmentByPatientUuid;
use App\Application\Patient\Queries\GetPatientSubscriptionByUserId;
use App\Application\Queries\QueryBus;
use App\Domain\Subscription\SubscriptionAggregate;
use App\Domain\Subscription\SubscriptionRenewalSaga;
use App\Models\PatientEnrollment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\Events\Dispatcher;
use App\Services\EventStoreContract;

class StaffPatientSubscriptionController extends Controller
{
    public function renew(
        string $patientUuid,
        Request $request,
        QueryBus $queryBus,
        EventStoreContract $eventStore,
        Dispatcher $dispatcher
    ): JsonResponse {
        /** @var User|null $authUser */
        $authUser = $request->user();

        // Authorization: Only staff/admin can renew patient subscriptions
        abort_unless(
            $authUser && ($authUser->hasAnyRole(['admin', 'staff']) || in_array($authUser->role, ['admin', 'staff'], true)),
            403,
        );

        // Get the patient enrollment by patient UUID
        /** @var PatientEnrollment|null $enrollment */
        $enrollment = $queryBus->ask(
            new GetPatientEnrollmentByPatientUuid($patientUuid)
        );

        if (! $enrollment instanceof PatientEnrollment) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        $patient = User::find($enrollment->user_id);

        if (! $patient) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        /** @var Subscription|null $subscription */
        $subscription = $queryBus->ask(
            new GetPatientSubscriptionByUserId($patient->id)
        );

        if (! $subscription instanceof Subscription) {
            return response()->json([
                'error' => 'No active subscription found for this patient',
            ], 404);
        }

        // Calculate new end date based on plan duration
        $planDurationMonths = $subscription->plan->duration_months ?? 1;
        $newEndsAt = $subscription->ends_at->addMonths($planDurationMonths);

        // Record the SubscriptionRenewed event
        $renewalPayload = [
            'previous_ends_at' => $subscription->ends_at->toDateTimeString(),
            'new_ends_at' => $newEndsAt->toDateTimeString(),
            'renewal_reason' => 'manual',
        ];

        $aggregate = SubscriptionAggregate::renew(
            (string) $subscription->id,
            $renewalPayload
        );

        // Store events
        foreach ($aggregate->getRecordedEvents() as $event) {
            $eventStore->store($event);
            $dispatcher->dispatch($event);
        }

        // Start renewal saga
        $sagaUuid = Str::uuid()->toString();
        $correlationId = Str::uuid()->toString();
        $sagaPayload = [
            'subscription_id' => $subscription->id,
            'user_id' => $patient->id,
            'plan_id' => $subscription->plan_id,
            'amount' => $subscription->plan->price ?? 0,
            'billing_date' => now()->toDateString(),
            'correlation_id' => $correlationId,
        ];

        $saga = SubscriptionRenewalSaga::start($sagaUuid, $sagaPayload);

        foreach ($saga->getRecordedEvents() as $event) {
            $eventStore->store($event);
            $dispatcher->dispatch($event);
        }

        // Update subscription end date and reactivate if cancelled
        $subscription->ends_at = $newEndsAt;
        $subscription->status = Subscription::STATUS_ACTIVE;
        $subscription->cancelled_at = null;
        $subscription->save();

        return response()->json([
            'subscription' => [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'plan_name' => optional($subscription->plan)->name,
                'is_trial' => $subscription->is_trial,
                'starts_at' => optional($subscription->starts_at)?->toISOString(),
                'ends_at' => optional($subscription->ends_at)?->toISOString(),
            ],
        ]);
    }
}

