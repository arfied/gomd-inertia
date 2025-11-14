<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->boolean('is_non_standard')->default(false)->after('pharmacist_id');
        });

        Schema::create('non_standard_prescription_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->onDelete('cascade');
            $table->text('compounding_instructions')->nullable();
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->string('custom_medication_name')->nullable()->after('quantity');
            $table->text('custom_medication_details')->nullable()->after('custom_medication_name');
        });
    }

    public function down()
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('is_non_standard');
        });

        Schema::dropIfExists('non_standard_prescription_details');

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropColumn('custom_medication_name');
            $table->dropColumn('custom_medication_details');
        });
    }
};
