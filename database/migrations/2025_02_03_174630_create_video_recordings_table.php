<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('filename');
            $table->string('path');
            $table->integer('duration')->nullable();
            $table->string('format')->nullable();
            $table->timestamps();
        });
    }
};
