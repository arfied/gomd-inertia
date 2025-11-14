<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

trait TracksLinkTrust
{
    /**
     * Track a conversion event in LinkTrust
     *
     * @param string|null $merchantReferenceId User ID or other unique identifier
     * @param float|null $totalSales Amount of the sale
     * @param string|null $transactionId Optional transaction ID for logging
     * @param bool $isSuccessful Whether the transaction was successful
     * @param array|null $customerInfo Optional customer information for unsuccessful transactions
     * @return array Response data
     */
    protected function trackLinkTrustConversion(
        ?string $merchantReferenceId = null,
        ?float $totalSales = 0.0,
        ?string $transactionId = null,
        bool $isSuccessful = true,
        ?array $customerInfo = null
    ): array
    {
        // Get ClickID from cookie first, then session, then request
        $clickId = Cookie::get('ClickID') ??
                  session('ClickID') ??
                  request()->query('ClickID');

        // Get LTClickID as fallback for backwards compatibility
        $ltClickId = Cookie::get('LTClickID') ??
                    session('LTClickID') ??
                    request()->query('LTClickID');

        // Use LTClickID as fallback if ClickID is not set
        if (empty($clickId) && !empty($ltClickId)) {
            $clickId = $ltClickId;
        }

        // If no ClickID or LTClickID is present, don't track in LinkTrust
        if (empty($clickId)) {
            Log::info('LinkTrust tracking skipped - no ClickID or LTClickID present', [
                'user_id' => $merchantReferenceId,
                'transaction_id' => $transactionId
            ]);

            return [
                'success' => false,
                'message' => 'LinkTrust tracking skipped - no ClickID or LTClickID present',
                'skipped' => true
            ];
        }

        // Get AFID for recurring billing
        $afid = Cookie::get('AFID') ??
               session('AFID') ??
               request()->query('AFID');

        // Use authenticated user ID if not provided
        if (!$merchantReferenceId && auth()->check()) {
            $merchantReferenceId = auth()->id();
        }

        // Build the tracking URL
        $url = config('services.linktrust.tracking_url', 'https://tracking.gomdusa.com/pixel.track');

        // Build query parameters
        $queryParams = [
            'ClickID' => $clickId,
            'MerchantReferenceID' => $merchantReferenceId ?? '',
            'TotalSales' => $isSuccessful && $totalSales ? round($totalSales, 2) : '0',
        ];

        // Add AFID if available (for recurring billing)
        if ($afid) {
            $queryParams['AFID'] = $afid;

            // According to LinkTrust documentation, when using AFID for recurring billing,
            // we need to include the original ClickID as well
            $queryParams['CID'] = $clickId; // CID is used alongside AFID for recurring billing
        }

        // Add customer information for unsuccessful transactions
        if (!$isSuccessful && $customerInfo) {
            if (!empty($customerInfo['name'])) {
                $queryParams['CustomerName'] = $customerInfo['name'];
            }

            if (!empty($customerInfo['email'])) {
                $queryParams['CustomerEmail'] = $customerInfo['email'];
            }

            if (!empty($customerInfo['phone'])) {
                $queryParams['CustomerPhone'] = $customerInfo['phone'];
            }

            // Add a status indicator for unsuccessful transactions
            $queryParams['TransactionStatus'] = 'failed';
        }

        // Log the tracking request with context
        Log::info('LinkTrust tracking request initiated', [
            'url' => $url,
            'params' => $queryParams,
            'user_id' => $merchantReferenceId,
            'transaction_id' => $transactionId
        ]);

        try {
            $response = Http::get($url, $queryParams);

            // Log the response with proper formatting and context
            Log::info('LinkTrust tracking response received', [
                'status_code' => $response->status(),
                'success' => $response->successful(),
                'body' => $response->json() ?: $response->body(),
                'transaction_id' => $transactionId
            ]);

            if (!$response->successful()) {
                Log::warning('LinkTrust tracking request failed', [
                    'status_code' => $response->status(),
                    'reason' => $response->body(),
                    'transaction_id' => $transactionId
                ]);

                return [
                    'success' => false,
                    'message' => 'LinkTrust tracking request failed',
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ];
            }

            return [
                'success' => true,
                'message' => 'LinkTrust tracking request successful',
                'status_code' => $response->status(),
                'response' => $response->json() ?: $response->body(),
                'tracking_url' => $url . '?' . http_build_query($queryParams)
            ];
        } catch (\Exception $e) {
            Log::error('LinkTrust tracking request exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction_id' => $transactionId
            ]);

            return [
                'success' => false,
                'message' => 'LinkTrust tracking request exception: ' . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Track a purchase conversion in LinkTrust
     *
     * @param float $amount Purchase amount
     * @param string|null $userId User ID
     * @param string|null $transactionId Transaction ID
     * @param bool $isSuccessful Whether the transaction was successful
     * @param array|null $customerInfo Optional customer information for unsuccessful transactions
     * @return array Response data
     */
    protected function trackLinkTrustPurchase(
        float $amount,
        ?string $userId = null,
        ?string $transactionId = null,
        bool $isSuccessful = true,
        ?array $customerInfo = null
    ): array
    {
        return $this->trackLinkTrustConversion($userId, $amount, $transactionId, $isSuccessful, $customerInfo);
    }

    /**
     * Track a subscription conversion in LinkTrust
     *
     * @param float $amount Subscription amount
     * @param string|null $userId User ID
     * @param string|null $subscriptionId Subscription ID
     * @param bool $isSuccessful Whether the transaction was successful
     * @param array|null $customerInfo Optional customer information for unsuccessful transactions
     * @return array Response data
     */
    protected function trackLinkTrustSubscription(
        float $amount,
        ?string $userId = null,
        ?string $subscriptionId = null,
        bool $isSuccessful = true,
        ?array $customerInfo = null
    ): array
    {
        return $this->trackLinkTrustConversion($userId, $amount, $subscriptionId, $isSuccessful, $customerInfo);
    }

    /**
     * Track a failed transaction in LinkTrust
     *
     * @param string|null $userId User ID
     * @param string|null $transactionId Transaction ID
     * @param array|null $customerInfo Customer information
     * @return array Response data
     */
    protected function trackLinkTrustFailedTransaction(
        ?string $userId = null,
        ?string $transactionId = null,
        ?array $customerInfo = null
    ): array
    {
        return $this->trackLinkTrustConversion($userId, 0.0, $transactionId, false, $customerInfo);
    }
}
