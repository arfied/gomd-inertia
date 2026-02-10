<?php

namespace App\Http\Controllers;

use App\Application\Patient\Queries\GetPatientSubscriptionByUserId;
use App\Application\Queries\QueryBus;
use App\Domain\Subscription\SubscriptionAggregate;
use App\Domain\Subscription\SubscriptionRenewalSaga;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Contracts\Events\Dispatcher;
use App\Services\EventStoreContract;

class PatientSubscriptionController extends Controller
{
    public function show(Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        /** @var Subscription|null $subscription */
        $subscription = $queryBus->ask(
            new GetPatientSubscriptionByUserId($user->id)
        );

        return $this->formatSubscriptionResponse($subscription);
    }

    public function cancel(Request $request, QueryBus $queryBus): JsonResponse
    {
        $user = $request->user();

        /** @var Subscription|null $subscription */
        $subscription = $queryBus->ask(
            new GetPatientSubscriptionByUserId($user->id)
        );

        if (! $subscription instanceof Subscription) {
            return $this->formatSubscriptionResponse(null);
        }

        $subscription->status = Subscription::STATUS_CANCELLED;
        $subscription->cancelled_at = now();
        $subscription->save();

        return $this->formatSubscriptionResponse($subscription);
    }

    public function create(Request $request, QueryBus $queryBus, EventStoreContract $eventStore, Dispatcher $dispatcher): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'plan_id' => 'required|integer|exists:subscription_plans,id',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'payment_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Verify the payment method belongs to the user
        $paymentMethod = \App\Models\PaymentMethod::where('id', $data['payment_method_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$paymentMethod) {
            return response()->json(['error' => 'Payment method not found'], 404);
        }

        // Get the plan
        $plan = \App\Models\SubscriptionPlan::find($data['plan_id']);
        if (!$plan) {
            return response()->json(['error' => 'Plan not found'], 404);
        }

        try {
            // Create subscription
            $subscription = \App\Models\Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addMonths($plan->duration_months),
                'status' => \App\Models\Subscription::STATUS_ACTIVE,
                'is_trial' => false,
            ]);

            // Record the subscription created event
            $aggregate = \App\Domain\Subscription\SubscriptionAggregate::create(
                (string) $subscription->id,
                [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'starts_at' => now()->toDateTimeString(),
                    'ends_at' => now()->addMonths($plan->duration_months)->toDateTimeString(),
                ]
            );

            foreach ($aggregate->getRecordedEvents() as $event) {
                $eventStore->store($event);
                $dispatcher->dispatch($event);
            }

            return response()->json([
                'success' => true,
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'plan_name' => $plan->name,
                    'is_trial' => $subscription->is_trial,
                    'starts_at' => $subscription->starts_at->toISOString(),
                    'ends_at' => $subscription->ends_at->toISOString(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create subscription',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function renew(
        Request $request,
        QueryBus $queryBus,
        EventStoreContract $eventStore,
        Dispatcher $dispatcher
    ): JsonResponse {
        $user = $request->user();

        // Authorization: Only the subscription owner can renew
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        /** @var Subscription|null $subscription */
        $subscription = $queryBus->ask(
            new GetPatientSubscriptionByUserId($user->id)
        );

        if (! $subscription instanceof Subscription) {
            return response()->json([
                'error' => 'No active subscription found',
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
            'user_id' => $user->id,
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

        return $this->formatSubscriptionResponse($subscription);
    }

    private function formatSubscriptionResponse(?Subscription $subscription, int $status = 200): JsonResponse
    {
        return response()->json([
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'plan_name' => optional($subscription->plan)->name,
                'is_trial' => $subscription->is_trial,
                'starts_at' => optional($subscription->starts_at)?->toISOString(),
                'ends_at' => optional($subscription->ends_at)?->toISOString(),
            ] : null,
        ], $status);
    }
}

