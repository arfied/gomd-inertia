<?php

namespace App\Services\AuthorizeNet\Validation;

use App\Services\AuthorizeNet\Exceptions\ValidationException;

/**
 * Validator for payment-related inputs
 * Validates credit card numbers, routing numbers, and other payment data
 */
class PaymentValidator
{
    /**
     * Validate credit card number using Luhn algorithm
     *
     * @param string $cardNumber
     * @return bool
     */
    public static function validateCardNumber(string $cardNumber): bool
    {
        // Remove spaces and dashes
        $cardNumber = preg_replace('/[\s\-]/', '', $cardNumber);

        // Check if it's numeric and between 13-19 digits
        if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
            return false;
        }

        // Luhn algorithm
        $sum = 0;
        $isEven = false;

        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $digit = (int)$cardNumber[$i];

            if ($isEven) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $isEven = !$isEven;
        }

        return $sum % 10 === 0;
    }

    /**
     * Validate expiration date
     *
     * @param string $month
     * @param string $year
     * @return bool
     */
    public static function validateExpirationDate(string $month, string $year): bool
    {
        // Validate month (01-12)
        if (!preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
            return false;
        }

        // Validate year (2 or 4 digits)
        if (!preg_match('/^\d{2}(\d{2})?$/', $year)) {
            return false;
        }

        // Convert 2-digit year to 4-digit
        $year = strlen($year) === 2 ? '20' . $year : $year;

        // Check if the card is not expired
        $expiryDate = \DateTime::createFromFormat('Y-m-t', $year . '-' . $month . '-01');
        return $expiryDate && $expiryDate > new \DateTime();
    }

    /**
     * Validate CVV
     *
     * @param string $cvv
     * @return bool
     */
    public static function validateCvv(string $cvv): bool
    {
        // CVV should be 3-4 digits
        return preg_match('/^\d{3,4}$/', $cvv) === 1;
    }

    /**
     * Validate routing number (US)
     *
     * @param string $routingNumber
     * @return bool
     */
    public static function validateRoutingNumber(string $routingNumber): bool
    {
        // Routing number should be 9 digits
        if (!preg_match('/^\d{9}$/', $routingNumber)) {
            return false;
        }

        // Validate using checksum algorithm
        $digits = str_split($routingNumber);
        $sum = ($digits[0] * 3 + $digits[1] * 7 + $digits[2] * 1) +
               ($digits[3] * 3 + $digits[4] * 7 + $digits[5] * 1) +
               ($digits[6] * 3 + $digits[7] * 7 + $digits[8] * 1);

        return $sum % 10 === 0;
    }

    /**
     * Validate account number
     *
     * @param string $accountNumber
     * @return bool
     */
    public static function validateAccountNumber(string $accountNumber): bool
    {
        // Account number should be 5-17 digits
        return preg_match('/^\d{5,17}$/', $accountNumber) === 1;
    }

    /**
     * Validate email address
     *
     * @param string $email
     * @return bool
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitize card number (remove spaces and dashes)
     *
     * @param string $cardNumber
     * @return string
     */
    public static function sanitizeCardNumber(string $cardNumber): string
    {
        return preg_replace('/[\s\-]/', '', $cardNumber);
    }

    /**
     * Mask card number for logging (show only last 4 digits)
     *
     * @param string $cardNumber
     * @return string
     */
    public static function maskCardNumber(string $cardNumber): string
    {
        $cardNumber = self::sanitizeCardNumber($cardNumber);
        return 'XXXX-XXXX-XXXX-' . substr($cardNumber, -4);
    }
}

