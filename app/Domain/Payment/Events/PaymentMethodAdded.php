<?php

namespace App\Domain\Payment\Events;

use App\Domain\Events\DomainEvent;

/**
 * Domain event raised when a payment method is added.
 *
 * Payload should include:
 * - user_id: The user who added the payment method
 * - type: Payment method type (credit_card, ach, invoice)
 * - is_default: Whether this is the default payment method
 * - For credit cards: cc_last_four, cc_brand, cc_expiration_month, cc_expiration_year, cc_token
 * - For ACH: ach_account_name, ach_account_type, ach_routing_number_last_four, ach_account_number_last_four, ach_token
 * - For invoices: invoice_email, invoice_company_name, invoice_contact_name, invoice_phone, invoice_billing_address, invoice_payment_terms
 */
class PaymentMethodAdded extends DomainEvent
{
    public static function aggregateType(): string
    {
        return 'payment_method';
    }

    public static function eventType(): string
    {
        return 'payment_method.added';
    }
}

