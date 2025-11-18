<?php

namespace Tests\Unit;

use App\Application\MedicationCatalog\MedicationSearchFinder;
use App\Models\Medication;
use App\Models\MedicationSearchIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicationSearchFinderTest extends TestCase
{
    use RefreshDatabase;
    private MedicationSearchFinder $finder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->finder = app(MedicationSearchFinder::class);
    }

    public function test_search_returns_paginated_results(): void
    {
        // Create test medications
        $medication1 = Medication::factory()->create([
            'name' => 'Aspirin',
            'generic_name' => 'acetylsalicylic acid',
            'status' => 'active',
        ]);

        $medication2 = Medication::factory()->create([
            'name' => 'Ibuprofen',
            'generic_name' => 'ibuprofen',
            'status' => 'active',
        ]);

        // Create search index entries
        MedicationSearchIndex::factory()->create([
            'medication_id' => $medication1->id,
            'name' => 'Aspirin',
            'generic_name' => 'acetylsalicylic acid',
            'status' => 'active',
        ]);

        MedicationSearchIndex::factory()->create([
            'medication_id' => $medication2->id,
            'name' => 'Ibuprofen',
            'generic_name' => 'ibuprofen',
            'status' => 'active',
        ]);

        $results = $this->finder->search(status: 'active', perPage: 15);

        $this->assertCount(2, $results->items());
    }

    public function test_search_filters_by_drug_class(): void
    {
        $medication = MedicationSearchIndex::factory()->create([
            'drug_class' => 'NSAID',
            'status' => 'active',
        ]);

        $results = $this->finder->search(drugClass: 'NSAID', status: 'active');

        $this->assertCount(1, $results->items());
        $this->assertEquals('NSAID', $results->items()[0]->drug_class);
    }

    public function test_search_filters_by_prescription_requirement(): void
    {
        MedicationSearchIndex::factory()->create([
            'requires_prescription' => true,
            'status' => 'active',
        ]);

        MedicationSearchIndex::factory()->create([
            'requires_prescription' => false,
            'status' => 'active',
        ]);

        $results = $this->finder->search(requiresPrescription: true, status: 'active');

        $this->assertCount(1, $results->items());
        $this->assertTrue($results->items()[0]->requires_prescription);
    }

    public function test_search_filters_by_controlled_substance(): void
    {
        MedicationSearchIndex::factory()->create([
            'controlled_substance' => true,
            'status' => 'active',
        ]);

        MedicationSearchIndex::factory()->create([
            'controlled_substance' => false,
            'status' => 'active',
        ]);

        $results = $this->finder->search(controlledSubstance: true, status: 'active');

        $this->assertCount(1, $results->items());
        $this->assertTrue($results->items()[0]->controlled_substance);
    }

    public function test_count_returns_correct_total(): void
    {
        MedicationSearchIndex::factory()->count(5)->create(['status' => 'active']);

        $count = $this->finder->count(status: 'active');

        $this->assertEquals(5, $count);
    }

    public function test_search_respects_status_filter(): void
    {
        MedicationSearchIndex::factory()->create(['status' => 'active']);
        MedicationSearchIndex::factory()->create(['status' => 'inactive']);

        $results = $this->finder->search(status: 'active');

        $this->assertCount(1, $results->items());
    }
}

