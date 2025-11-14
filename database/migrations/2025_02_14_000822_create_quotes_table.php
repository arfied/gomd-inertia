<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('job_title');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->string('suite_number')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->integer('num_employees');
            $table->string('industry');
            $table->string('other_industry')->nullable();
            $table->string('current_insurance');
            $table->string('happy_with_coverage')->nullable();
            $table->text('coverage_issues')->nullable();
            $table->string('contact_method');
            $table->string('budget')->nullable();
            $table->string('current_provider')->nullable();
            $table->string('start_date')->nullable();
            $table->string('referral_source');
            $table->string('other_referral')->nullable();
            $table->text('additional_info')->nullable();
            $table->timestamps();
        });
    }
};
