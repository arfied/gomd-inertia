<?php

namespace App\Services\AuthorizeNet;

use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\AuthorizeNet\Exceptions\ApiException;
use App\Services\AuthorizeNet\Logging\StructuredLogger;

class AchPaymentService
{
    /**
     * @var AuthorizeNetApi
     */
    protected $api;

    /**
     * @var CustomerProfileService
     */
    protected $customerProfileService;

    /**
     * @var PaymentProfileService
     */
    protected $paymentProfileService;

    /**
     * @var TransactionService
     */
    protected $transactionService;

    /**
     * @var AchVerificationService
     */
    protected $verificationService;

    /**
     * Constructor
     *
     * @param AuthorizeNetApi|null $api
     * @param CustomerProfileService|null $customerProfileService
     * @param PaymentProfileService|null $paymentProfileService
     * @param TransactionService|null $transactionService
     * @param AchVerificationService|null $verificationService
     */
    public function __construct(
        ?AuthorizeNetApi $api = null,
        ?CustomerProfileService $customerProfileService = null,
        ?PaymentProfileService $paymentProfileService = null,
        ?TransactionService $transactionService = null,
        ?AchVerificationService $verificationService = null
    ) {
        $this->api = $api ?? new AuthorizeNetApi();
        $this->customerProfileService = $customerProfileService ?? new CustomerProfileService($this->api);
        $this->paymentProfileService = $paymentProfileService ?? new PaymentProfileService($this->api);
        $this->transactionService = $transactionService ?? new TransactionService($this->api);
        $this->verificationService = $verificationService ?? new AchVerificationService($this->api);
    }

