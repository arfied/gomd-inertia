<?php
namespace App\Concerns;

use App\Models\OfflineConversion;

trait TracksOC
{
    protected function recordConversion(
        string $eventType,
        float $conversionValue = 0.00,
        ?string $conversionName = null,
        array $additionalData = []
    ) {
        $gclid = session('gclid') ?? request()->query('gclid');
        
        // Store gclid in session if it exists in query parameters
        if (request()->has('gclid') && !session()->has('gclid')) {
            session()->put('gclid', request()->query('gclid'));
        }

        // Get UTM parameters
        $utmSource = request()->query('utm_source');
        $utmMedium = request()->query('utm_medium');
        $utmCampaign = request()->query('utm_campaign');

        // Determine conversion name if not provided
        if (!$conversionName) {
            $conversionName = match($eventType) {
                'visit' => 'Page View',
                'form_submit' => 'Lead Generation',
                'payment' => 'Purchase',
                default => ucfirst($eventType)
            };
        }

        // Get the current route or URL path as landing page
        $landingPage = request()->route() 
            ? request()->route()->getName() 
            : request()->path();

        // Create the conversion record
        $conversion = new OfflineConversion();
        $conversion->gclid = $gclid;
        $conversion->page = $landingPage;
        $conversion->event_type = $eventType;
        $conversion->conversion_name = $conversionName;
        $conversion->conversion_value = $conversionValue;
        $conversion->conversion_currency = 'USD'; // You can make this configurable
        $conversion->visitor_ip = request()->ip();
        $conversion->user_agent = request()->userAgent();
        $conversion->user_id = auth()->id(); // If user is authenticated
        $conversion->plan_id = $additionalData['plan_id'] ?? null;
        $conversion->source = $utmSource;
        $conversion->medium = $utmMedium;
        $conversion->campaign = $utmCampaign;

        // Store form data if provided
        if (isset($additionalData['form_data'])) {
            $conversion->form_data = $additionalData['form_data'];
        }

        $conversion->save();

        return $conversion;
    }

    protected function recordPageView()
    {
        return $this->recordConversion('visit', 0.00, 'Page View');
    }

    protected function recordLeadGeneration(array $formData, float $value = 0.00)
    {
        return $this->recordConversion('form_submit', $value, 'Lead Generation', [
            'form_data' => $formData
        ]);
    }

    protected function recordPurchase(float $value, int $planId)
    {
        return $this->recordConversion('payment', $value, 'Purchase', [
            'plan_id' => $planId
        ]);
    }
}