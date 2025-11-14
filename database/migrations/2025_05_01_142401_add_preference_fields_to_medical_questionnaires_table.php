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
        Schema::table('medical_questionnaires', function (Blueprint $table) {
            $table->string('treatment_preference')->nullable()->after('treatment_goals');
            $table->string('medication_preference')->nullable()->after('treatment_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_questionnaires', function (Blueprint $table) {
            $table->dropColumn('treatment_preference');
            $table->dropColumn('medication_preference');
        });
    }
};
