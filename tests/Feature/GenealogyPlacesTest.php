<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Places\Actions\CreatePlace;
use Liberu\Genealogy\Places\Actions\CreatePlaceName;
use Liberu\Genealogy\Places\Actions\DeletePlaceName;
use Liberu\Genealogy\Places\Actions\UpdatePlace;
use Liberu\Genealogy\Places\Actions\UpdatePlaceName;
use Liberu\Genealogy\Places\Events\PlaceCreated;
use Liberu\Genealogy\Places\Queries\PlaceHierarchy;

uses(RefreshDatabase::class);

it('records tenant-owned places with hierarchy and coordinates', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $country = (new CreatePlace())->execute([
        'name' => 'United Kingdom',
        'status' => 'active',
        'historical_names' => ['Kingdom of Great Britain'],
    ]);
    $city = (new CreatePlace())->execute([
        'name' => ' London ',
        'parent_id' => $country->id,
        'latitude' => 51.5074,
        'longitude' => -0.1278,
        'jurisdiction' => 'Greater London',
        'status' => 'active',
    ]);
    $historicalName = (new CreatePlaceName())->execute([
        'place_id' => $city->id,
        'name' => '  Londinium  ',
        'type' => 'historical',
        'valid_to' => '00410-01-01',
    ]);

    expect($city->team_id)->toBe((string) $team->id)
        ->and($city->name)->toBe('London')
        ->and($city->parent_id)->toBe($country->id)
        ->and($city->parent->is($country))->toBeTrue()
        ->and($historicalName->place->is($city))->toBeTrue()
        ->and($historicalName->name)->toBe('Londinium')
        ->and($city->hasCoordinates())->toBeTrue()
        ->and($city->mapUrl())->toContain('openstreetmap.org');
    Event::assertDispatched(PlaceCreated::class);
});

it('allows an existing place parent to be cleared', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $parent = (new CreatePlace())->execute(['name' => 'Parent']);
    $child = (new CreatePlace())->execute(['name' => 'Child', 'parent_id' => $parent->getKey()]);

    $updated = (new UpdatePlace())->execute($child, ['parent_id' => null]);

    expect($updated->parent_id)->toBeNull();
});

it('rejects invalid place state and hierarchy cycles', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    expect(fn () => (new CreatePlace())->execute(['name' => 'Nowhere', 'latitude' => 91]))
        ->toThrow(InvalidArgumentException::class, 'Latitude');

    $place = (new CreatePlace())->execute(['name' => 'Place']);
    expect(fn () => (new UpdatePlace())->execute($place, ['parent_id' => $place->id]))
        ->toThrow(InvalidArgumentException::class, 'cycle');
});

it('uses the stable place resource type in the API', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $this->actingAs($user)
        ->postJson('/api/v1/genealogy/places', ['name' => 'York', 'status' => 'active'])
        ->assertCreated()
        ->assertJsonPath('data.type', 'genealogy-place');
});

it('exposes the names-over-time lifecycle through tenant-scoped API actions', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $place = (new CreatePlace())->execute(['name' => 'London']);

    $response = $this->actingAs($user)->postJson("/api/v1/genealogy/places/{$place->id}/names", [
        'name' => 'Londinium', 'type' => 'historical', 'valid_to' => '0410-01-01',
    ]);
    $response->assertCreated()->assertJsonPath('data.type', 'genealogy-place-name');
    $nameId = $response->json('data.id');

    $this->actingAs($user)->patchJson("/api/v1/genealogy/places/{$place->id}/names/{$nameId}", [
        'name' => 'Londinium (Roman)',
    ])->assertOk()->assertJsonPath('data.attributes.name', 'Londinium (Roman)');

    $this->actingAs($user)->getJson("/api/v1/genealogy/places/{$place->id}/names")
        ->assertOk()->assertJsonCount(1, 'data');
    $this->actingAs($user)->deleteJson("/api/v1/genealogy/places/{$place->id}/names/{$nameId}")
        ->assertNoContent();
});

it('keeps place names behind domain lifecycle actions', function (): void {
    $team = Team::factory()->create();
    app(TeamContext::class)->set($team->id);
    $place = (new CreatePlace())->execute(['name' => 'York']);
    $name = (new CreatePlaceName())->execute(['place_id' => $place->id, 'name' => 'Eboracum']);

    (new UpdatePlaceName())->execute($name, ['name' => '  Eboracum Nova  ', 'valid_from' => '0071-01-01', 'valid_to' => '0400-01-01']);
    expect($name->refresh()->valid_from->toDateString())->toBe('0071-01-01')
        ->and($name->name)->toBe('Eboracum Nova');
    (new DeletePlaceName())->execute($name);
    expect($name->refresh()->trashed())->toBeTrue();
});

it('projects a nested place hierarchy through the API', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $country = (new CreatePlace())->execute(['name' => 'Country']);
    $city = (new CreatePlace())->execute(['name' => 'City', 'parent_id' => $country->id]);
    (new CreatePlaceName())->execute(['place_id' => $city->id, 'name' => 'Old City', 'type' => 'historical']);

    expect((new PlaceHierarchy())->execute()[0]['children'][0]['names'][0]['name'])->toBe('Old City');
    $this->actingAs($user)->getJson('/api/v1/genealogy/places/hierarchy')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Country')
        ->assertJsonPath('data.0.children.0.name', 'City');
});

it('retains disconnected place components in hierarchy projections', function (): void {
    $team = Team::factory()->create();
    app(TeamContext::class)->set($team->id);
    $first = (new CreatePlace())->execute(['name' => 'First']);
    $second = (new CreatePlace())->execute(['name' => 'Second']);

    // Simulate a legacy/imported cycle that cannot be reached from a root.
    $first->forceFill(['parent_id' => $second->id])->saveQuietly();
    $second->forceFill(['parent_id' => $first->id])->saveQuietly();

    $hierarchy = (new PlaceHierarchy())->execute(flat: true);

    expect(collect($hierarchy)->pluck('id'))
        ->toContain((string) $first->id)
        ->toContain((string) $second->id);
});
