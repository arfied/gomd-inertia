<?php

namespace Tests\Unit;

use App\Application\MedicationCatalog\EloquentMedicationCatalogProjector;
use App\Domain\MedicationCatalog\Events\FormularyCreated;
use App\Domain\MedicationCatalog\Events\MedicationAddedToFormulary;
use App\Domain\MedicationCatalog\Events\MedicationCreated;
use App\Domain\MedicationCatalog\Events\MedicationUpdated;
use App\Models\Formulary;
use App\Models\FormularyMedication;
use App\Models\Medication;
use App\Models\MedicationSearchIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectMedicationCatalogTest extends TestCase
{
    use RefreshDatabase;
    private EloquentMedicationCatalogProjector $projector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projector = app(EloquentMedicationCatalogProjector::class);
    }

    public function test_project_medication_created(): void
    {
        $medicationUuid = Str::uuid();
        $event = new MedicationCreated(
            aggregateUuid: $medicationUuid,
            payload: [
                'id' => 1,
                'name' => 'Aspirin',
                'generic_name' => 'acetylsalicylic acid',
                'drug_class' => 'NSAID',
                'status' => 'active',
                'requires_prescription' => false,
                'controlled_substance' => false,
            ],
            metadata: [],
        );

        $this->projector->projectMedicationCreated($event);

        $this->assertDatabaseHas('medications', [
            'id' => 1,
            'name' => 'Aspirin',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('medication_search_index', [
            'medication_id' => 1,
            'name' => 'Aspirin',
            'drug_class' => 'NSAID',
        ]);
    }

    public function test_project_medication_updated(): void
    {
        Medication::factory()->create(['name' => 'Old Name']);
        $medication = Medication::first();
        MedicationSearchIndex::factory()->create(['medication_id' => $medication->id, 'name' => 'Old Name']);

        $event = new MedicationUpdated(
            aggregateUuid: Str::uuid(),
            payload: [
                'id' => $medication->id,
                'name' => 'New Name',
                'status' => 'active',
            ],
            metadata: [],
        );

        $this->projector->projectMedicationUpdated($event);

        $this->assertDatabaseHas('medications', [
            'id' => $medication->id,
            'name' => 'New Name',
        ]);

        $this->assertDatabaseHas('medication_search_index', [
            'medication_id' => $medication->id,
            'name' => 'New Name',
        ]);
    }

    public function test_project_formulary_created(): void
    {
        $formularyUuid = Str::uuid();
        $event = new FormularyCreated(
            aggregateUuid: $formularyUuid,
            payload: [
                'name' => 'Blue Cross Formulary',
                'description' => 'Standard formulary',
                'organization_id' => 'org-1',
                'type' => 'insurance',
                'status' => 'active',
            ],
            metadata: [],
        );

        $this->projector->projectFormularyCreated($event);

        $this->assertDatabaseHas('formularies', [
            'uuid' => $formularyUuid,
            'name' => 'Blue Cross Formulary',
            'status' => 'active',
        ]);
    }

    public function test_project_medication_added_to_formulary(): void
    {
        $formularyUuid = Str::uuid();
        $formulary = Formulary::factory()->create(['uuid' => $formularyUuid]);
        $medication = Medication::factory()->create();

        $event = new MedicationAddedToFormulary(
            aggregateUuid: $formularyUuid,
            payload: [
                'medication_uuid' => $medication->id,
                'tier' => 'preferred',
                'requires_pre_authorization' => false,
                'notes' => 'Test note',
            ],
            metadata: [],
        );

        $this->projector->projectMedicationAddedToFormulary($event);

        $this->assertDatabaseHas('formulary_medications', [
            'formulary_id' => $formulary->id,
            'medication_id' => $medication->id,
            'tier' => 'preferred',
        ]);
    }
}

