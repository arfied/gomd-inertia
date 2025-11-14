<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pain_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users');
            $table->string('pain_type'); // Possible values: Acute pain, Chronic pain, Neuropathic pain, Inflammatory pain, Visceral pain, Functional pain, Substance-related pain, Fatigue-related pain, Other
            $table->string('pain_type_other')->nullable();
            $table->string('pain_location'); // Possible values: Head, Neck, Shoulder, Upper back, Middle back, Lower back, Spinal cord, Chest, Abdomen, Hip, Thigh, Knee, Calf, Ankle, Foot, Arm, Elbow, Wrist, Hand, Other
            $table->string('pain_location_other')->nullable();
            $table->integer('pain_intensity'); // 1-10
            $table->string('pain_duration'); // Possible values: Less than 1 month, 1-3 months, 3-6 months, 6-12 months, Over 1 year
            $table->date('pain_start');
            $table->string('pain_frequency'); // Possible values: Constantly, Several times a day, Once a day, Several times a week, Once a week, Occasionally
            $table->string('pain_triggers'); // Possible values: Physical activity, Stress, Poor posture, Certain movements or positions, Weather changes, Other
            $table->string('pain_triggers_other')->nullable();
            $table->string('pain_relief'); // Possible values: Rest, Heat, Cold, Over-the-counter medications, Prescription medications, Physical therapy, Other
            $table->string('pain_relief_other')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pain_assessments');
    }
};
