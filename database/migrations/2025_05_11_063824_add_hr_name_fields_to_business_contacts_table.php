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
        Schema::table('business_contacts', function (Blueprint $table) {
            $table->string('hr_fname')->nullable()->after('hr_contact_email');
            $table->string('hr_lname')->nullable()->after('hr_fname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_contacts', function (Blueprint $table) {
            $table->dropColumn(['hr_fname', 'hr_lname']);
        });
    }
};
