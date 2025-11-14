<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'last_four',
        'brand',
        'expiration_month',
        'expiration_year',
        'is_default',
        'token',
    ];

    public static function createFromRequest($user, $request)
    {
        $creditCard = new self();
        $creditCard->last_four = $request->card_number;
        $creditCard->brand = $creditCard->getCardBrand($request->card_number);
        $creditCard->expiration_month = $request->expiration_month;
        $creditCard->expiration_year = $request->expiration_year;
        $creditCard->token = '';
        $creditCard->is_default = $user->creditCards()->count() == 0;

        $user->creditCards()->save($creditCard);

        return $creditCard;
    }

    public function getCardBrand($cardNumber)
    {
        $brand = 'Unknown';
        if (preg_match('/^4/', $cardNumber)) {
            $brand = 'Visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            $brand = 'Mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            $brand = 'American Express';
        } elseif (preg_match('/^6(?:011|5)/', $cardNumber)) {
            $brand = 'Discover';
        }
        return $brand;
    }

    public function getCcAttribute(): string
    {
        return substr($this->last_four, -4);
    }
}
