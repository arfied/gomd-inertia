<?php

namespace App\Services\AuthorizeNet\Logging;

use Illuminate\Support\Facades\Log;

/**
 * Structured logging helper for AuthorizeNet operations
 * Provides consistent logging format and context across all services
 */
class StructuredLogger
{
    private const CONTEXT_KEY = 'authorize_net';

    /**
     * Log API request
     *
     * @param string $requestType
     * @param array $maskedPayload
     */
    public static function logApiRequest(string $requestType, array $maskedPayload): void
    {
        Log::debug('Authorize.net API Request', [
            'context' => self::CONTEXT_KEY,
            'request_type' => $requestType,
            'payload' => $maskedPayload,
        ]);
    }

    /**
     * Log API response
     *
     * @param string $requestType
     * @param array $response
     */
    public static function logApiResponse(string $requestType, array $response): void
    {
        Log::debug('Authorize.net API Response', [
            'context' => self::CONTEXT_KEY,
            'request_type' => $requestType,
            'response_code' => $response['messages']['resultCode'] ?? 'unknown',
        ]);
    }

    /**
     * Log API error
     *
     * @param string $requestType
     * @param string $errorCode
     * @param string $errorMessage
     * @param array $details
     */
    public static function logApiError(
        string $requestType,
        string $errorCode,
        string $errorMessage,
        array $details = []
    ): void {
        Log::error('Authorize.net API Error', array_merge([
            'context' => self::CONTEXT_KEY,
            'request_type' => $requestType,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ], $details));
    }

    /**
     * Log customer profile operation
     *
     * @param string $operation
     * @param int $userId
     * @param string|null $profileId
     * @param bool $success
     * @param string|null $message
     */
    public static function logCustomerProfileOperation(
        string $operation,
        int $userId,
        ?string $profileId = null,
        bool $success = true,
        ?string $message = null
    ): void {
        $level = $success ? 'info' : 'warning';
        Log::$level("Customer profile $operation", [
            'context' => self::CONTEXT_KEY,
            'operation' => $operation,
            'user_id' => $userId,
            'profile_id' => $profileId,
            'message' => $message,
        ]);
    }

    /**
     * Log payment profile operation
     *
     * @param string $operation
     * @param int $userId
     * @param string|null $paymentProfileId
     * @param bool $success
     * @param string|null $message
     */
    public static function logPaymentProfileOperation(
        string $operation,
        int $userId,
        ?string $paymentProfileId = null,
        bool $success = true,
        ?string $message = null
    ): void {
        $level = $success ? 'info' : 'warning';
        Log::$level("Payment profile $operation", [
            'context' => self::CONTEXT_KEY,
            'operation' => $operation,
            'user_id' => $userId,
            'payment_profile_id' => $paymentProfileId,
            'message' => $message,
        ]);
    }

    /**
     * Log transaction operation
     *
     * @param string $operation
     * @param int $userId
     * @param float $amount
     * @param string|null $transactionId
     * @param bool $success
     * @param string|null $message
     */
    public static function logTransactionOperation(
        string $operation,
        int $userId,
        float $amount,
        ?string $transactionId = null,
        bool $success = true,
        ?string $message = null
    ): void {
        $level = $success ? 'info' : 'error';
        Log::$level("Transaction $operation", [
            'context' => self::CONTEXT_KEY,
            'operation' => $operation,
            'user_id' => $userId,
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'message' => $message,
        ]);
    }

    /**
     * Log validation error
     *
     * @param string $operation
     * @param array $errors
     */
    public static function logValidationError(string $operation, array $errors): void
    {
        Log::warning("Validation error during $operation", [
            'context' => self::CONTEXT_KEY,
            'operation' => $operation,
            'errors' => $errors,
        ]);
    }
}

