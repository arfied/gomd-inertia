<?php

namespace App\Services\AuthorizeNet;

use App\Mail\AchVerificationFailed;
use App\Mail\AchVerificationInitiated;
use App\Mail\AchVerificationSuccessful;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\AuthorizeNet\Exceptions\ApiException;
use App\Services\AuthorizeNet\Logging\StructuredLogger;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AchVerificationService
{
    /**
     * @var AuthorizeNetApi
     */
    protected $api;

    /**
     * @var TransactionService
     */
    protected $transactionService;

    /**
     * Constructor
     *
     * @param AuthorizeNetApi|null $api
     * @param TransactionService|null $transactionService
     */
    public function __construct(
        ?AuthorizeNetApi $api = null,
        ?TransactionService $transactionService = null
    ) {
        $this->api = $api ?? new AuthorizeNetApi();
        $this->transactionService = $transactionService ?? new TransactionService($this->api);
    }

    /**
     * Initiate micro-deposit verification for an ACH payment method
     *
     * @param PaymentMethod $paymentMethod
     * @return array
     */
    public function initiateMicroDeposits(PaymentMethod $paymentMethod): array
    {
        try {
            // Ensure this is an ACH payment method
            if (!$paymentMethod->isAch()) {
                throw new \Exception('Payment method is not an ACH account');
            }

            // Get the user
            $user = $paymentMethod->user;
            if (!$user) {
                throw new \Exception('User not found for payment method');
            }

            // Get customer profile ID
            $customerProfileId = $user->authorize_net_customer_id;
            if (empty($customerProfileId)) {
                throw new \Exception('No Authorize.net customer profile found for this user');
            }

            // Initiate micro-deposits
            $response = $this->api->sendRequest('createCustomerPaymentProfileRequest', [
                'customerProfileId' => $customerProfileId,
                'paymentProfile' => [
                    'customerPaymentProfileId' => $paymentMethod->ach_token,
                    'validation' => [
                        'validationMode' => config('services.authorize_net.sandbox') ? 'testMode' : 'liveMode'
                    ]
                ]
            ]);

            // Update payment method with verification status
            $paymentMethod->meta_data = array_merge($paymentMethod->meta_data ?? [], [
                'verification_status' => 'pending',
                'verification_initiated_at' => now()->toDateTimeString(),
                'verification_code' => Str::random(10)
            ]);
            $paymentMethod->save();

            // Send email notification
            try {
                Mail::to($user->email)->send(new AchVerificationInitiated($user, $paymentMethod));
                StructuredLogger::logVerificationOperation(
                    'verification_initiated',
                    $user->id,
                    $paymentMethod->id,
                    true
                );
            } catch (\Exception $e) {
                StructuredLogger::logVerificationOperation(
                    'verification_email_failed',
                    $user->id,
                    $paymentMethod->id,
                    false,
                    'Failed to send verification email: ' . $e->getMessage()
                );
            }

            return [
                'success' => true,
                'message' => 'Micro-deposits initiated successfully. They should appear in the bank account within 1-2 business days.',
                'verification_code' => $paymentMethod->meta_data['verification_code']
            ];
        } catch (\Exception $e) {
            StructuredLogger::logVerificationOperation(
                'verification_initiation_failed',
                $user->id ?? 0,
                $paymentMethod->id ?? 0,
                false,
                $e->getMessage()
            );
            return [
                'success' => false,
                'message' => 'Failed to initiate micro-deposits: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify micro-deposits for an ACH payment method
     *
     * @param PaymentMethod $paymentMethod
     * @param float $amount1
     * @param float $amount2
     * @return array
     */
    public function verifyMicroDeposits(PaymentMethod $paymentMethod, float $amount1, float $amount2): array
    {
        try {
            // Ensure this is an ACH payment method
            if (!$paymentMethod->isAch()) {
                throw new \Exception('Payment method is not an ACH account');
            }

            // Check if verification is pending
            if (!isset($paymentMethod->meta_data['verification_status']) ||
                $paymentMethod->meta_data['verification_status'] !== 'pending') {
                throw new \Exception('No pending verification found for this payment method');
            }

            // Get the user
            $user = $paymentMethod->user;
            if (!$user) {
                throw new \Exception('User not found for payment method');
            }

            // Get customer profile ID
            $customerProfileId = $user->authorize_net_customer_id;
            if (empty($customerProfileId)) {
                throw new \Exception('No Authorize.net customer profile found for this user');
            }

            // Verify micro-deposits
            $response = $this->api->sendRequest('validateCustomerPaymentProfileRequest', [
                'customerProfileId' => $customerProfileId,
                'customerPaymentProfileId' => $paymentMethod->ach_token,
                'validationMode' => 'testMode', // Use 'liveMode' in production
                'amounts' => [
                    'amount1' => $amount1,
                    'amount2' => $amount2
                ]
            ]);

            // Update payment method with verification status
            $paymentMethod->meta_data = array_merge($paymentMethod->meta_data ?? [], [
                'verification_status' => 'verified',
                'verification_completed_at' => now()->toDateTimeString()
            ]);
            $paymentMethod->save();

            // Send email notification
            try {
                Mail::to($user->email)->send(new AchVerificationSuccessful($user, $paymentMethod));
                StructuredLogger::logVerificationOperation(
                    'verification_successful',
                    $user->id,
                    $paymentMethod->id,
                    true
                );
            } catch (\Exception $e) {
                StructuredLogger::logVerificationOperation(
                    'verification_email_failed',
                    $user->id,
                    $paymentMethod->id,
                    false,
                    'Failed to send success email: ' . $e->getMessage()
                );
            }

            return [
                'success' => true,
                'message' => 'Bank account verified successfully.'
            ];
        } catch (ApiException $e) {
            // Check if it's a validation error
            if ($e->isValidationError()) {
                // Update payment method with failed attempt
                $attempts = ($paymentMethod->meta_data['verification_attempts'] ?? 0) + 1;
                $paymentMethod->meta_data = array_merge($paymentMethod->meta_data ?? [], [
                    'verification_attempts' => $attempts,
                    'last_verification_attempt' => now()->toDateTimeString()
                ]);

                // If too many failed attempts, mark as failed
                if ($attempts >= 3) {
                    $paymentMethod->meta_data['verification_status'] = 'failed';

                    // Send failure email notification
                    try {
                        Mail::to($user->email)->send(new AchVerificationFailed($user, $paymentMethod));
                        StructuredLogger::logVerificationOperation(
                            'verification_failed',
                            $user->id,
                            $paymentMethod->id,
                            false,
                            'Max verification attempts exceeded'
                        );
                    } catch (\Exception $emailException) {
                        StructuredLogger::logVerificationOperation(
                            'verification_email_failed',
                            $user->id,
                            $paymentMethod->id,
                            false,
                            'Failed to send failure email: ' . $emailException->getMessage()
                        );
                    }
                }

                $paymentMethod->save();

                return [
                    'success' => false,
                    'message' => 'The amounts entered do not match the micro-deposits. Please try again.',
                    'attempts_remaining' => 3 - $attempts
                ];
            }

            StructuredLogger::logVerificationOperation(
                'verification_error',
                $user->id ?? 0,
                $paymentMethod->id ?? 0,
                false,
                $e->getMessage()
            );
            return [
                'success' => false,
                'message' => 'Failed to verify micro-deposits: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            StructuredLogger::logVerificationOperation(
                'verification_error',
                $user->id ?? 0,
                $paymentMethod->id ?? 0,
                false,
                $e->getMessage()
            );
            return [
                'success' => false,
                'message' => 'Failed to verify micro-deposits: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check verification status of an ACH payment method
     *
     * @param PaymentMethod $paymentMethod
     * @return array
     */
    public function getVerificationStatus(PaymentMethod $paymentMethod): array
    {
        // Ensure this is an ACH payment method
        if (!$paymentMethod->isAch()) {
            return [
                'success' => false,
                'message' => 'Payment method is not an ACH account',
                'status' => 'not_applicable'
            ];
        }

        // Get verification status from meta_data
        $status = $paymentMethod->meta_data['verification_status'] ?? 'not_initiated';
        $attempts = $paymentMethod->meta_data['verification_attempts'] ?? 0;
        $initiatedAt = isset($paymentMethod->meta_data['verification_initiated_at'])
            ? \Carbon\Carbon::parse($paymentMethod->meta_data['verification_initiated_at'])
            : null;
        $completedAt = isset($paymentMethod->meta_data['verification_completed_at'])
            ? \Carbon\Carbon::parse($paymentMethod->meta_data['verification_completed_at'])
            : null;

        return [
            'success' => true,
            'status' => $status,
            'attempts' => $attempts,
            'attempts_remaining' => 3 - $attempts,
            'initiated_at' => $initiatedAt,
            'completed_at' => $completedAt,
            'message' => $this->getStatusMessage($status, $initiatedAt)
        ];
    }

    /**
     * Get a human-readable message for a verification status
     *
     * @param string $status
     * @param \Carbon\Carbon|null $initiatedAt
     * @return string
     */
    protected function getStatusMessage(string $status, ?\Carbon\Carbon $initiatedAt): string
    {
        return match($status) {
            'not_initiated' => 'Bank account verification has not been initiated.',
            'pending' => 'Verification is pending. Micro-deposits were initiated on ' .
                ($initiatedAt ? $initiatedAt->format('F j, Y') : 'an unknown date') .
                ' and should appear in your account within 1-2 business days.',
            'verified' => 'Bank account has been successfully verified.',
            'failed' => 'Bank account verification failed. Please contact support for assistance.',
            default => 'Unknown verification status.'
        };
    }
}
