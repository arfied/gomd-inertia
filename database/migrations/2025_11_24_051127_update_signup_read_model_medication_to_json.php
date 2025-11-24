<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('signup_read_model', function (Blueprint $table) {
                $table->json('medication_name')->nullable()->change();
            });
        } else {
            // For MySQL, we can modify the column directly
            DB::statement('ALTER TABLE signup_read_model MODIFY medication_name JSON NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('signup_read_model', function (Blueprint $table) {
                $table->string('medication_name')->nullable()->change();
            });
        } else {
            DB::statement('ALTER TABLE signup_read_model MODIFY medication_name VARCHAR(255) NULL');
        }
    }
};
