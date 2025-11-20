<?php

namespace App\Services\AuthorizeNet\Exceptions;

/**
 * Exception thrown when Authorize.net API returns an error
 */
class ApiException extends AuthorizeNetException
{
    // Common Authorize.net error codes
    public const DUPLICATE_PROFILE = 'E00039';
    public const PROFILE_NOT_FOUND = 'E00040';
    public const INVALID_RESPONSE = 'E00001';
    public const VALIDATION_ERROR = 'E00114';

    public static function fromApiResponse(array $response): self
    {
        $errorCode = $response['messages']['message'][0]['code'] ?? 'UNKNOWN';
        $errorMessage = $response['messages']['message'][0]['text'] ?? 'Unknown API error';

        return new self(
            "Authorize.net API Error: $errorMessage",
            $errorCode,
            ['response' => $response]
        );
    }

    public function isDuplicateProfile(): bool
    {
        return $this->errorCode === self::DUPLICATE_PROFILE;
    }

    public function isProfileNotFound(): bool
    {
        return $this->errorCode === self::PROFILE_NOT_FOUND;
    }

    public function isValidationError(): bool
    {
        return $this->errorCode === self::VALIDATION_ERROR;
    }
}

