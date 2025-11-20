<?php

namespace App\Services\AuthorizeNet;

use App\Models\User;
use App\Services\AuthorizeNet\Exceptions\ApiException;
use App\Services\AuthorizeNet\Exceptions\ValidationException;
use App\Services\AuthorizeNet\Logging\StructuredLogger;
use App\Services\AuthorizeNet\Responses\ResponseParser;
use App\Services\AuthorizeNet\Validation\PaymentValidator;

class PaymentProfileService
{
    /**
     * @var AuthorizeNetApi
     */
    protected $api;

    /**
     * Constructor
     */
    public function __construct(AuthorizeNetApi $api)
    {
        $this->api = $api;
    }

    /**
     * Create a payment profile for a customer
     *
     * @param string $customerProfileId
     * @param string $cardNumber
     * @param string $expirationMonth
     * @param string $expirationYear
     * @param string $cvv
     * @param User $user
     * @return string Payment profile ID
     * @throws \Exception
     */
    public function createPaymentProfile(
        string $customerProfileId,
        string $cardNumber,
        string $expirationMonth,
        string $expirationYear,
        string $cvv,
        User $user
    ): string {
        // Validate inputs
        $this->validatePaymentProfileInputs($cardNumber, $expirationMonth, $expirationYear, $cvv);

        try {
            // Sanitize card number
            $cardNumber = PaymentValidator::sanitizeCardNumber($cardNumber);

            // Format expiration date as YYYY-MM
            // Make sure the year is 4 digits (YYYY)
            $expirationYear = strlen($expirationYear) == 2 ? '20' . $expirationYear : $expirationYear;
            $expirationDate = $expirationYear . '-' . str_pad($expirationMonth, 2, '0', STR_PAD_LEFT);

            // According to the Authorize.net schema, billTo should be at the same level as paymentProfile
            $response = $this->api->sendRequest('createCustomerPaymentProfileRequest', [
                'customerProfileId' => $customerProfileId,
                'paymentProfile' => [
                    'customerType' => 'individual',
                    'payment' => [
                        'creditCard' => [
                            'cardNumber' => $cardNumber,
                            'expirationDate' => $expirationDate,
                            'cardCode' => $cvv
                        ]
                    ],
                    // 'billTo' => [
                    //     'firstName' => $user->fname ?? '',
                    //     'lastName' => $user->lname ?? '',
                    //     'company' => $user->business->name ?? '',
                    //     'address' => $user->address1 ?? '',
                    //     'city' => $user->city ?? '',
                    //     'state' => $user->state ?? '',
                    //     'zip' => $user->zip ?? '',
                    //     'country' => 'US',
                    //     'phoneNumber' => $user->phone ?? '',
                    //     'email' => $user->email ?? ''
                    // ]
                ]
            ]);

            // Extract the payment profile ID from the response
            $paymentProfileId = ResponseParser::extractPaymentProfileId($response);
            if ($paymentProfileId) {
                StructuredLogger::logPaymentProfileOperation('created', $user->id, $paymentProfileId, true);
                return $paymentProfileId;
            }

            throw new \Exception("Could not extract payment profile ID from response");
        } catch (ApiException $e) {
            // Check for duplicate payment profile error
            if ($e->isDuplicateProfile()) {
                StructuredLogger::logPaymentProfileOperation(
                    'duplicate_detected',
                    $user->id,
                    null,
                    false,
                    'Duplicate payment profile detected'
                );

                // Extract the profile ID from the error message
                if (preg_match('/ID: (\d+)/', $e->getMessage(), $matches)) {
                    return $matches[1];
                }
            }

            // Check for profile not found error
            if ($e->isProfileNotFound()) {
                StructuredLogger::logPaymentProfileOperation(
                    'customer_profile_not_found',
                    $user->id,
                    null,
                    false,
                    "Customer profile {$customerProfileId} not found in Authorize.net"
                );
            }

            throw $e;
        }
    }

