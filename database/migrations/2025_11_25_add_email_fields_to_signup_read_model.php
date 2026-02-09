<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signup_read_model', function (Blueprint $table) {
            // Add email and user_created_at columns if they don't exist
            if (!Schema::hasColumn('signup_read_model', 'user_email')) {
                $table->string('user_email')->nullable()->after('user_id');
                $table->index('user_email');
            }

            if (!Schema::hasColumn('signup_read_model', 'user_created_at')) {
                $table->timestamp('user_created_at')->nullable()->after('user_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signup_read_model', function (Blueprint $table) {
            if (Schema::hasColumn('signup_read_model', 'user_email')) {
                $table->dropIndex(['user_email']);
                $table->dropColumn('user_email');
            }

            if (Schema::hasColumn('signup_read_model', 'user_created_at')) {
                $table->dropColumn('user_created_at');
            }
        });
    }
};

