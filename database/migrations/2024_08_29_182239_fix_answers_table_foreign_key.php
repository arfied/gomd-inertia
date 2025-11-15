<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // SQLite (used in tests) does not support dropping foreign keys by name.
        // Skip this migration's foreign key alteration when running on SQLite.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('answers', function (Blueprint $table) {
            $table->dropForeign('answers_user_id_foreign');

            $table->foreign('user_service_id')
                  ->references('id')
                  ->on('user_services')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('answers', function (Blueprint $table) {
            $table->dropForeign(['user_service_id']);

            $table->foreign('user_service_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