    /**
     * Add an ACH payment method to a user's account
     *
     * @param User $user
     * @param string $accountName
     * @param string $accountType
     * @param string $routingNumber
     * @param string $accountNumber
     * @param string $bankName
     * @return array
     */
    public function addAchPaymentMethod(User $user, string $accountName, string $accountType, string $routingNumber, string $accountNumber, string $bankName): array
    {
        try {
            // Get or create customer profile
            $profileResult = $this->getOrCreateCustomerProfile($user);

            if (!$profileResult['success']) {
                throw new \Exception($profileResult['error'] ?? 'Failed to get customer profile');
            }

            $customerProfileId = $profileResult['profile_id'];

            // Create ACH payment profile
            $paymentProfileId = $this->paymentProfileService->createAchPaymentProfile(
                $customerProfileId,
                $accountName,
                $accountType,
                $routingNumber,
                $accountNumber,
                $bankName,
                $user
            );

            if (empty($paymentProfileId)) {
                throw new \Exception("Received empty paymentProfileId from Authorize.net");
            }

            // Create the result array
            $result = [
                'success' => true,
                'customer_profile_id' => $customerProfileId,
                'payment_profile_id' => $paymentProfileId,
                'account_type' => $accountType,
                'routing_number_last_four' => substr($routingNumber, -4),
                'account_number_last_four' => substr($accountNumber, -4),
                'message' => 'ACH payment method added successfully!'
            ];

            // If verification is required, initiate it
            if (config('services.authorize_net.ach_verification_required', true)) {
                // We'll initiate verification after the payment method is saved in the database
                $result['verification_required'] = true;
                $result['verification_message'] = 'Bank account verification is required. Micro-deposits will be initiated after saving.';
            }

            StructuredLogger::logCardOperation(
                'ach_account_added',
                $user->id,
                'ACH',
                true
            );

            return $result;
        } catch (ApiException $e) {
            // Check for duplicate payment profile
            if ($e->isDuplicateProfile()) {
                StructuredLogger::logCardOperation(
                    'ach_account_duplicate',
                    $user->id,
                    'ACH',
                    false,
                    'Duplicate payment profile'
                );
                return [
                    'success' => false,
                    'message' => 'This bank account is already on file.',
                    'error' => 'Duplicate payment profile'
                ];
            }

            StructuredLogger::logCardOperation(
                'ach_account_failed',
                $user->id,
                'ACH',
                false,
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to add bank account. Please try again.',
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            StructuredLogger::logCardOperation(
                'ach_account_failed',
                $user->id,
                'ACH',
                false,
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to add bank account. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process an ACH transaction
     *
     * @param float $amount
     * @param string $customerProfileId
     * @param string $paymentProfileId
     * @param string $description
     * @param int|null $userId
     * @param int|null $subscriptionId
     * @param bool $isDiscounted
     * @return array
     */
    public function processAchTransaction(float $amount, string $customerProfileId, string $paymentProfileId, string $description = '', int $userId = null, int $subscriptionId = null, bool $isDiscounted = false): array
    {
        try {
            // If verification is required, check if the payment method is verified
            if (config('services.authorize_net.ach_verification_required', true) && $userId) {
                // Find the payment method
                $paymentMethod = PaymentMethod::where('user_id', $userId)
                    ->where('ach_token', $paymentProfileId)
                    ->where('type', 'ach')
                    ->first();

                if ($paymentMethod) {
                    // Check verification status
                    $verificationStatus = $paymentMethod->meta_data['verification_status'] ?? 'not_initiated';

                    if ($verificationStatus !== 'verified') {
                        return [
                            'success' => false,
                            'message' => 'This bank account has not been verified. Please complete the verification process before making payments.',
                            'verification_required' => true
                        ];
                    }
                }
            }

            $result = $this->transactionService->processProfileTransaction(
                $amount,
                $customerProfileId,
                $paymentProfileId,
                TransactionService::TRANSACTION_TYPE_AUTH_CAPTURE,
                $description,
                $userId,
                $subscriptionId,
                $isDiscounted,
                'ach'
            );

            StructuredLogger::logTransactionOperation(
                'ach_transaction_processed',
                $userId,
                $amount,
                $result['transaction_id'],
                true
            );

            return [
                'success' => true,
                'transaction_id' => $result['transaction_id'],
                'auth_code' => $result['auth_code'],
                'message' => 'ACH transaction processed successfully!'
            ];
        } catch (\Exception $e) {
            StructuredLogger::logTransactionOperation(
                'ach_transaction_failed',
                $userId,
                $amount,
                null,
                false,
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to process ACH transaction. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get or create a customer profile
     *
     * @param User $user
     * @return array
     */
    private function getOrCreateCustomerProfile(User $user): array
    {
        try {
            // Check if user already has a profile ID
            if ($user->authorize_net_customer_id) {
                StructuredLogger::logProfileOperation(
                    'customer_profile_retrieved',
                    $user->id,
                    $user->authorize_net_customer_id,
                    true
                );
                return [
                    'success' => true,
                    'profile_id' => $user->authorize_net_customer_id,
                    'is_new' => false
                ];
            }

            // Create a new profile
            $profileId = $this->customerProfileService->createCustomerProfile($user);
            $user->authorize_net_customer_id = $profileId;
            $user->save();

            StructuredLogger::logProfileOperation(
                'customer_profile_created',
                $user->id,
                $profileId,
                true
            );

            return [
                'success' => true,
                'profile_id' => $profileId,
                'is_new' => true
            ];
        } catch (\Exception $e) {
            StructuredLogger::logProfileOperation(
                'customer_profile_failed',
                $user->id,
                null,
                false,
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to manage customer profile. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Initiate verification for an ACH payment method
     *
     * @param PaymentMethod $paymentMethod
     * @return array
     */
    public function initiateVerification(PaymentMethod $paymentMethod): array
    {
        return $this->verificationService->initiateMicroDeposits($paymentMethod);
    }

    /**
     * Verify an ACH payment method with micro-deposit amounts
     *
     * @param PaymentMethod $paymentMethod
     * @param float $amount1
     * @param float $amount2
     * @return array
     */
    public function verifyMicroDeposits(PaymentMethod $paymentMethod, float $amount1, float $amount2): array
    {
        return $this->verificationService->verifyMicroDeposits($paymentMethod, $amount1, $amount2);
    }

    /**
     * Get verification status for an ACH payment method
     *
     * @param PaymentMethod $paymentMethod
     * @return array
     */
    public function getVerificationStatus(PaymentMethod $paymentMethod): array
    {
        return $this->verificationService->getVerificationStatus($paymentMethod);
    }
}
