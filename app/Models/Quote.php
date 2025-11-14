<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'company_name',
        'contact_name', 
        'job_title',
        'email',
        'phone',
        'address',
        'suite_number',
        'city',
        'state', 
        'zip_code',
        'num_employees',
        'industry',
        'other_industry',
        'current_insurance',
        'happy_with_coverage',
        'coverage_issues',
        'contact_method',
        'budget',
        'current_provider',
        'start_date',
        'referral_source',
        'other_referral',
        'additional_info'
    ];
}
