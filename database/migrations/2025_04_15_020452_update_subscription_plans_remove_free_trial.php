<?php

use App\Models\SubscriptionPlan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update plans with ID 7 (Individual Monthly)
        $this->updatePlanBenefits(
            7,
            ["Unlimited Virtual Doctor Visits", "Unlimited medications", "Treatments for over 200 conditions"],
            ["30-day Money Back Guarantee", "No hidden fees"]
        );

        // Update plans with ID 8 (Family Monthly)
        $this->updatePlanBenefits(
            8,
            ["Covers 2 adults and kids under 21", "Unlimited Virtual Doctor Visits", "Unlimited medications", "Treatments for over 200 conditions"],
            ["30-day Money Back Guarantee", "No hidden fees"]
        );

        // Update plans with ID 9 (Individual Annual)
        $this->updatePlanBenefits(
            9,
            ["Unlimited Virtual Doctor Visits", "Unlimited medications", "Treatments for over 200 conditions", "Includes 1-year supply of medication"],
            ["30-day Money Back Guarantee", "No hidden fees"]
        );

        // Update plans with ID 10 (Family Annual)
        $this->updatePlanBenefits(
            10,
            ["Covers 2 adults and kids under 21", "Unlimited Virtual Doctor Visits", "Unlimited medications", "Treatments for over 200 conditions", "Includes 1-year supply of medication"],
            ["30-day Money Back Guarantee", "No hidden fees"]
        );

        // Update plans with ID 11 (Single Treatment Plan)
        $this->updatePlanBenefits(
            11,
            ["Virtual Doctor consultation for a single treatment", "Includes 6-month supply of medication", "No recurring billing"],
            ["30-day Money Back Guarantee", "No hidden fees"]
        );

        // Update any other plans that might have free trial benefits
        $this->removeFreeTrial();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore plans with ID 7 (Individual Monthly)
        $this->updatePlanBenefits(
            7,
            ["Unlimited Virtual Doctor Visits", "Unlimited medications", "Treatments for over 200 conditions"],
            ["7-Day Free Trial – No Charge Today!", "30-day Money Back Guarantee"]
        );

        // Restore plans with ID 8 (Family Monthly)
        $this->updatePlanBenefits(
            8,
            ["Covers 2 adults and kids under 21", "Unlimited Virtual Doctor Visits", "Unlimited medications", "Treatments for over 200 conditions"],
            ["7-Day Free Trial – No Charge Today!", "30-day Money Back Guarantee"]
        );

        // Restore plans with ID 9 (Individual Annual)
        $this->updatePlanBenefits(
            9,
            ["Unlimited Virtual Doctor Visits", "Unlimited medications", "Treatments for over 200 conditions", "Includes 1-year supply of medication"],
            ["7-Day Free Trial – No Charge Today!", "30-day Money Back Guarantee"]
        );

        // Restore plans with ID 10 (Family Annual)
        $this->updatePlanBenefits(
            10,
            ["Covers 2 adults and kids under 21", "Unlimited Virtual Doctor Visits", "Unlimited medications", "Treatments for over 200 conditions", "Includes 1-year supply of medication"],
            ["7-Day Free Trial – No Charge Today!", "30-day Money Back Guarantee"]
        );

        // Restore plans with ID 11 (Single Treatment Plan)
        $this->updatePlanBenefits(
            11,
            ["Virtual Doctor consultation for a single treatment", "Includes 6-month supply of medication", "No recurring billing"],
            ["7-Day Free Trial – No Charge Today!", "30-day Money Back Guarantee"]
        );
    }

    /**
     * Update the features and benefits of a subscription plan
     *
     * @param int $planId
     * @param array $features
     * @param array $benefits
     * @return void
     */
    private function updatePlanBenefits(int $planId, array $features, array $benefits): void
    {
        DB::table('subscription_plans')
            ->where('id', $planId)
            ->update([
                'features' => json_encode($features),
                'benefits' => json_encode($benefits),
                'updated_at' => now()
            ]);
    }

    /**
     * Remove free trial benefits from all subscription plans
     *
     * @return void
     */
    private function removeFreeTrial(): void
    {
        // Get all plans
        $plans = DB::table('subscription_plans')->get();

        foreach ($plans as $plan) {
            $benefits = json_decode($plan->benefits, true);

            if (is_array($benefits)) {
                // Remove any benefit that mentions "Free Trial"
                $benefits = array_filter($benefits, function($benefit) {
                    return !str_contains($benefit, 'Free Trial');
                });

                // If we removed benefits, make sure we have at least two
                if (count($benefits) < 2) {
                    $benefits[] = "30-day Money Back Guarantee";
                    $benefits[] = "No hidden fees";
                }

                // Re-index array
                $benefits = array_values($benefits);

                // Update the plan
                DB::table('subscription_plans')
                    ->where('id', $plan->id)
                    ->update([
                        'benefits' => json_encode($benefits),
                        'updated_at' => now()
                    ]);
            }
        }
    }
};
