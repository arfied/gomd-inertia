<?php

namespace App\Services\AuthorizeNet\Exceptions;

use Exception;

/**
 * Base exception for all Authorize.net related errors
 */
class AuthorizeNetException extends Exception
{
    /**
     * The error code from Authorize.net API
     */
    protected ?string $errorCode = null;

    /**
     * Additional error details
     */
    protected array $details = [];

    public function __construct(
        string $message = '',
        ?string $errorCode = null,
        array $details = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->details = $details;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function withDetails(array $details): self
    {
        $this->details = array_merge($this->details, $details);
        return $this;
    }
}

