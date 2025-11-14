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
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('primary_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('dependent_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('relationship_type', ['spouse', 'child', 'other'])->default('other');
            $table->date('date_of_birth')->nullable();
            $table->timestamps();
            
            // Add unique constraint to prevent duplicate relationships
            $table->unique(['primary_user_id', 'dependent_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
