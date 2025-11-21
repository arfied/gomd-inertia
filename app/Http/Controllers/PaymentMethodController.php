<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    /**
     * Get all payment methods for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $paymentMethods = PaymentMethod::where('user_id', $user->id)
            ->active()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($pm) => $this->formatPaymentMethod($pm));

        return response()->json([
            'data' => $paymentMethods,
            'count' => $paymentMethods->count(),
        ]);
    }

    /**
     * Get a specific payment method
     */
    public function show(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $this->authorize('view', $paymentMethod);

        return response()->json([
            'data' => $this->formatPaymentMethod($paymentMethod),
        ]);
    }

    /**
     * Create a new payment method
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $this->validatePaymentMethod($request);

        // If this is the first payment method or marked as default, set it as default
        $isDefault = $validated['is_default'] ?? false;
        if (!$isDefault && PaymentMethod::where('user_id', $user->id)->active()->count() === 0) {
            $isDefault = true;
        }

        // If setting as default, unset other defaults
        if ($isDefault) {
            PaymentMethod::where('user_id', $user->id)
                ->active()
                ->update(['is_default' => false]);
        }

        $paymentMethod = PaymentMethod::create([
            'user_id' => $user->id,
            ...$validated,
            'is_default' => $isDefault,
            'verification_status' => $this->getInitialVerificationStatus($validated['type']),
        ]);

        return response()->json([
            'data' => $this->formatPaymentMethod($paymentMethod),
        ], 201);
    }

    /**
     * Update a payment method
     */
    public function update(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $this->authorize('update', $paymentMethod);

        $validated = $this->validatePaymentMethod($request, $paymentMethod->id);

        $paymentMethod->update($validated);

        return response()->json([
            'data' => $this->formatPaymentMethod($paymentMethod),
        ]);
    }

    /**
     * Delete (archive) a payment method
     */
    public function destroy(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $this->authorize('delete', $paymentMethod);

        // Prevent deletion of default payment method
        if ($paymentMethod->is_default) {
            return response()->json([
                'error' => 'Cannot delete the default payment method. Please set another as default first.',
            ], 422);
        }

        $paymentMethod->archive();

        return response()->json(null, 204);
    }

    /**
     * Set a payment method as default
     */
    public function setDefault(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $this->authorize('update', $paymentMethod);

        // Unset other defaults
        PaymentMethod::where('user_id', $request->user()->id)
            ->active()
            ->update(['is_default' => false]);

        // Set this as default
        $paymentMethod->update(['is_default' => true]);

        return response()->json([
            'data' => $this->formatPaymentMethod($paymentMethod),
        ]);
    }

    /**
     * Validate payment method input
     */
    private function validatePaymentMethod(Request $request, ?int $paymentMethodId = null): array
    {
        $type = $request->input('type');

        $rules = [
            'type' => ['required', Rule::in(['credit_card', 'ach', 'invoice'])],
            'is_default' => ['boolean'],
        ];

        // Type-specific validation
        if ($type === 'credit_card') {
            $rules = array_merge($rules, [
                'cc_last_four' => ['required', 'string', 'size:4'],
                'cc_brand' => ['required', 'string'],
                'cc_expiration_month' => ['required', 'numeric', 'between:1,12'],
                'cc_expiration_year' => ['required', 'numeric', 'digits:4'],
                'cc_token' => ['required', 'string'],
            ]);
        } elseif ($type === 'ach') {
            $rules = array_merge($rules, [
                'ach_account_name' => ['required', 'string', 'max:255'],
                'ach_account_type' => ['required', Rule::in(['checking', 'savings'])],
                'ach_routing_number_last_four' => ['required', 'string', 'size:4'],
                'ach_account_number_last_four' => ['required', 'string', 'size:4'],
                'ach_token' => ['required', 'string'],
            ]);
        } elseif ($type === 'invoice') {
            $rules = array_merge($rules, [
                'invoice_company_name' => ['required', 'string', 'max:255'],
                'invoice_contact_name' => ['required', 'string', 'max:255'],
                'invoice_email' => ['required', 'email'],
                'invoice_phone' => ['required', 'string', 'max:20'],
                'invoice_billing_address' => ['required', 'string', 'max:500'],
                'invoice_payment_terms' => ['required', 'string', 'max:100'],
            ]);
        }

        return $request->validate($rules);
    }

    /**
     * Get initial verification status based on payment method type
     */
    private function getInitialVerificationStatus(string $type): string
    {
        return $type === 'ach' ? 'pending' : 'verified';
    }

    /**
     * Format payment method for API response
     */
    private function formatPaymentMethod(PaymentMethod $paymentMethod): array
    {
        return [
            'id' => $paymentMethod->id,
            'type' => $paymentMethod->type,
            'is_default' => $paymentMethod->is_default,
            'verification_status' => $paymentMethod->verification_status,
            'display_name' => $paymentMethod->getDisplayName(),
            'created_at' => $paymentMethod->created_at,
            'updated_at' => $paymentMethod->updated_at,
            // Type-specific data
            ...$this->getTypeSpecificData($paymentMethod),
        ];
    }

    /**
     * Get type-specific data for formatting
     */
    private function getTypeSpecificData(PaymentMethod $paymentMethod): array
    {
        if ($paymentMethod->isCreditCard()) {
            return [
                'cc_last_four' => $paymentMethod->cc_last_four,
                'cc_brand' => $paymentMethod->cc_brand,
                'cc_expiration_month' => $paymentMethod->cc_expiration_month,
                'cc_expiration_year' => $paymentMethod->cc_expiration_year,
            ];
        } elseif ($paymentMethod->isAch()) {
            return [
                'ach_account_name' => $paymentMethod->ach_account_name,
                'ach_account_type' => $paymentMethod->ach_account_type,
                'ach_routing_number_last_four' => $paymentMethod->ach_routing_number_last_four,
                'ach_account_number_last_four' => $paymentMethod->ach_account_number_last_four,
            ];
        } elseif ($paymentMethod->isInvoice()) {
            return [
                'invoice_company_name' => $paymentMethod->invoice_company_name,
                'invoice_contact_name' => $paymentMethod->invoice_contact_name,
                'invoice_email' => $paymentMethod->invoice_email,
                'invoice_phone' => $paymentMethod->invoice_phone,
            ];
        }

        return [];
    }
}

