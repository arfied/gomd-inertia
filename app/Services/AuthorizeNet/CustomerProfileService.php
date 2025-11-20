<?php

namespace App\Services\AuthorizeNet;

use App\Models\User;
use App\Services\AuthorizeNet\Exceptions\ApiException;
use App\Services\AuthorizeNet\Logging\StructuredLogger;
use App\Services\AuthorizeNet\Responses\ResponseParser;

class CustomerProfileService
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
     * Create a customer profile
     *
     * @param User $user
     * @return string The customer profile ID
     * @throws \Exception
     */
    public function createCustomerProfile(User $user): string
    {
        try {
            $response = $this->api->sendRequest('createCustomerProfileRequest', [
                'profile' => [
                    'merchantCustomerId' => 'M_' . $user->id,
                    'description' => 'Customer Profile for ' . $user->name,
                    'email' => $user->email
                ]
            ]);

            // Extract the customer profile ID from the response
            $profileId = ResponseParser::extractCustomerProfileId($response);
            if ($profileId) {
                StructuredLogger::logCustomerProfileOperation('created', $user->id, $profileId, true);
                return $profileId;
            }

            throw new \Exception("Could not extract customer profile ID from response");
        } catch (ApiException $e) {
            // Check for duplicate profile error
            if ($e->isDuplicateProfile()) {
                StructuredLogger::logCustomerProfileOperation(
                    'duplicate_detected',
                    $user->id,
                    null,
                    false,
                    'Duplicate profile detected'
                );

                // Extract the profile ID from the error message
                if (preg_match('/ID: (\d+)/', $e->getMessage(), $matches)) {
                    return $matches[1];
                }
            }

            throw $e;
        }
    }

    /**
     * Get a customer profile
     *
     * @param string $profileId
     * @return array The customer profile data
     * @throws \Exception
     */
    public function getCustomerProfile(string $profileId): array
    {
        try {
            $response = $this->api->sendRequest('getCustomerProfileRequest', [
                'customerProfileId' => $profileId
            ]);

            $profileData = ResponseParser::extractProfileData($response);
            if ($profileData) {
                return $profileData;
            }

            throw new \Exception("Could not retrieve customer profile");
        } catch (ApiException $e) {
            // Check for profile not found error
            if ($e->isProfileNotFound()) {
                StructuredLogger::logCustomerProfileOperation(
                    'not_found',
                    0,
                    $profileId,
                    false,
                    'Customer profile not found in Authorize.net'
                );
            }

            throw $e;
        }
    }

    /**
     * Check if a customer profile exists
     *
     * @param string $profileId
     * @return bool Whether the profile exists
     */
    public function customerProfileExists(string $profileId): bool
    {
        try {
            $this->getCustomerProfile($profileId);
            return true;
        } catch (ApiException $e) {
            if ($e->isProfileNotFound()) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Update a customer profile
     *
     * @param string $profileId
     * @param array $profileData
     * @return bool Success status
     * @throws \Exception
     */
    public function updateCustomerProfile(string $profileId, array $profileData): bool
    {
        $profile = array_merge(['customerProfileId' => $profileId], $profileData);

        $this->api->sendRequest('updateCustomerProfileRequest', [
            'profile' => $profile
        ]);

        return true;
    }

    /**
     * Delete a customer profile
     *
     * @param string $profileId
     * @return bool Success status
     * @throws \Exception
     */
    public function deleteCustomerProfile(string $profileId): bool
    {
        $this->api->sendRequest('deleteCustomerProfileRequest', [
            'customerProfileId' => $profileId
        ]);

        return true;
    }
}
