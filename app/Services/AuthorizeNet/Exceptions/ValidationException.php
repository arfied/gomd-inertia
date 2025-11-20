<?php

namespace App\Services\AuthorizeNet\Exceptions;

/**
 * Exception thrown when input validation fails
 */
class ValidationException extends AuthorizeNetException
{
    /**
     * The validation errors
     */
    protected array $errors = [];

    public function __construct(
        string $message = 'Validation failed',
        array $errors = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, 'VALIDATION_ERROR', [], 0, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }
}

