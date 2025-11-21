<?php

namespace App\Listeners;

use App\Domain\Subscription\Events\RenewalFailureAlert;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RenewalFailureAlertHandler implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RenewalFailureAlert $event): void
    {
        if (!config('subscription.failure_alerts.enabled')) {
            return;
        }

        $user = User::find($event->userId);
        if (!$user) {
            Log::warning('User not found for renewal failure alert', [
                'user_id' => $event->userId,
                'saga_uuid' => $event->sagaUuid,
            ]);
            return;
        }

        // Send email alerts
        $this->sendEmailAlert($event, $user);

        // Send Slack alert
        $this->sendSlackAlert($event, $user);

        // Send PagerDuty alert
        $this->sendPagerDutyAlert($event, $user);

        Log::info('Renewal failure alert sent', [
            'saga_uuid' => $event->sagaUuid,
            'user_id' => $event->userId,
            'correlation_id' => $event->correlationId,
        ]);
    }

    private function sendEmailAlert(RenewalFailureAlert $event, User $user): void
    {
        $recipients = config('subscription.failure_alerts.email_recipients', []);
        if (empty($recipients) || empty(array_filter($recipients))) {
            return;
        }

        try {
            $message = $this->buildAlertMessage($event, $user);
            // In production, use a proper mailable class
            Log::info('Email alert would be sent', [
                'recipients' => $recipients,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email alert', [
                'error' => $e->getMessage(),
                'saga_uuid' => $event->sagaUuid,
            ]);
        }
    }

    private function sendSlackAlert(RenewalFailureAlert $event, User $user): void
    {
        $webhook = config('subscription.failure_alerts.slack_webhook');
        if (!$webhook) {
            return;
        }

        try {
            $payload = [
                'text' => 'Subscription Renewal Failed',
                'attachments' => [
                    [
                        'color' => 'danger',
                        'fields' => [
                            ['title' => 'User', 'value' => $user->name, 'short' => true],
                            ['title' => 'Amount', 'value' => '$' . number_format($event->amount, 2), 'short' => true],
                            ['title' => 'Reason', 'value' => $event->reason, 'short' => false],
                            ['title' => 'Attempts', 'value' => "{$event->attemptNumber}/{$event->maxAttempts}", 'short' => true],
                            ['title' => 'Saga UUID', 'value' => $event->sagaUuid, 'short' => true],
                            ['title' => 'Correlation ID', 'value' => $event->correlationId, 'short' => false],
                        ],
                    ],
                ],
            ];

            Http::post($webhook, $payload);
        } catch (\Exception $e) {
            Log::error('Failed to send Slack alert', [
                'error' => $e->getMessage(),
                'saga_uuid' => $event->sagaUuid,
            ]);
        }
    }

    private function sendPagerDutyAlert(RenewalFailureAlert $event, User $user): void
    {
        $key = config('subscription.failure_alerts.pagerduty_key');
        if (!$key) {
            return;
        }

        try {
            $payload = [
                'routing_key' => $key,
                'event_action' => 'trigger',
                'dedup_key' => "renewal_failure_{$event->sagaUuid}",
                'payload' => [
                    'summary' => "Subscription renewal failed for {$user->name}",
                    'severity' => 'error',
                    'source' => 'TeleMed Pro',
                    'custom_details' => [
                        'user_id' => $event->userId,
                        'amount' => $event->amount,
                        'reason' => $event->reason,
                        'saga_uuid' => $event->sagaUuid,
                        'correlation_id' => $event->correlationId,
                    ],
                ],
            ];

            Http::post('https://events.pagerduty.com/v2/enqueue', $payload);
        } catch (\Exception $e) {
            Log::error('Failed to send PagerDuty alert', [
                'error' => $e->getMessage(),
                'saga_uuid' => $event->sagaUuid,
            ]);
        }
    }

    private function buildAlertMessage(RenewalFailureAlert $event, User $user): string
    {
        return "Subscription renewal failed for {$user->name} (ID: {$user->id})\n" .
               "Amount: \${$event->amount}\n" .
               "Reason: {$event->reason}\n" .
               "Attempts: {$event->attemptNumber}/{$event->maxAttempts}\n" .
               "Saga UUID: {$event->sagaUuid}\n" .
               "Correlation ID: {$event->correlationId}";
    }
}

