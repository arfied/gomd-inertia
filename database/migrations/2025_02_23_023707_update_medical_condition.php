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
        Schema::table('medical_conditions', function (Blueprint $table) {
            $table->date('symtom_start_date')->nullable()->after('is_chronic');
        });
    }
};
