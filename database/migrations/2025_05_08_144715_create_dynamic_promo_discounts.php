<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Discount;
use App\Models\SubscriptionPlan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the ExtraHelp discount with high priority
        $discount = Discount::create([
            'name' => 'ExtraHelp Promo',
            'promo_code' => 'extrahelp',
            'description' => '$15 off all subscription plans',
            'type' => 'fixed',
            'value' => 15.00,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => Discount::STATUS_ACTIVE,
            'priority' => 100, // High priority to override other discounts
        ]);

        // Attach all active subscription plans to this discount
        $subscriptionPlans = SubscriptionPlan::where('is_active', true)->get();
        $discount->subscriptionPlans()->attach($subscriptionPlans->pluck('id')->toArray());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Find and delete the ExtraHelp discount
        $discount = Discount::where('promo_code', 'extrahelp')->first();
        if ($discount) {
            $discount->subscriptionPlans()->detach();
            $discount->delete();
        }
    }
};
