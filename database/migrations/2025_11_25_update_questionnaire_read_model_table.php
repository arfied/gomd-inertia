<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite requires special handling for dropping indexed columns
            // We need to recreate the table without the columns
            DB::statement('
                CREATE TABLE questionnaire_read_model_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    questionnaire_uuid VARCHAR(255) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    questions JSON,
                    created_by BIGINT UNSIGNED,
                    status VARCHAR(255) NOT NULL DEFAULT "active",
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');

            DB::statement('
                INSERT INTO questionnaire_read_model_new
                SELECT id, questionnaire_uuid, title, description, questions, created_by, status, created_at, updated_at
                FROM questionnaire_read_model
            ');

            DB::statement('DROP TABLE questionnaire_read_model');
            DB::statement('ALTER TABLE questionnaire_read_model_new RENAME TO questionnaire_read_model');

            // Recreate indexes
            DB::statement('CREATE UNIQUE INDEX questionnaire_read_model_questionnaire_uuid_unique ON questionnaire_read_model(questionnaire_uuid)');
            DB::statement('CREATE INDEX questionnaire_read_model_created_by_index ON questionnaire_read_model(created_by)');
            DB::statement('CREATE INDEX questionnaire_read_model_status_index ON questionnaire_read_model(status)');
            DB::statement('CREATE INDEX questionnaire_read_model_created_at_index ON questionnaire_read_model(created_at)');
        } else {
            // MySQL/PostgreSQL approach
            Schema::table('questionnaire_read_model', function (Blueprint $table) {
                if (Schema::hasColumn('questionnaire_read_model', 'responses')) {
                    $table->dropColumn('responses');
                }
                if (Schema::hasColumn('questionnaire_read_model', 'patient_id')) {
                    $table->dropColumn('patient_id');
                }
                if (Schema::hasColumn('questionnaire_read_model', 'submitted_at')) {
                    $table->dropColumn('submitted_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('
                CREATE TABLE questionnaire_read_model_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    questionnaire_uuid VARCHAR(255) NOT NULL UNIQUE,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    questions JSON,
                    responses JSON,
                    created_by BIGINT UNSIGNED,
                    patient_id VARCHAR(255),
                    status VARCHAR(255) NOT NULL DEFAULT "active",
                    submitted_at TIMESTAMP,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');

            DB::statement('
                INSERT INTO questionnaire_read_model_new
                SELECT id, questionnaire_uuid, title, description, questions, NULL, created_by, NULL, status, NULL, created_at, updated_at
                FROM questionnaire_read_model
            ');

            DB::statement('DROP TABLE questionnaire_read_model');
            DB::statement('ALTER TABLE questionnaire_read_model_new RENAME TO questionnaire_read_model');

            // Recreate indexes
            DB::statement('CREATE INDEX questionnaire_read_model_patient_id_index ON questionnaire_read_model(patient_id)');
            DB::statement('CREATE INDEX questionnaire_read_model_created_by_index ON questionnaire_read_model(created_by)');
            DB::statement('CREATE INDEX questionnaire_read_model_status_index ON questionnaire_read_model(status)');
            DB::statement('CREATE INDEX questionnaire_read_model_created_at_index ON questionnaire_read_model(created_at)');
        } else {
            Schema::table('questionnaire_read_model', function (Blueprint $table) {
                $table->json('responses')->nullable();
                $table->string('patient_id')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->index('patient_id');
            });
        }
    }
};

