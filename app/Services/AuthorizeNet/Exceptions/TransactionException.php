<?php

namespace App\Services\AuthorizeNet\Exceptions;

/**
 * Exception thrown when a transaction fails
 */
class TransactionException extends AuthorizeNetException
{
    /**
     * The transaction response data
     */
    protected ?array $transactionResponse = null;

    public function __construct(
        string $message = 'Transaction failed',
        ?string $errorCode = null,
        ?array $transactionResponse = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $errorCode, [], 0, $previous);
        $this->transactionResponse = $transactionResponse;
    }

    public function getTransactionResponse(): ?array
    {
        return $this->transactionResponse;
    }

    public static function fromTransactionResponse(array $response): self
    {
        $errorText = $response['errors'][0]['errorText'] ?? 'Unknown transaction error';
        $errorCode = $response['errors'][0]['errorCode'] ?? 'UNKNOWN';

        return new self(
            "Transaction failed: $errorText",
            $errorCode,
            $response
        );
    }
}

