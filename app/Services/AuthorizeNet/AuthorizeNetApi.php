<?php

namespace App\Services\AuthorizeNet;

use App\Services\AuthorizeNet\Exceptions\ApiException;
use App\Services\AuthorizeNet\Logging\StructuredLogger;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthorizeNetApi
{
    /**
     * @var string The API endpoint URL
     */
    protected $apiUrl;

    /**
     * @var array Authentication credentials
     */
    protected $credentials;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set the API URL based on environment
        $this->apiUrl = config('services.authorize_net.sandbox')
            ? 'https://apitest.authorize.net/xml/v1/request.api'
            : 'https://api.authorize.net/xml/v1/request.api';

        // Set the authentication credentials
        $this->credentials = [
            'name' => config('services.authorize_net.login_id'),
            'transactionKey' => config('services.authorize_net.transaction_key')
        ];
    }

    /**
     * Send a request to the Authorize.net API
     *
     * @param string $requestType The type of request
     * @param array $data The request data
     * @return array The response data
     * @throws \Exception
     */
    public function sendRequest(string $requestType, array $data): array
    {
        // Create the request payload
        $payload = [
            $requestType => array_merge(
                [
                    'merchantAuthentication' => $this->credentials
                ],
                $data
            )
        ];

        // Log the request for debugging (mask sensitive data)
        $logPayload = $this->maskSensitiveData($payload);
        StructuredLogger::logApiRequest($requestType, $logPayload);

        try {
            // Send the request to the API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, $payload);

            // Get response body and remove BOM if present
            $responseBody = preg_replace('/^\xEF\xBB\xBF/', '', $response->body());

            // Log the cleaned response (will be logged after validation)

            // Try to decode JSON after BOM removal
            $responseData = json_decode($responseBody, true);

            // Check if JSON parsing succeeded
            if ($responseData === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Authorize.net: ' . $responseBody);
            }

            // Check if the response has the expected structure
            if (!isset($responseData['messages']) || !isset($responseData['messages']['resultCode'])) {
                // Special case for some responses that might have a different structure
                if (isset($responseData['customerProfileId']) || isset($responseData['customerPaymentProfileId'])) {
                    return $responseData;
                }

                throw new ApiException('Invalid response structure from Authorize.net: ' . $response->body());
            }

            // Check if the request was successful
            if ($responseData['messages']['resultCode'] !== 'Ok') {
                throw ApiException::fromApiResponse($responseData);
            }

            // Log successful response
            StructuredLogger::logApiResponse($requestType, $responseData);

            // Return the response data
            return $responseData;
        } catch (ApiException $e) {
            StructuredLogger::logApiError(
                $requestType,
                $e->getErrorCode() ?? 'UNKNOWN_ERROR',
                $e->getMessage(),
                $e->getDetails()
            );
            throw $e;
        } catch (\Exception $e) {
            StructuredLogger::logApiError(
                $requestType,
                'UNKNOWN_ERROR',
                $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Mask sensitive data in the request payload for logging
     *
     * @param array $payload The request payload
     * @return array The masked payload
     */
    protected function maskSensitiveData(array $payload): array
    {
        $maskedPayload = $payload;

        // Mask transaction key
        if (isset($maskedPayload['merchantAuthentication']['transactionKey'])) {
            $key = $maskedPayload['merchantAuthentication']['transactionKey'];
            $maskedPayload['merchantAuthentication']['transactionKey'] = substr($key, 0, 4) . '****' . substr($key, -4);
        }

        // Mask credit card number if present
        $this->recursiveMaskCreditCard($maskedPayload);

        return $maskedPayload;
    }

    /**
     * Recursively mask credit card numbers in an array
     *
     * @param array &$array The array to mask
     * @return void
     */
    protected function recursiveMaskCreditCard(array &$array): void
    {
        foreach ($array as $key => &$value) {
            if ($key === 'cardNumber' && is_string($value)) {
                $value = substr($value, 0, 6) . '******' . substr($value, -4);
            } elseif ($key === 'cardCode' && is_string($value)) {
                $value = '***';
            } elseif (is_array($value)) {
                $this->recursiveMaskCreditCard($value);
            }
        }
    }
}