    /**
     * Get a payment profile
     *
     * @param string $customerProfileId
     * @param string $paymentProfileId
     * @return array The payment profile data
     * @throws \Exception
     */
    public function getPaymentProfile(string $customerProfileId, string $paymentProfileId): array
    {
        try {
            $response = $this->api->sendRequest('getCustomerPaymentProfileRequest', [
                'customerProfileId' => $customerProfileId,
                'customerPaymentProfileId' => $paymentProfileId
            ]);

            if (isset($response['paymentProfile'])) {
                return $response['paymentProfile'];
            }

            throw new \Exception("Could not retrieve payment profile");
        } catch (ApiException $e) {
            // Check for profile not found error
            if ($e->isProfileNotFound()) {
                StructuredLogger::logPaymentProfileOperation(
                    'not_found',
                    null,
                    $customerProfileId,
                    $paymentProfileId,
                    false,
                    'Payment profile or customer profile not found'
                );

                // Try to verify if the customer profile exists
                try {
                    $this->api->sendRequest('getCustomerProfileRequest', [
                        'customerProfileId' => $customerProfileId
                    ]);

                    // If we get here, the customer profile exists but the payment profile doesn't
                    StructuredLogger::logPaymentProfileOperation(
                        'not_found',
                        null,
                        $customerProfileId,
                        $paymentProfileId,
                        false,
                        'Payment profile not found but customer profile exists'
                    );
                } catch (ApiException $verifyError) {
                    // If the profile doesn't exist, log it
                    if ($verifyError->isProfileNotFound()) {
                        StructuredLogger::logPaymentProfileOperation(
                            'customer_profile_not_found',
                            null,
                            $customerProfileId,
                            null,
                            false,
                            'Customer profile not found'
                        );
                    }
                }
            }

            throw $e;
        }
    }

    /**
     * Update a payment profile
     *
     * @param string $customerProfileId
     * @param string $paymentProfileId
     * @param array $paymentData
     * @return bool Success status
     * @throws \Exception
     */
    public function updatePaymentProfile(string $customerProfileId, string $paymentProfileId, array $paymentData): bool
    {
        $paymentProfile = array_merge(['customerPaymentProfileId' => $paymentProfileId], $paymentData);

        $this->api->sendRequest('updateCustomerPaymentProfileRequest', [
            'customerProfileId' => $customerProfileId,
            'paymentProfile' => $paymentProfile
        ]);

        return true;
    }

    /**
     * Delete a payment profile
     *
     * @param string $customerProfileId
     * @param string $paymentProfileId
     * @return bool Success status
     * @throws \Exception
     */
    public function deletePaymentProfile(string $customerProfileId, string $paymentProfileId): bool
    {
        $this->api->sendRequest('deleteCustomerPaymentProfileRequest', [
            'customerProfileId' => $customerProfileId,
            'customerPaymentProfileId' => $paymentProfileId
        ]);

        return true;
    }

    /**
     * Validate a payment profile
     *
     * @param string $customerProfileId
     * @param string $paymentProfileId
     * @param string $validationMode
     * @return bool Success status
     * @throws \Exception
     */
    public function validatePaymentProfile(
        string $customerProfileId,
        string $paymentProfileId,
        string $validationMode = 'testMode'
    ): bool {
        $this->api->sendRequest('validateCustomerPaymentProfileRequest', [
            'customerProfileId' => $customerProfileId,
            'customerPaymentProfileId' => $paymentProfileId,
            'validationMode' => $validationMode
        ]);

        return true;
    }

