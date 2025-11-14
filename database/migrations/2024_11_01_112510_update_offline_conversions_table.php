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
        Schema::table('offline_conversions', function (Blueprint $table) {
            $table->string('page')->nullable()->index()->after('gclid');
            $table->string('event_type', 50)->default('visit')->after('page');
            $table->string('visitor_ip', 45)->nullable()->after('event_type');
            $table->text('user_agent')->nullable()->after('visitor_ip');
            $table->json('form_data')->nullable()->after('user_agent');
            $table->string('source', 50)->nullable()->after('form_data');
            $table->string('medium', 50)->nullable()->after('source');
            $table->string('campaign', 100)->nullable()->after('medium');
            $table->index(['gclid', 'event_type'], 'idx_gclid_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_conversions', function (Blueprint $table) {
            $table->dropColumn(['page', 'event_type', 'visitor_ip', 'user_agent', 'form_data', 'source', 'medium', 'campaign']);
        });
    }
};
