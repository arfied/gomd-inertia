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
        Schema::table('insurance_claims', function (Blueprint $table) {
            // Drop existing columns
            $table->dropForeign(['prescription_id']);
            $table->dropColumn('insurance_provider');
            
            // Add new columns
            $table->foreignId('insurance_id')->after('prescription_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_approved', 10, 2)->nullable()->after('amount_claimed');
            $table->decimal('amount_paid', 10, 2)->nullable()->after('amount_approved');
            $table->decimal('patient_responsibility', 10, 2)->nullable()->after('amount_paid');
            $table->string('denial_reason')->nullable()->after('resolution_date');
            $table->json('supporting_documents')->nullable()->after('denial_reason');
            
            // Recreate foreign key
            $table->foreign('prescription_id')->references('id')->on('prescriptions')->onDelete('cascade');
            
            // Make claim_number unique
            $table->unique('claim_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurance_claims', function (Blueprint $table) {
            $table->dropForeign(['insurance_id']);
            $table->dropColumn([
                'insurance_id',
                'amount_approved',
                'amount_paid',
                'patient_responsibility',
                'denial_reason',
                'supporting_documents'
            ]);
            $table->string('insurance_provider')->after('prescription_id');
            $table->dropUnique(['claim_number']);
        });
    }
};
