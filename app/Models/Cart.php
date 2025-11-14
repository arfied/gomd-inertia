<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'medication_variant_id',
        'quantity'
    ];

    public function medicationVariant()
    {
        return $this->belongsTo(MedicationVariant::class);
    }

    public function medicalQuestionnaire()
    {
        return $this->hasOne(MedicalQuestionnaire::class, 'cart_session_id', 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($cart) {
            // If user is authenticated, ensure user_id is set for new cart items
            if (Auth::check() && empty($cart->user_id)) {
                $cart->user_id = Auth::id();
            }
        });

        // We're not updating existing cart items with user_id as per the requirement
        // Only new cart items will have the user_id set
    }
}
