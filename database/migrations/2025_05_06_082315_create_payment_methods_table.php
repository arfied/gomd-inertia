<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // credit_card, ach, invoice
            $table->boolean('is_default')->default(false);

            // Credit Card specific fields
            $table->string('cc_last_four')->nullable();
            $table->string('cc_brand')->nullable();
            $table->string('cc_expiration_month')->nullable();
            $table->string('cc_expiration_year')->nullable();
            $table->string('cc_token')->nullable(); // Authorize.net payment profile ID

            // ACH specific fields
            $table->string('ach_account_name')->nullable();
            $table->string('ach_account_type')->nullable(); // checking, savings
            $table->string('ach_routing_number_last_four')->nullable();
            $table->string('ach_account_number_last_four')->nullable();
            $table->string('ach_token')->nullable(); // Authorize.net payment profile ID for ACH

            // Invoice specific fields
            $table->string('invoice_email')->nullable();
            $table->string('invoice_company_name')->nullable();
            $table->string('invoice_contact_name')->nullable();
            $table->string('invoice_phone')->nullable();
            $table->text('invoice_billing_address')->nullable();
            $table->string('invoice_payment_terms')->nullable(); // net30, net60, etc.

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
