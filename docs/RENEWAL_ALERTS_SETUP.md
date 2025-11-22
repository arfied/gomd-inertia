# Renewal Alerts Setup Guide

This guide explains how to configure and set up renewal failure alerts in TeleMed Pro. The system supports three notification channels: Email, Slack, and PagerDuty.

## Overview

When a subscription renewal fails after all retry attempts are exhausted, the system triggers a `RenewalFailureAlert` event. The `RenewalFailureAlertHandler` listener processes this event and sends notifications through configured channels.

## Configuration

All alert settings are configured via environment variables in your `.env` file.

### Enable/Disable Alerts

```bash
# Enable or disable all renewal failure alerts (default: true)
RENEWAL_FAILURE_ALERTS_ENABLED=true
```

## Email Alerts Setup

### Configuration

```bash
# Comma-separated list of email addresses to receive alerts
RENEWAL_FAILURE_EMAIL_RECIPIENTS=admin@example.com,ops@example.com
```

### SMTP Configuration

Ensure your Laravel SMTP configuration is properly set up in `.env`:

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@telemed.pro
MAIL_FROM_NAME="TeleMed Pro"
```

### Email Alert Content

Email alerts include:
- User name and ID
- Renewal amount
- Failure reason
- Retry attempt count
- Saga UUID (for tracking)
- Correlation ID (for debugging)

### Testing Email Alerts

```bash
# Test email configuration
php artisan tinker
>>> Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
```

## Slack Alerts Setup

### Configuration

```bash
# Slack webhook URL for renewal failure notifications
RENEWAL_FAILURE_SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

### Getting Your Slack Webhook URL

1. Go to your Slack workspace settings
2. Navigate to **Apps & integrations** → **Manage apps**
3. Search for "Incoming Webhooks" or create a new app
4. Click **Create New Webhook**
5. Select the channel where alerts should be posted
6. Copy the webhook URL and add it to your `.env` file

### Slack Alert Format

Slack alerts are formatted as rich messages with:
- Color-coded attachment (red for danger)
- User information
- Amount and reason
- Attempt count
- Saga and correlation IDs

### Testing Slack Alerts

```bash
# Manually trigger a test alert
php artisan tinker
>>> event(new \App\Domain\Subscription\Events\RenewalFailureAlert(
    sagaUuid: 'test-uuid',
    userId: 1,
    amount: 99.99,
    reason: 'card_declined',
    attemptNumber: 3,
    maxAttempts: 5,
    correlationId: 'corr-123'
));
```

## PagerDuty Alerts Setup

### Configuration

```bash
# PagerDuty integration key (routing key)
RENEWAL_FAILURE_PAGERDUTY_KEY=your_routing_key_here
```

### Getting Your PagerDuty Routing Key

1. Log in to your PagerDuty account
2. Go to **Services** and select or create a service
3. Navigate to **Integrations** tab
4. Add a new integration with type "Events API v2"
5. Copy the **Routing Key** and add it to your `.env` file

### PagerDuty Alert Details

PagerDuty alerts include:
- Summary: "Subscription renewal failed for {user_name}"
- Severity: error
- Source: TeleMed Pro
- Custom details with user ID, amount, reason, saga UUID, and correlation ID

### Deduplication

Alerts are deduplicated using the saga UUID to prevent duplicate incidents for the same renewal failure.

## Monitoring and Troubleshooting

### View Alert Logs

```bash
# Check recent alert activity
tail -f storage/logs/laravel.log | grep "Renewal failure alert"
```

### Common Issues

**Alerts not being sent:**
- Verify `RENEWAL_FAILURE_ALERTS_ENABLED=true`
- Check that at least one channel is configured
- Review logs for error messages

**Email alerts not received:**
- Verify SMTP credentials are correct
- Check spam/junk folders
- Test SMTP connection: `php artisan mail:test`

**Slack alerts not appearing:**
- Verify webhook URL is correct and active
- Check Slack channel permissions
- Test webhook with curl:
  ```bash
  curl -X POST -H 'Content-type: application/json' \
    --data '{"text":"Test"}' \
    YOUR_WEBHOOK_URL
  ```

**PagerDuty alerts not triggering:**
- Verify routing key is correct
- Check PagerDuty service is active
- Verify integration is enabled in PagerDuty

### Debug Mode

Enable detailed logging for alert processing:

```bash
# In config/logging.php, set channel to 'debug'
# Or temporarily modify the handler:
Log::channel('debug')->info('Alert details', $event->toArray());
```

## Admin Dashboard Widget

Failed renewals are also displayed in the admin dashboard widget at `/admin/subscription-configuration`. This widget shows:

- Total failures in the selected period
- Total amount at risk
- Filterable by days (7, 14, 30)
- Detailed table with user, amount, reason, and attempts

## Best Practices

1. **Multiple Channels**: Configure multiple alert channels for redundancy
2. **Regular Testing**: Test alerts monthly to ensure they're working
3. **Monitor Trends**: Track renewal failure rates to identify patterns
4. **Escalation**: Use PagerDuty for critical failures requiring immediate action
5. **Documentation**: Keep alert recipient lists updated
6. **Retention**: Archive alert logs for compliance and analysis

## Related Documentation

- [Subscription Renewal System](./SUBSCRIPTION_RENEWAL_SYSTEM.md)
- [Event Sourcing & CQRS](./event-sourcing-and-cqrs-foundation.md)
- [Saga Pattern Implementation](./SAGA_SUMMARY.md)