    /**
     * Create an ACH payment profile for a customer
     *
     * @param string $customerProfileId
     * @param string $accountName
     * @param string $accountType
     * @param string $routingNumber
     * @param string $accountNumber
     * @param string $bankName
     * @param User $user
     * @return string Payment profile ID
     * @throws \Exception
     */
    public function createAchPaymentProfile(
        string $customerProfileId,
        string $accountName,
        string $accountType,
        string $routingNumber,
        string $accountNumber,
        string $bankName,
        User $user
    ): string {
        // Validate inputs
        $this->validateAchPaymentProfileInputs($routingNumber, $accountNumber, $accountType);

        try {
            // Create the payment profile with bank account information
            $response = $this->api->sendRequest('createCustomerPaymentProfileRequest', [
                'customerProfileId' => $customerProfileId,
                'paymentProfile' => [
                    'customerType' => 'individual',
                    'payment' => [
                        'bankAccount' => [
                            'accountType' => $accountType,
                            'routingNumber' => $routingNumber,
                            'accountNumber' => $accountNumber,
                            'nameOnAccount' => $accountName,
                            'bankName' => $bankName
                        ]
                    ],
                    // 'billTo' => [
                    //     'firstName' => $user->fname ?? '',
                    //     'lastName' => $user->lname ?? '',
                    //     'address' => $user->address1 ?? '',
                    //     'city' => $user->city ?? '',
                    //     'state' => $user->state ?? '',
                    //     'zip' => $user->zip ?? '',
                    //     'country' => 'US',
                    //     'phoneNumber' => $user->phone ?? '',
                    //     'email' => $user->email ?? ''
                    // ]
                ]
            ]);

            // Extract the payment profile ID from the response
            $paymentProfileId = ResponseParser::extractPaymentProfileId($response);
            if ($paymentProfileId) {
                StructuredLogger::logPaymentProfileOperation('ach_created', $user->id, $paymentProfileId, true);
                return $paymentProfileId;
            }

            throw new \Exception("Could not extract payment profile ID from response");
        } catch (ApiException $e) {
            // Check for duplicate payment profile error
            if ($e->isDuplicateProfile()) {
                StructuredLogger::logPaymentProfileOperation(
                    'ach_duplicate_detected',
                    $user->id,
                    null,
                    false,
                    'Duplicate ACH payment profile detected'
                );

                // Extract the profile ID from the error message
                if (preg_match('/ID: (\d+)/', $e->getMessage(), $matches)) {
                    return $matches[1];
                }
            }

            // Check for profile not found error
            if ($e->isProfileNotFound()) {
                StructuredLogger::logPaymentProfileOperation(
                    'ach_customer_profile_not_found',
                    $user->id,
                    null,
                    false,
                    "Customer profile {$customerProfileId} not found in Authorize.net"
                );
            }

            throw $e;
        }
    }

    /**
     * Validate payment profile inputs
     *
     * @param string $cardNumber
     * @param string $expirationMonth
     * @param string $expirationYear
     * @param string $cvv
     * @throws ValidationException
     */
    private function validatePaymentProfileInputs(
        string $cardNumber,
        string $expirationMonth,
        string $expirationYear,
        string $cvv
    ): void {
        $errors = [];

        if (!PaymentValidator::validateCardNumber($cardNumber)) {
            $errors['card_number'] = 'Invalid credit card number';
        }

        if (!PaymentValidator::validateExpirationDate($expirationMonth, $expirationYear)) {
            $errors['expiration_date'] = 'Invalid or expired expiration date';
        }

        if (!PaymentValidator::validateCvv($cvv)) {
            $errors['cvv'] = 'Invalid CVV (must be 3-4 digits)';
        }

        if (!empty($errors)) {
            StructuredLogger::logValidationError('payment_profile_creation', $errors);
            throw new ValidationException('Payment profile validation failed', $errors);
        }
    }

    /**
     * Validate ACH payment profile inputs
     *
     * @param string $routingNumber
     * @param string $accountNumber
     * @param string $accountType
     * @throws ValidationException
     */
    private function validateAchPaymentProfileInputs(
        string $routingNumber,
        string $accountNumber,
        string $accountType
    ): void {
        $errors = [];

        if (!PaymentValidator::validateRoutingNumber($routingNumber)) {
            $errors['routing_number'] = 'Invalid routing number';
        }

        if (!PaymentValidator::validateAccountNumber($accountNumber)) {
            $errors['account_number'] = 'Invalid account number';
        }

        if (!in_array($accountType, ['checking', 'savings'])) {
            $errors['account_type'] = 'Account type must be either "checking" or "savings"';
        }

        if (!empty($errors)) {
            StructuredLogger::logValidationError('ach_payment_profile_creation', $errors);
            throw new ValidationException('ACH payment profile validation failed', $errors);
        }
    }
}
