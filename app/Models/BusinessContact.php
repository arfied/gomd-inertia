<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessContact extends Model
{
    use HasFactory;

    const TYPES = [
        'billing' => 'Billing Contact',
        'hr' => 'HR Contact',
        'primary' => 'Primary Contact',
        'technical' => 'Technical Contact',
        'other' => 'Other Contact',
    ];

    protected $fillable = [
        'business_id',
        'owner_fname',
        'owner_lname',
        'billing_contact_email',
        'billing_contact_phone',
        'hr_contact_email',
        'hr_contact_phone',
        'hr_fname',
        'hr_lname',
    ];

    /**
     * Get the business that owns the contact.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
