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
            // Change user_id from unsignedBigInteger to string to support both
            // authenticated users (integer ID) and unauthenticated users (UUID)
            $table->string('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signup_read_model', function (Blueprint $table) {
            // Revert to unsignedBigInteger
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }
};
