<?php

namespace App\Services\AuthorizeNet\Responses;

/**
 * Helper class to parse Authorize.net API responses
 * Reduces code duplication in response parsing across service classes
 */
class ResponseParser
{
    /**
     * Extract customer profile ID from response
     *
     * @param array $response
     * @return string|null
     */
    public static function extractCustomerProfileId(array $response): ?string
    {
        if (isset($response['customerProfileId'])) {
            return $response['customerProfileId'];
        }

        return self::extractIdFromMessage($response);
    }

    /**
     * Extract payment profile ID from response
     *
     * @param array $response
     * @return string|null
     */
    public static function extractPaymentProfileId(array $response): ?string
    {
        if (isset($response['customerPaymentProfileId'])) {
            return $response['customerPaymentProfileId'];
        }

        return self::extractIdFromMessage($response);
    }

    /**
     * Extract transaction ID from response
     *
     * @param array $response
     * @return string|null
     */
    public static function extractTransactionId(array $response): ?string
    {
        if (isset($response['transactionResponse']['transId'])) {
            return $response['transactionResponse']['transId'];
        }

        return null;
    }

    /**
     * Extract auth code from transaction response
     *
     * @param array $response
     * @return string|null
     */
    public static function extractAuthCode(array $response): ?string
    {
        if (isset($response['transactionResponse']['authCode'])) {
            return $response['transactionResponse']['authCode'];
        }

        return null;
    }

    /**
     * Extract error message from transaction response
     *
     * @param array $response
     * @return string
     */
    public static function extractTransactionErrorMessage(array $response): string
    {
        if (isset($response['errors']) &&
            isset($response['errors'][0]) &&
            isset($response['errors'][0]['errorText'])) {
            return $response['errors'][0]['errorText'];
        }

        return "Unknown error";
    }

    /**
     * Extract ID from response message using regex
     *
     * @param array $response
     * @return string|null
     */
    private static function extractIdFromMessage(array $response): ?string
    {
        if (isset($response['messages']['message'][0]['text'])) {
            $message = $response['messages']['message'][0]['text'];
            if (preg_match('/ID: (\d+)/', $message, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Check if response indicates success
     *
     * @param array $response
     * @return bool
     */
    public static function isSuccess(array $response): bool
    {
        if (isset($response['transactionResponse'])) {
            return isset($response['transactionResponse']['responseCode']) &&
                   $response['transactionResponse']['responseCode'] === '1';
        }

        return false;
    }

    /**
     * Extract profile data from response
     *
     * @param array $response
     * @return array|null
     */
    public static function extractProfileData(array $response): ?array
    {
        return $response['profile'] ?? null;
    }

    /**
     * Extract transaction data from response
     *
     * @param array $response
     * @return array|null
     */
    public static function extractTransactionData(array $response): ?array
    {
        return $response['transaction'] ?? null;
    }
}

