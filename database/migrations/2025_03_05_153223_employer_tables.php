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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->boolean('trial_enabled')->default(false);
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('business_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('owner_fname');
            $table->string('owner_lname');
            $table->string('billing_contact_email');
            $table->string('billing_contact_phone')->nullable();
            $table->string('hr_contact_email')->nullable();
            $table->string('hr_contact_phone')->nullable();
            $table->timestamps();
        });

        Schema::create('business_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->integer('plan_quantity');
            $table->integer('price_per_plan')->default(2000); // Stored in cents (20.00 dollars = 2000 cents)
            $table->integer('total_price'); // Stored in cents
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('business_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('status')->default('pending'); // ['active', 'pending', 'terminated']
            $table->timestamp('terminated_at')->nullable();
            $table->text('termination_reason')->nullable();
            $table->boolean('transitioned_to_consumer')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add business_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_id');
        });

        Schema::dropIfExists('business_employees');
        Schema::dropIfExists('business_plans');
        Schema::dropIfExists('business_contacts');
        Schema::dropIfExists('businesses');
    }
};
