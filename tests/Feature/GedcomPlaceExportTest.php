<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\PersonEvent;
use App\Models\Place;
use App\Models\PlaceName;
use App\Models\User;
use App\Services\GedcomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GedcomPlaceExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($this->user);
    }

    public function test_it_exports_hierarchical_place_names_for_person_events(): void
    {
        // 1. Arrange
        // Create a hierarchical place structure
        $country = Place::factory()->create();
        PlaceName::factory()->create(['place_id' => $country->id, 'name' => 'USA', 'is_default' => true]);

        $state = Place::factory()->create(['parent_id' => $country->id]);
        PlaceName::factory()->create(['place_id' => $state->id, 'name' => 'California', 'is_default' => true]);

        $city = Place::factory()->create(['parent_id' => $state->id]);
        PlaceName::factory()->create(['place_id' => $city->id, 'name' => 'Los Angeles', 'is_default' => true]);
// Create a person with an event linked to the most specific place
$person = Person::factory()->create(['team_id' => $this->user->current_team_id]);
PersonEvent::factory()->create([
    'person_id' => $person->id,
    'title' => 'BIRT',
    'places_id' => $city->id,
    'date' => '1 JAN 1990',
    'team_id' => $this->user->current_team_id,
]);

$people = Person::with('events.place.defaultName', 'events.place.parent')->get();
$families = collect();

        // 2. Act
        $gedcomService = new GedcomService();
        $gedcomContent = $gedcomService->generateGedcomContent($people, $families);

        // 3. Assert
        $expectedGedcomLines = [
            '1 BIRT',
            '2 DATE 1 JAN 1990',
            '2 PLAC Los Angeles, California, USA',
        ];
        $expectedGedcomString = implode("\n", $expectedGedcomLines);

        $this->assertStringContainsString($expectedGedcomString, $gedcomContent);
    }
}
