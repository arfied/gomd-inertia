<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Drop the existing unique index
        Schema::table('medication_variants', function (Blueprint $table) {
            $table->dropUnique('medication_variants_brand_name_dosage_form_strength_unique');
        });

        // Add the new unique index with medication_base_id
        Schema::table('medication_variants', function (Blueprint $table) {
            $table->unique(['medication_base_id', 'brand_name', 'dosage_form', 'strength'], 'medication_variants_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the new unique index in case of rollback
        Schema::table('medication_variants', function (Blueprint $table) {
            $table->dropUnique('medication_variants_unique');
        });

        // Recreate the old unique index
        Schema::table('medication_variants', function (Blueprint $table) {
            $table->unique(['brand_name', 'dosage_form', 'strength'], 'medication_variants_brand_name_dosage_form_strength_unique');
        });
    }
};
