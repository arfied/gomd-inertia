<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyMember extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subscription_id',
        'primary_user_id',
        'dependent_user_id',
        'relationship_type',
        'date_of_birth',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the subscription that this family member belongs to.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the primary user (account holder).
     */
    public function primaryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_user_id');
    }

    /**
     * Get the dependent user.
     */
    public function dependentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dependent_user_id');
    }

    /**
     * Check if the dependent is a minor (under 18).
     *
     * @return bool
     */
    public function isMinor(): bool
    {
        return $this->date_of_birth && $this->date_of_birth->age < 18;
    }

    /**
     * Check if the dependent is an adult (18-23).
     *
     * @return bool
     */
    public function isAdult(): bool
    {
        return $this->date_of_birth && $this->date_of_birth->age >= 18 && $this->date_of_birth->age <= 23;
    }

    /**
     * Check if the dependent is eligible (23 or younger, or an older dependent).
     *
     * @return bool
     */
    public function isEligibleDependent(): bool
    {
        if (!$this->date_of_birth) {
            return false;
        }

        // Either 23 or younger, or an older dependent
        return $this->date_of_birth->age <= 23 || $this->isOlderDependent();
    }

    /**
     * Check if the dependent is an older dependent (24 or older).
     *
     * @return bool
     */
    public function isOlderDependent(): bool
    {
        return $this->date_of_birth && $this->date_of_birth->age >= 24;
    }

    /**
     * Get the age of the dependent.
     *
     * @return int|null
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }
}
