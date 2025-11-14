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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained();
            $table->string('question');
            $table->text('additional_text')->nullable();
            $table->string('type'); // text, radio, checkbox, select
            // $table->json('options')->nullable();
            $table->boolean('required')->default(true);
            $table->bigInteger('parent_question_id')->nullable();
            $table->text('parent_answer_value')->nullable();
            $table->integer('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
