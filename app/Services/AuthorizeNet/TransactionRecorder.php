<?php

namespace App\Services\AuthorizeNet;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

/**
 * Helper class to record transactions in the database
 * Reduces code duplication across transaction service methods
 */
class TransactionRecorder
{
    /**
     * Record a transaction in the database
     *
     * @param int $userId
     * @param int|null $subscriptionId
     * @param float $amount
     * @param bool $isDiscounted
     * @param string $currency
     * @param string|null $transactionId
     * @param string $paymentMethod
     * @param string $status
     * @param string|null $errorMessage
     * @return Transaction
     */
    public static function record(
        int $userId,
        ?int $subscriptionId,
        float $amount,
        bool $isDiscounted = false,
        string $currency = 'USD',
        ?string $transactionId = null,
        string $paymentMethod = 'credit_card',
        string $status = 'success',
        ?string $errorMessage = null
    ): Transaction {
        try {
            // Check if a transaction with this ID already exists
            if ($transactionId) {
                $existingTransaction = Transaction::where('transaction_id', $transactionId)->first();

                if ($existingTransaction) {
                    return self::updateExisting($existingTransaction, $status, $errorMessage, $subscriptionId);
                }
            }

            return self::createNew($userId, $subscriptionId, $amount, $isDiscounted, $currency, $transactionId, $paymentMethod, $status, $errorMessage);
        } catch (\Exception $e) {
            return self::handleRecordingError($userId, $amount, $currency, $transactionId, $paymentMethod, $status, $errorMessage, $e);
        }
    }

    private static function updateExisting(Transaction $transaction, string $status, ?string $errorMessage, ?int $subscriptionId): Transaction
    {
        $data = [
            'status' => $status,
            'error_message' => $errorMessage,
        ];

        if ($subscriptionId !== null && $transaction->subscription_id === null) {
            $data['subscription_id'] = $subscriptionId;
        }

        $transaction->update($data);
        Log::info("Updated existing transaction record: {$transaction->transaction_id}");
        return $transaction;
    }

    private static function createNew(int $userId, ?int $subscriptionId, float $amount, bool $isDiscounted, string $currency, ?string $transactionId, string $paymentMethod, string $status, ?string $errorMessage): Transaction
    {
        $data = [
            'user_id' => $userId,
            'amount' => $amount,
            'is_discounted' => $isDiscounted,
            'currency' => $currency,
            'transaction_id' => $transactionId,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'error_message' => $errorMessage,
        ];

        if ($subscriptionId !== null) {
            $data['subscription_id'] = $subscriptionId;
        }

        $transaction = Transaction::create($data);
        Log::info("Created new transaction record: " . ($transactionId ?? 'no_id'));
        return $transaction;
    }

    private static function handleRecordingError(int $userId, float $amount, string $currency, ?string $transactionId, string $paymentMethod, string $status, ?string $errorMessage, \Exception $e): Transaction
    {
        Log::error("Error recording transaction: {$e->getMessage()}", [
            'user_id' => $userId,
            'transaction_id' => $transactionId,
            'error' => $e->getMessage()
        ]);

        if ($transactionId) {
            $existingTransaction = Transaction::where('transaction_id', $transactionId)->first();
            if ($existingTransaction) {
                return $existingTransaction;
            }
        }

        $uniqueTransactionId = $transactionId ?? ('manual_' . time() . '_' . rand(1000, 9999));

        return Transaction::create([
            'user_id' => $userId,
            'amount' => $amount,
            'currency' => $currency,
            'transaction_id' => $uniqueTransactionId,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'error_message' => $errorMessage ?? $e->getMessage(),
        ]);
    }
}

