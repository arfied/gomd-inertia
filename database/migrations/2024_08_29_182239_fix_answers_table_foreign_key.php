<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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
        Schema::table('answers', function (Blueprint $table) {
            $table->dropForeign(['user_service_id']);
            
            $table->foreign('user_service_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
