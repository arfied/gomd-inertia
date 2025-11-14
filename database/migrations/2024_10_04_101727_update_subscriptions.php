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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('prescription_limit')->nullable()->after('is_trial');
            $table->decimal('medication_coverage_limit', 10, 2)->nullable()->after('prescription_limit');
            $table->integer('used_prescription_count')->default(0)->after('medication_coverage_limit');
            $table->decimal('used_medication_coverage', 10, 2)->default(0)->after('used_prescription_count');
            $table->timestamp('coverage_reset_date')->nullable()->after('used_medication_coverage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('prescription_limit', 'medication_coverage_limit', 'used_prescription_count', 'used_medication_coverage', 'coverage_reset_date');
        });
    }
};
