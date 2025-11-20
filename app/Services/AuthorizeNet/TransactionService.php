<?php

namespace App\Services\AuthorizeNet;

use App\Models\Transaction;
use App\Services\AuthorizeNet\Exceptions\TransactionException;
use App\Services\AuthorizeNet\Logging\StructuredLogger;
use App\Services\AuthorizeNet\Responses\ResponseParser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    /**
     * @var AuthorizeNetApi
     */
    protected $api;

    /**
     * Transaction types
     */
    const TRANSACTION_TYPE_AUTH_ONLY = 'authOnlyTransaction';
    const TRANSACTION_TYPE_AUTH_CAPTURE = 'authCaptureTransaction';
    const TRANSACTION_TYPE_CAPTURE_ONLY = 'captureOnlyTransaction';
    const TRANSACTION_TYPE_REFUND = 'refundTransaction';
    const TRANSACTION_TYPE_VOID = 'voidTransaction';

    /**
     * Constructor
     */
    public function __construct(AuthorizeNetApi $api)
    {
        $this->api = $api;
    }

    /**
     * Process a transaction using a customer profile
     *
     * @param float $amount
     * @param string $customerProfileId
     * @param string $paymentProfileId
     * @param string $transactionType
     * @param string $description
     * @param int|null $userId
     * @param int|null $subscriptionId
     * @param bool $isDiscounted
     * @param string $currency
     * @return array Transaction details
     * @throws \Exception
     */
    public function processProfileTransaction(
        float $amount,
        string $customerProfileId,
        string $paymentProfileId,
        string $transactionType = self::TRANSACTION_TYPE_AUTH_ONLY,
        string $description = '',
        int $userId = null,
        int $subscriptionId = null,
        bool $isDiscounted = false,
        string $currency = 'USD'
    ): array {
        $response = $this->api->sendRequest('createTransactionRequest', [
            'refId' => 'ref' . time(),
            'transactionRequest' => [
                'transactionType' => $transactionType,
                'amount' => number_format($amount, 2, '.', ''),
                'profile' => [
                    'customerProfileId' => $customerProfileId,
                    'paymentProfile' => [
                        'paymentProfileId' => $paymentProfileId
                    ]
                ],
                'order' => [
                    'description' => $description
                ]
            ]
        ]);

        // Extract transaction details from the response
        if (isset($response['transactionResponse'])) {
            $transactionResponse = $response['transactionResponse'];

            if (isset($transactionResponse['responseCode']) && $transactionResponse['responseCode'] == "1") {
                $result = [
                    'success' => true,
                    'transaction_id' => $transactionResponse['transId'] ?? null,
                    'auth_code' => $transactionResponse['authCode'] ?? null,
                    'response_code' => $transactionResponse['responseCode'],
                    'message' => $transactionResponse['messages'][0]['description'] ?? 'Transaction successful',
                    'raw_response' => $transactionResponse
                ];

                // Record the transaction in the database
                TransactionRecorder::record(
                    $userId ?? Auth::id(),
                    $subscriptionId,
                    $amount,
                    $isDiscounted,
                    $currency,
                    $result['transaction_id'],
                    'credit_card',
                    'success',
                    null
                );

                StructuredLogger::logTransactionOperation(
                    'profile_transaction_processed',
                    $userId ?? Auth::id(),
                    $amount,
                    $result['transaction_id'],
                    true
                );

                return $result;
            } else {
                $errorText = ResponseParser::extractTransactionErrorMessage($transactionResponse);

                // Record the failed transaction
                TransactionRecorder::record(
                    $userId ?? Auth::id(),
                    $subscriptionId,
                    $amount,
                    $isDiscounted,
                    $currency,
                    null,
                    'credit_card',
                    'failed',
                    $errorText
                );

                StructuredLogger::logTransactionOperation(
                    'profile_transaction_failed',
                    $userId ?? Auth::id(),
                    $amount,
                    null,
                    false,
                    $errorText
                );

                throw TransactionException::fromTransactionResponse($transactionResponse);
            }
        }

        throw new TransactionException("Invalid transaction response format");
    }

    /**
     * Refund a transaction
     *
     * @param string $transactionId
     * @param float $amount
     * @param string $cardNumber
     * @param string $expirationDate
     * @param int|null $userId
     * @param int|null $subscriptionId
     * @param bool $isDiscounted
     * @param string $currency
     * @return array Refund details
     * @throws \Exception
     */
    public function refundTransaction(
        string $transactionId,
        float $amount,
        string $cardNumber,
        string $expirationDate,
        int $userId = null,
        int $subscriptionId = null,
        bool $isDiscounted = false,
        string $currency = 'USD'
    ): array {
        $response = $this->api->sendRequest('createTransactionRequest', [
            'refId' => 'ref' . time(),
            'transactionRequest' => [
                'transactionType' => self::TRANSACTION_TYPE_REFUND,
                'amount' => number_format($amount, 2, '.', ''),
                'payment' => [
                    'creditCard' => [
                        'cardNumber' => $cardNumber,
                        'expirationDate' => $expirationDate
                    ]
                ],
                'refTransId' => $transactionId
            ]
        ]);

        // Extract transaction details from the response
        if (isset($response['transactionResponse'])) {
            $transactionResponse = $response['transactionResponse'];

            if (isset($transactionResponse['responseCode']) && $transactionResponse['responseCode'] == "1") {
                $result = [
                    'success' => true,
                    'transaction_id' => $transactionResponse['transId'] ?? null,
                    'auth_code' => $transactionResponse['authCode'] ?? null,
                    'response_code' => $transactionResponse['responseCode'],
                    'message' => $transactionResponse['messages'][0]['description'] ?? 'Refund successful',
                    'raw_response' => $transactionResponse
                ];

                // Record the refund transaction in the database
                TransactionRecorder::record(
                    $userId ?? Auth::id(),
                    $subscriptionId,
                    -$amount, // Negative amount for refunds
                    $isDiscounted,
                    $currency,
                    $result['transaction_id'],
                    'credit_card',
                    'refunded',
                    null
                );

                return $result;
            } else {
                $errorText = ResponseParser::extractTransactionErrorMessage($transactionResponse);

                // Record the failed refund transaction
                TransactionRecorder::record(
                    $userId ?? Auth::id(),
                    $subscriptionId,
                    -$amount, // Negative amount for refunds
                    $isDiscounted,
                    $currency,
                    null,
                    'credit_card',
                    'failed',
                    $errorText
                );

                StructuredLogger::logTransactionOperation(
                    'refund_failed',
                    $userId ?? Auth::id(),
                    -$amount,
                    null,
                    false,
                    $errorText
                );

                throw TransactionException::fromTransactionResponse($transactionResponse);
            }
        }

        throw new TransactionException("Invalid refund response format");
    }

    /**
     * Void a transaction
     *
     * @param string $transactionId
     * @param int|null $userId
     * @param int|null $subscriptionId
     * @param float|null $amount
     * @param bool $isDiscounted
     * @param string $currency
     * @return array Void details
     * @throws \Exception
     */
    public function voidTransaction(
        string $transactionId,
        int $userId = null,
        int $subscriptionId = null,
        float $amount = null,
        bool $isDiscounted = false,
        string $currency = 'USD'
    ): array {
        $response = $this->api->sendRequest('createTransactionRequest', [
            'refId' => 'ref' . time(),
            'transactionRequest' => [
                'transactionType' => self::TRANSACTION_TYPE_VOID,
                'refTransId' => $transactionId
            ]
        ]);

        // Extract transaction details from the response
        if (isset($response['transactionResponse'])) {
            $transactionResponse = $response['transactionResponse'];

            if (isset($transactionResponse['responseCode']) && $transactionResponse['responseCode'] == "1") {
                $result = [
                    'success' => true,
                    'transaction_id' => $transactionResponse['transId'] ?? null,
                    'auth_code' => $transactionResponse['authCode'] ?? null,
                    'response_code' => $transactionResponse['responseCode'],
                    'message' => $transactionResponse['messages'][0]['description'] ?? 'Void successful',
                    'raw_response' => $transactionResponse
                ];

                // Record the void transaction in the database
                if ($amount !== null) {
                    TransactionRecorder::record(
                        $userId ?? Auth::id(),
                        $subscriptionId,
                        0, // Amount is 0 for voids
                        $isDiscounted,
                        $currency,
                        $result['transaction_id'],
                        'credit_card',
                        'voided',
                        null
                    );
                }

                return $result;
            } else {
                $errorText = ResponseParser::extractTransactionErrorMessage($transactionResponse);

                // Record the failed void transaction
                if ($amount !== null) {
                    TransactionRecorder::record(
                        $userId ?? Auth::id(),
                        $subscriptionId,
                        0, // Amount is 0 for voids
                        $isDiscounted,
                        $currency,
                        null,
                        'credit_card',
                        'failed',
                        $errorText
                    );

                    StructuredLogger::logTransactionOperation(
                        'void_failed',
                        $userId ?? Auth::id(),
                        0,
                        null,
                        false,
                        $errorText
                    );
                }

                throw TransactionException::fromTransactionResponse($transactionResponse);
            }
        }

        throw new TransactionException("Invalid void response format");
    }

    /**
     * Get transaction details
     *
     * @param string $transactionId
     * @return array Transaction details
     * @throws \Exception
     */
    public function getTransactionDetails(string $transactionId): array
    {
        $response = $this->api->sendRequest('getTransactionDetailsRequest', [
            'transId' => $transactionId
        ]);

        $transactionData = ResponseParser::extractTransactionData($response);
        if ($transactionData) {
            return $transactionData;
        }

        throw new \Exception("Could not retrieve transaction details");
    }

}
