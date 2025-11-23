<?php

namespace App\Http\Controllers\Signup;

use App\Application\Commands\CommandBus;
use App\Application\Signup\Commands\CompleteQuestionnaire;
use App\Application\Signup\Commands\CreateSubscription;
use App\Application\Signup\Commands\FailSignup;
use App\Application\Signup\Commands\ProcessPayment;
use App\Application\Signup\Commands\SelectCondition;
use App\Application\Signup\Commands\SelectMedication;
use App\Application\Signup\Commands\SelectPlan;
use App\Application\Signup\Commands\StartSignup;
use App\Http\Controllers\Controller;
use App\Models\SignupReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SignupController extends Controller
{
    /**
     * Start a new signup process.
     */
    public function start(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'signup_path' => 'required|string|in:medication_first,condition_first,plan_first',
        ]);

        $signupId = (string) Str::uuid();

        $command = new StartSignup(
            signupId: $signupId,
            userId: $request->user()?->id ?? (string) Str::uuid(),
            signupPath: $data['signup_path'],
            metadata: ['source' => 'web', 'ip' => $request->ip()],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'success' => true,
            'signup_id' => $signupId,
            'message' => 'Signup process started',
        ]);
    }

    /**
     * Select medication in the signup flow.
     */
    public function selectMedication(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'signup_id' => 'required|string|uuid',
            'medication_id' => 'required|string|uuid',
        ]);

        $command = new SelectMedication(
            signupId: $data['signup_id'],
            medicationId: $data['medication_id'],
            metadata: ['source' => 'web', 'ip' => $request->ip()],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'success' => true,
            'message' => 'Medication selected',
        ]);
    }

    /**
     * Select condition in the signup flow.
     */
    public function selectCondition(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'signup_id' => 'required|string|uuid',
            'condition_id' => 'required|string|uuid',
        ]);

        $command = new SelectCondition(
            signupId: $data['signup_id'],
            conditionId: $data['condition_id'],
            metadata: ['source' => 'web', 'ip' => $request->ip()],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'success' => true,
            'message' => 'Condition selected',
        ]);
    }

    /**
     * Select plan in the signup flow.
     */
    public function selectPlan(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'signup_id' => 'required|string|uuid',
            'plan_id' => 'required|string|uuid',
        ]);

        $command = new SelectPlan(
            signupId: $data['signup_id'],
            planId: $data['plan_id'],
            metadata: ['source' => 'web', 'ip' => $request->ip()],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'success' => true,
            'message' => 'Plan selected',
        ]);
    }

    /**
     * Complete questionnaire in the signup flow.
     */
    public function completeQuestionnaire(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'signup_id' => 'required|string|uuid',
            'responses' => 'required|array',
        ]);

        $command = new CompleteQuestionnaire(
            signupId: $data['signup_id'],
            responses: $data['responses'],
            metadata: ['source' => 'web', 'ip' => $request->ip()],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'success' => true,
            'message' => 'Questionnaire completed',
        ]);
    }

    /**
     * Process payment in the signup flow.
     */
    public function processPayment(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'signup_id' => 'required|string|uuid',
            'payment_id' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|string|in:success,pending,failed',
        ]);

        $command = new ProcessPayment(
            signupId: $data['signup_id'],
            paymentId: $data['payment_id'],
            amount: (float) $data['amount'],
            status: $data['status'],
            metadata: ['source' => 'web', 'ip' => $request->ip()],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'success' => true,
            'message' => 'Payment processed',
        ]);
    }

    /**
     * Create subscription after successful payment.
     */
    public function createSubscription(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'signup_id' => 'required|string|uuid',
            'subscription_id' => 'required|string|uuid',
            'user_id' => 'required|string',
            'plan_id' => 'required|string|uuid',
            'medication_id' => 'nullable|string|uuid',
            'condition_id' => 'nullable|string|uuid',
        ]);

        $command = new CreateSubscription(
            signupId: $data['signup_id'],
            subscriptionId: $data['subscription_id'],
            userId: $data['user_id'],
            planId: $data['plan_id'],
            medicationId: $data['medication_id'] ?? null,
            conditionId: $data['condition_id'] ?? null,
            metadata: ['source' => 'web', 'ip' => $request->ip()],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'success' => true,
            'message' => 'Subscription created',
        ]);
    }

    /**
     * Fail signup process.
     */
    public function fail(Request $request, CommandBus $commandBus): JsonResponse
    {
        $data = $request->validate([
            'signup_id' => 'required|string|uuid',
            'reason' => 'required|string|in:validation_error,payment_failed,system_error',
            'message' => 'required|string|max:500',
        ]);

        $command = new FailSignup(
            signupId: $data['signup_id'],
            reason: $data['reason'],
            message: $data['message'],
            metadata: ['source' => 'web', 'ip' => $request->ip()],
        );

        $commandBus->dispatch($command);

        return response()->json([
            'success' => true,
            'message' => 'Signup failed',
        ]);
    }

    /**
     * Get signup status.
     */
    public function status(string $signupId): JsonResponse
    {
        $signup = SignupReadModel::where('signup_uuid', $signupId)->first();

        if (! $signup) {
            return response()->json([
                'success' => false,
                'message' => 'Signup not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $signup,
        ]);
    }
}

