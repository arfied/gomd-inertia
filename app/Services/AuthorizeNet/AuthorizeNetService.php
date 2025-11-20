<?php

namespace App\Services\AuthorizeNet;

use App\Models\User;
use App\Services\AuthorizeNet\Exceptions\ApiException;
use App\Services\AuthorizeNet\Logging\StructuredLogger;
use Illuminate\Support\Facades\Log;

class AuthorizeNetService
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
     * Constructor
     *
     * @param AuthorizeNetApi|null $api
     * @param CustomerProfileService|null $customerProfileService
     * @param PaymentProfileService|null $paymentProfileService
     * @param TransactionService|null $transactionService
     */
    public function __construct(
        ?AuthorizeNetApi $api = null,
        ?CustomerProfileService $customerProfileService = null,
        ?PaymentProfileService $paymentProfileService = null,
        ?TransactionService $transactionService = null
    ) {
        $this->api = $api ?? new AuthorizeNetApi();
        $this->customerProfileService = $customerProfileService ?? new CustomerProfileService($this->api);
        $this->paymentProfileService = $paymentProfileService ?? new PaymentProfileService($this->api);
        $this->transactionService = $transactionService ?? new TransactionService($this->api);
    }

    /**
     * Get or create a customer profile
     *
     * @param User $user
     * @return array
     */
    public function getOrCreateCustomerProfile(User $user): array
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
     * Add a credit card to a user's account
     *
     * @param User $user
     * @param string $cardNumber
     * @param string $expirationMonth
     * @param string $expirationYear
     * @param string $cvv
     * @return array
     */
    public function addCreditCard(User $user, string $cardNumber, string $expirationMonth, string $expirationYear, string $cvv): array
    {
        try {
            // Get or create customer profile
            $profileResult = $this->getOrCreateCustomerProfile($user);

            if (!$profileResult['success']) {
                throw new \Exception($profileResult['error'] ?? 'Failed to get customer profile');
            }

            $customerProfileId = $profileResult['profile_id'];

            // Create payment profile
            $paymentProfileId = $this->paymentProfileService->createPaymentProfile(
                $customerProfileId,
                $cardNumber,
                $expirationMonth,
                $expirationYear,
                $cvv,
                $user
            );

            if (empty($paymentProfileId)) {
                throw new \Exception("Received empty paymentProfileId from Authorize.net");
            }

            // Determine card brand
            $cardBrand = $this->getCardBrand($cardNumber);

            StructuredLogger::logCardOperation(
                'card_added',
                $user->id,
                $cardBrand,
                true
            );

            return [
                'success' => true,
                'customer_profile_id' => $customerProfileId,
                'payment_profile_id' => $paymentProfileId,
                'last_four' => substr($cardNumber, -4),
                'brand' => $cardBrand,
                'expiration_month' => $expirationMonth,
                'expiration_year' => $expirationYear,
                'message' => 'Credit card added successfully!'
            ];
        } catch (ApiException $e) {
            // Check for duplicate payment profile
            if ($e->isDuplicateProfile()) {
                StructuredLogger::logCardOperation(
                    'card_duplicate',
                    $user->id,
                    null,
                    false,
                    'Duplicate payment profile'
                );
                return [
                    'success' => false,
                    'message' => 'This credit card is already on file.',
                    'error' => 'Duplicate payment profile'
                ];
            }

            StructuredLogger::logCardOperation(
                'card_failed',
                $user->id,
                null,
                false,
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to add credit card. Please try again.',
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            StructuredLogger::logCardOperation(
                'card_failed',
                $user->id,
                null,
                false,
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to add credit card. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a transaction
     *
     * @param float $amount
     * @param string $customerProfileId
     * @param string $paymentProfileId
     * @param string $description
     * @param int|null $userId The user ID to associate with the transaction (patient, not admin)
     * @param int|null $subscriptionId The subscription ID to associate with the transaction
     * @param bool $isDiscounted Whether the transaction is discounted
     * @return array
     */
    public function processTransaction(float $amount, string $customerProfileId, string $paymentProfileId, string $description = '', int $userId = null, int $subscriptionId = null, bool $isDiscounted = false): array
    {
        try {
            $result = $this->transactionService->processProfileTransaction(
                $amount,
                $customerProfileId,
                $paymentProfileId,
                TransactionService::TRANSACTION_TYPE_AUTH_CAPTURE,
                $description,
                $userId,
                $subscriptionId,
                $isDiscounted
            );

            StructuredLogger::logTransactionOperation(
                'transaction_processed',
                $userId,
                $amount,
                $result['transaction_id'],
                true
            );

            return [
                'success' => true,
                'transaction_id' => $result['transaction_id'],
                'auth_code' => $result['auth_code'],
                'message' => 'Transaction processed successfully!'
            ];
        } catch (\Exception $e) {
            StructuredLogger::logTransactionOperation(
                'transaction_failed',
                $userId,
                $amount,
                null,
                false,
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to process transaction. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Refund a transaction
     *
     * @param string $transactionId
     * @param float $amount
     * @param string $cardNumber
     * @param string $expirationDate
     * @return array
     */
    public function refundTransaction(string $transactionId, float $amount, string $cardNumber, string $expirationDate): array
    {
        try {
            $result = $this->transactionService->refundTransaction(
                $transactionId,
                $amount,
                $cardNumber,
                $expirationDate
            );

            StructuredLogger::logTransactionOperation(
                'refund_processed',
                null,
                -$amount,
                $result['transaction_id'],
                true
            );

            return [
                'success' => true,
                'transaction_id' => $result['transaction_id'],
                'message' => 'Refund processed successfully!'
            ];
        } catch (\Exception $e) {
            StructuredLogger::logTransactionOperation(
                'refund_failed',
                null,
                -$amount,
                null,
                false,
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to process refund. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Void a transaction
     *
     * @param string $transactionId
     * @return array
     */
    public function voidTransaction(string $transactionId): array
    {
        try {
            $result = $this->transactionService->voidTransaction($transactionId);

            StructuredLogger::logTransactionOperation(
                'void_processed',
                null,
                0,
                $result['transaction_id'],
                true
            );

            return [
                'success' => true,
                'transaction_id' => $result['transaction_id'],
                'message' => 'Transaction voided successfully!'
            ];
        } catch (\Exception $e) {
            StructuredLogger::logTransactionOperation(
                'void_failed',
                null,
                0,
                null,
                false,
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to void transaction. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get transaction details
     *
     * @param string $transactionId
     * @return array
     */
    public function getTransactionDetails(string $transactionId): array
    {
        try {
            $result = $this->transactionService->getTransactionDetails($transactionId);

            StructuredLogger::logTransactionOperation(
                'transaction_details_retrieved',
                null,
                0,
                $transactionId,
                true
            );

            return [
                'success' => true,
                'transaction' => $result
            ];
        } catch (\Exception $e) {
            StructuredLogger::logTransactionOperation(
                'transaction_details_failed',
                null,
                0,
                $transactionId,
                false,
                $e->getMessage()
            );

            return [
                'success' => false,
                'message' => 'Failed to retrieve transaction details. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Determine the credit card brand based on the card number
     *
     * @param string $cardNumber
     * @return string
     */
    protected function getCardBrand(string $cardNumber): string
    {
        $brand = 'Unknown';

        if (preg_match('/^4/', $cardNumber)) {
            $brand = 'Visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            $brand = 'Mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            $brand = 'American Express';
        } elseif (preg_match('/^6(?:011|5)/', $cardNumber)) {
            $brand = 'Discover';
        }

        return $brand;
    }
}
