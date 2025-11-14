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
        Schema::table('medications', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable()->change();
            $table->string('drug_class', 100)->nullable()->after('type');
            $table->string('route_of_administration', 100)->nullable()->after('dosage_form');
            $table->string('half_life', 50)->nullable()->after('strength');
            $table->text('contraindications')->nullable()->after('storage_conditions');
            $table->text('side_effects')->nullable()->after('contraindications');
            $table->text('interactions')->nullable()->after('side_effects');
            $table->string('pregnancy_category', 50)->nullable()->after('interactions');
            $table->boolean('breastfeeding_safe')->nullable()->after('pregnancy_category');
            $table->text('black_box_warning')->nullable()->after('breastfeeding_safe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable(false)->change();
            $table->dropColumn([
                'drug_class',
                'route_of_administration',
                'half_life',
                'contraindications',
                'side_effects',
                'interactions',
                'pregnancy_category',
                'breastfeeding_safe',
                'black_box_warning'
            ]);
        });
    }
};
