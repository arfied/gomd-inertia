<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, clean up existing duplicate transaction IDs
        $duplicates = DB::table('transactions')
            ->select('transaction_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('transaction_id')
            ->where('transaction_id', '!=', '')
            ->groupBy('transaction_id')
            ->having('count', '>', 1)
            ->get();
            
        foreach ($duplicates as $duplicate) {
            // Get all transactions with this ID
            $transactions = DB::table('transactions')
                ->where('transaction_id', $duplicate->transaction_id)
                ->orderBy('id')
                ->get();
                
            // Keep the first one, delete the rest
            $firstId = $transactions->first()->id;
            
            DB::table('transactions')
                ->where('transaction_id', $duplicate->transaction_id)
                ->where('id', '!=', $firstId)
                ->delete();
        }
        
        // Now add the unique constraint
        Schema::table('transactions', function (Blueprint $table) {
            // First make sure transaction_id is not null or empty
            DB::statement("UPDATE transactions SET transaction_id = CONCAT('legacy_', id) WHERE transaction_id IS NULL OR transaction_id = ''");
            
            // Now add the unique constraint
            $table->unique('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['transaction_id']);
        });
    }
};
