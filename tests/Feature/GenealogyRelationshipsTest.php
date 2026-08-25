<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\Relationships\Events\RelationshipCreated;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Liberu\Genealogy\Relationships\Queries\GraphValidator;

uses(RefreshDatabase::class);

it('records supported relationship types within the active team and emits an event', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $parent = (new CreatePerson())->execute(['given_name' => 'Parent']);
    $child = (new CreatePerson())->execute(['given_name' => 'Child']);

    $relationship = (new CreateRelationship())->execute([
        'person_id' => $parent->id,
        'related_person_id' => $child->id,
        'type' => 'adoption',
        'confidence' => 80,
    ]);

    expect($relationship->team_id)->toBe((string) $team->id)
        ->and(Relationship::query()->whereKey($relationship)->exists())->toBeTrue();
    Event::assertDispatched(RelationshipCreated::class);
});

it('rejects a relationship whose endpoints are in another team', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'Local']);
    $otherUser = User::factory()->create();
    $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);
    app(TeamContext::class)->set($otherTeam->id);
    $otherPerson = (new CreatePerson())->execute(['given_name' => 'Remote']);
    app(TeamContext::class)->set($team->id);

    expect(fn () => (new CreateRelationship())->execute([
        'person_id' => $person->id,
        'related_person_id' => $otherPerson->id,
        'type' => 'parent',
    ]))->toThrow(InvalidArgumentException::class);
});

it('rejects duplicate and cyclic parent edges while allowing uncertain links', function (): void {
    $team = Team::factory()->create();
    app(TeamContext::class)->set($team->id);
    $grandparent = (new CreatePerson())->execute(['given_name' => 'Grandparent']);
    $parent = (new CreatePerson())->execute(['given_name' => 'Parent']);
    $child = (new CreatePerson())->execute(['given_name' => 'Child']);
    $create = new CreateRelationship();
    $create->execute(['person_id' => $grandparent->id, 'related_person_id' => $parent->id, 'type' => 'parent']);
    $create->execute(['person_id' => $parent->id, 'related_person_id' => $child->id, 'type' => 'parent']);

    $validator = new GraphValidator();
    expect($validator->validate($child->id, $grandparent->id, 'parent')['valid'])->toBeFalse()
        ->and($validator->validate($grandparent->id, $parent->id, 'parent')['valid'])->toBeFalse()
        ->and($validator->validate($grandparent->id, $child->id, 'uncertain')['valid'])->toBeTrue();
});

it('filters relationship edges by person, type, and confidence through the API', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'Person']);
    $related = (new CreatePerson())->execute(['given_name' => 'Related']);
    (new CreateRelationship())->execute(['person_id' => $person->id, 'related_person_id' => $related->id, 'type' => 'uncertain', 'confidence' => 80]);
    (new CreateRelationship())->execute(['person_id' => $person->id, 'related_person_id' => $related->id, 'type' => 'household', 'confidence' => 20]);

    $this->actingAs($user)->getJson('/api/v1/genealogy/relationships?person_id='.$person->id.'&type=uncertain&confidence_min=50')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.attributes.type', 'uncertain');
});
