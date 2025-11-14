<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'is_default',
        // Credit Card fields
        'cc_last_four',
        'cc_brand',
        'cc_expiration_month',
        'cc_expiration_year',
        'cc_token',
        // ACH fields
        'ach_account_name',
        'ach_account_type',
        'ach_routing_number_last_four',
        'ach_account_number_last_four',
        'ach_token',
        // Invoice fields
        'invoice_email',
        'invoice_company_name',
        'invoice_contact_name',
        'invoice_phone',
        'invoice_billing_address',
        'invoice_payment_terms',
        // General fields
        'meta_data',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'cc_token',
        'ach_token',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'preferences' => 'array',
    ];

    /**
     * Get the user that owns the payment method.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this payment method is a credit card.
     */
    public function isCreditCard(): bool
    {
        return $this->type === 'credit_card';
    }

    /**
     * Check if this payment method is ACH.
     */
    public function isAch(): bool
    {
        return $this->type === 'ach';
    }

    /**
     * Check if this payment method is Invoice.
     */
    public function isInvoice(): bool
    {
        return $this->type === 'invoice';
    }

    /**
     * Get a display name for the payment method.
     */
    public function getDisplayName(): string
    {
        if ($this->isCreditCard()) {
            return "{$this->cc_brand} ending in {$this->cc_last_four}";
        } elseif ($this->isAch()) {
            return "{$this->ach_account_type} account ending in {$this->ach_account_number_last_four}";
        } elseif ($this->isInvoice()) {
            return "Invoice to {$this->invoice_company_name}";
        }

        return "Unknown payment method";
    }

    /**
     * Get a preference value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getPreference(string $key, $default = null)
    {
        return data_get($this->preferences, $key, $default);
    }

    /**
     * Set a preference value
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setPreference(string $key, $value)
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->preferences = $preferences;
        $this->save();

        return $this;
    }

    /**
     * Check if a preference exists
     *
     * @param string $key
     * @return bool
     */
    public function hasPreference(string $key): bool
    {
        return data_get($this->preferences, $key) !== null;
    }

    /**
     * Get all preferences for a specific service
     *
     * @param string $service
     * @return array
     */
    public function getServicePreferences(string $service): array
    {
        return $this->getPreference("services.{$service}", []);
    }

    /**
     * Set preferences for a specific service
     *
     * @param string $service
     * @param array $preferences
     * @return $this
     */
    public function setServicePreferences(string $service, array $preferences)
    {
        return $this->setPreference("services.{$service}", $preferences);
    }

    /**
     * Check if this payment method is preferred for a specific service
     *
     * @param string $service
     * @return bool
     */
    public function isPreferredForService(string $service): bool
    {
        return $this->getPreference("services.{$service}.is_preferred", false);
    }

    /**
     * Set this payment method as preferred for a specific service
     *
     * @param string $service
     * @param bool $isPreferred
     * @return $this
     */
    public function setPreferredForService(string $service, bool $isPreferred = true)
    {
        return $this->setPreference("services.{$service}.is_preferred", $isPreferred);
    }
}
