<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('height', 5, 2); // in inches, max 999.99
            $table->decimal('weight', 6, 2); // in pounds, max 9999.99
            $table->timestamp('measured_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_measurements');
    }
};
