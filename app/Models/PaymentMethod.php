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
        'verification_status',
        'verification_attempts',
        'last_verification_attempt_at',
        'archived_at',
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
        'is_default' => 'boolean',
        'verification_attempts' => 'integer',
    ];

    /**
     * Get the name of the "deleted at" column.
     */
    public function getDeletedAtColumn(): string
    {
        return 'archived_at';
    }

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

    /**
     * Check if this payment method is valid for processing
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->isCreditCard()) {
            return $this->isCreditCardValid();
        } elseif ($this->isAch()) {
            return $this->isAchValid();
        } elseif ($this->isInvoice()) {
            return $this->isInvoiceValid();
        }

        return false;
    }

    /**
     * Check if credit card is valid (not expired, has token)
     *
     * @return bool
     */
    private function isCreditCardValid(): bool
    {
        if (!$this->cc_token || !$this->cc_expiration_month || !$this->cc_expiration_year) {
            return false;
        }

        // Check if card is expired
        $expiryDate = \Carbon\Carbon::createFromDate(
            $this->cc_expiration_year,
            $this->cc_expiration_month,
            1
        )->endOfMonth();

        return $expiryDate->isFuture();
    }

    /**
     * Check if ACH is valid (has token, account info, and is verified)
     *
     * @return bool
     */
    private function isAchValid(): bool
    {
        // ACH must have token and account info
        if (empty($this->ach_token) || empty($this->ach_account_number_last_four)) {
            return false;
        }

        // ACH must be verified (micro-deposit verification completed)
        return $this->isVerified();
    }

    /**
     * Check if invoice is valid (has email and company)
     *
     * @return bool
     */
    private function isInvoiceValid(): bool
    {
        return !empty($this->invoice_email) && !empty($this->invoice_company_name);
    }

    /**
     * Get validation error message if payment method is invalid
     *
     * @return string|null
     */
    public function getValidationError(): ?string
    {
        if ($this->isCreditCard()) {
            if (!$this->cc_token) {
                return 'Credit card token is missing';
            }
            if (!$this->cc_expiration_month || !$this->cc_expiration_year) {
                return 'Credit card expiration date is missing';
            }
            $expiryDate = \Carbon\Carbon::createFromDate(
                $this->cc_expiration_year,
                $this->cc_expiration_month,
                1
            )->endOfMonth();
            if ($expiryDate->isPast()) {
                return 'Credit card has expired';
            }
        } elseif ($this->isAch()) {
            if (!$this->ach_token) {
                return 'ACH token is missing';
            }
            if (!$this->ach_account_number_last_four) {
                return 'ACH account information is missing';
            }
            if ($this->isPendingVerification()) {
                return 'ACH account is pending micro-deposit verification';
            }
            if ($this->isVerificationFailed()) {
                return 'ACH account verification failed';
            }
            if (!$this->isVerified()) {
                return 'ACH account is not verified';
            }
        } elseif ($this->isInvoice()) {
            if (!$this->invoice_email) {
                return 'Invoice email is missing';
            }
            if (!$this->invoice_company_name) {
                return 'Invoice company name is missing';
            }
        }

        return null;
    }

    /**
     * Check if payment method is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if payment method is pending verification
     */
    public function isPendingVerification(): bool
    {
        return $this->verification_status === 'pending';
    }

    /**
     * Check if payment method verification failed
     */
    public function isVerificationFailed(): bool
    {
        return $this->verification_status === 'failed';
    }

    /**
     * Mark payment method as verified
     */
    public function markAsVerified(): self
    {
        $this->update([
            'verification_status' => 'verified',
            'last_verification_attempt_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark payment method verification as failed
     */
    public function markVerificationFailed(): self
    {
        $this->update([
            'verification_status' => 'failed',
            'verification_attempts' => $this->verification_attempts + 1,
            'last_verification_attempt_at' => now(),
        ]);

        return $this;
    }

    /**
     * Archive payment method (soft delete)
     */
    public function archive(): self
    {
        $this->update(['archived_at' => now()]);
        return $this;
    }

    /**
     * Check if payment method is archived
     */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * Scope to get only active (non-archived) payment methods
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Scope to get only verified payment methods
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope to get only pending verification payment methods
     */
    public function scopePendingVerification($query)
    {
        return $query->where('verification_status', 'pending');
    }
}
