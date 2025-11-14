<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('adverse_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_reported_medication_id')->constrained('user_reported_medications')->onDelete('cascade');
            $table->text('reaction_details');
            $table->date('reaction_date')->nullable();
            $table->string('severity')->nullable(); // e.g., 'Mild', 'Moderate', 'Severe'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('adverse_reactions');
    }
};
