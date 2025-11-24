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
        Schema::table('signup_read_model', function (Blueprint $table) {
            // Rename medication_id to medication_name and change type to string
            if (Schema::hasColumn('signup_read_model', 'medication_id')) {
                $table->renameColumn('medication_id', 'medication_name');
            }

            // Change the column type to string if it exists
            if (Schema::hasColumn('signup_read_model', 'medication_name')) {
                $table->string('medication_name')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signup_read_model', function (Blueprint $table) {
            // Revert back to medication_id
            if (Schema::hasColumn('signup_read_model', 'medication_name')) {
                $table->renameColumn('medication_name', 'medication_id');
            }
        });
    }
};
