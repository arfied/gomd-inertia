<?php

namespace App\Http\Controllers;

use App\Application\Patient\Queries\GetPatientSubscriptionByUserId;
use App\Application\Queries\QueryBus;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

