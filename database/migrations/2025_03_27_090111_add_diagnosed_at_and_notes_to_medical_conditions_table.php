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
            $table->date('diagnosed_at')->nullable()->after('condition_name');
            $table->text('notes')->nullable()->after('diagnosed_at');
            $table->foreignId('condition_id')->nullable()->after('notes')
                  ->constrained('conditions')->onDelete('set null');
            $table->boolean('is_custom')->default(false)->after('condition_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_conditions', function (Blueprint $table) {
            $table->dropForeign(['condition_id']);
            $table->dropColumn(['diagnosed_at', 'notes', 'condition_id', 'is_custom']);
        });
    }
};
