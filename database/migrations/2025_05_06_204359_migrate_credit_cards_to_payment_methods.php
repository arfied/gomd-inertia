<?php

use App\Models\CreditCard;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration moves all credit card data from the credit_cards table
     * to the payment_methods table.
     */
    public function up(): void
    {
        // Get all credit cards, including soft deleted ones
        $creditCards = DB::table('credit_cards')->get();

        $count = 0;
        $errors = 0;

        foreach ($creditCards as $creditCard) {
            try {
                // Check if a payment method with this credit card already exists
                $existingPaymentMethod = DB::table('payment_methods')
                    ->where('user_id', $creditCard->user_id)
                    ->where('type', 'credit_card')
                    ->where('cc_last_four', $creditCard->last_four)
                    ->where('cc_token', $creditCard->token)
                    ->first();

                if ($existingPaymentMethod) {
                    Log::info("Payment method already exists for credit card ID {$creditCard->id}. Skipping.");
                    continue;
                }

                // Create a new payment method record
                $paymentMethodData = [
                    'user_id' => $creditCard->user_id,
                    'type' => 'credit_card',
                    'is_default' => $creditCard->is_default,
                    'cc_last_four' => $creditCard->last_four,
                    'cc_brand' => $creditCard->brand,
                    'cc_expiration_month' => $creditCard->expiration_month,
                    'cc_expiration_year' => $creditCard->expiration_year,
                    'cc_token' => $creditCard->token,
                    'created_at' => $creditCard->created_at,
                    'updated_at' => $creditCard->updated_at,
                ];

                // If the credit card is soft deleted, also soft delete the payment method
                if (isset($creditCard->deleted_at) && $creditCard->deleted_at) {
                    $paymentMethodData['deleted_at'] = $creditCard->deleted_at;
                }

                DB::table('payment_methods')->insert($paymentMethodData);
                $count++;

                Log::info("Migrated credit card ID {$creditCard->id} to payment_methods table.");
            } catch (\Exception $e) {
                Log::error("Error migrating credit card ID {$creditCard->id}: " . $e->getMessage());
                $errors++;
            }
        }

        Log::info("Credit card migration completed. Migrated: $count, Errors: $errors");
    }

    /**
     * Reverse the migrations.
     *
     * This will remove all credit card payment methods that were migrated from the credit_cards table.
     * It will NOT affect any payment methods that were created directly in the payment_methods table.
     */
    public function down(): void
    {
        // Get all credit cards
        $creditCards = DB::table('credit_cards')->get();

        foreach ($creditCards as $creditCard) {
            // Find and delete the corresponding payment method
            DB::table('payment_methods')
                ->where('user_id', $creditCard->user_id)
                ->where('type', 'credit_card')
                ->where('cc_last_four', $creditCard->last_four)
                ->where('cc_token', $creditCard->token)
                ->delete();

            Log::info("Removed migrated payment method for credit card ID {$creditCard->id}.");
        }

        Log::info("Credit card migration reversal completed.");
    }
};
