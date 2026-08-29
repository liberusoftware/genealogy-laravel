<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Actions\MergePersons;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\Relationships\Actions\UpdateRelationship;
use Liberu\Genealogy\Relationships\Events\RelationshipCreated;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Liberu\Genealogy\Relationships\Queries\GraphValidator;
use Liberu\Genealogy\Relationships\Queries\RelationshipCalculator;
use Livewire\Livewire;

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

it('does not inspect another teams parent graph while validating an edge', function (): void {
    $team = Team::factory()->create();
    app(TeamContext::class)->set($team->id);
    $parent = (new CreatePerson())->execute(['given_name' => 'Parent']);
    $child = (new CreatePerson())->execute(['given_name' => 'Child']);

    $otherTeam = Team::factory()->create();
    $foreignRelationship = new Relationship([
        'person_id' => $child->id,
        'related_person_id' => $parent->id,
        'type' => 'parent',
    ]);
    $foreignRelationship->forceFill(['team_id' => $otherTeam->id])->saveQuietly();

    expect((new GraphValidator())->validate($parent->id, $child->id, 'parent')['valid'])->toBeTrue();
});

it('rejects direct relationship updates outside the active team', function (): void {
    $firstTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($firstTeam->id);
    $left = (new CreatePerson())->execute(['given_name' => 'Left']);
    $right = (new CreatePerson())->execute(['given_name' => 'Right']);
    $relationship = (new CreateRelationship())->execute([
        'person_id' => $left->id,
        'related_person_id' => $right->id,
        'type' => 'household',
    ]);

    $secondTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($secondTeam->id);

    expect(fn () => app(UpdateRelationship::class)->execute($relationship->withoutRelations(), ['confidence' => 50]))
        ->toThrow(InvalidArgumentException::class, 'active team');
});

it('reconciles relationship endpoints when a person is merged', function (): void {
    $team = Team::factory()->create();
    app(TeamContext::class)->set($team->id);
    $primary = (new CreatePerson())->execute(['given_name' => 'Primary']);
    $duplicate = (new CreatePerson())->execute(['given_name' => 'Duplicate']);
    $related = (new CreatePerson())->execute(['given_name' => 'Related']);
    (new CreateRelationship())->execute([
        'person_id' => $duplicate->id,
        'related_person_id' => $related->id,
        'type' => 'parent',
    ]);

    app(MergePersons::class)->execute($primary, $duplicate);

    expect(Relationship::query()->where('person_id', $primary->id)->where('related_person_id', $related->id)->exists())->toBeTrue()
        ->and(Relationship::query()->where('person_id', $duplicate->id)->exists())->toBeFalse();
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

it('calculates direct, sibling, cousin, and unrelated relationships through the modular query', function (): void {
    $team = Team::factory()->create();
    app(TeamContext::class)->set($team->id);
    $grandparent = (new CreatePerson())->execute(['given_name' => 'Grandparent']);
    $parentA = (new CreatePerson())->execute(['given_name' => 'Parent A']);
    $parentB = (new CreatePerson())->execute(['given_name' => 'Parent B']);
    $childA = (new CreatePerson())->execute(['given_name' => 'Child A']);
    $childB = (new CreatePerson())->execute(['given_name' => 'Child B']);
    $unrelated = (new CreatePerson())->execute(['given_name' => 'Unrelated']);
    $create = new CreateRelationship();

    $create->execute(['person_id' => $grandparent->id, 'related_person_id' => $parentA->id, 'type' => 'parent']);
    $create->execute(['person_id' => $grandparent->id, 'related_person_id' => $parentB->id, 'type' => 'parent']);
    $create->execute(['person_id' => $parentA->id, 'related_person_id' => $childA->id, 'type' => 'parent']);
    $create->execute(['person_id' => $parentB->id, 'related_person_id' => $childB->id, 'type' => 'parent']);

    $calculator = new RelationshipCalculator();

    expect($calculator->between($grandparent->id, $childA->id)['relationship'])->toBe('grandparent')
        ->and($calculator->between($childA->id, $childB->id)['relationship'])->toBe('1st cousin')
        ->and($calculator->between($parentA->id, $parentB->id)['relationship'])->toBe('sibling')
        ->and($calculator->between($childA->id, $unrelated->id)['relationship'])->toBe('no traceable relationship');
});

it('exposes the relationship calculator through the API and Livewire adapter', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $parent = (new CreatePerson())->execute(['given_name' => 'Parent']);
    $child = (new CreatePerson())->execute(['given_name' => 'Child']);
    (new CreateRelationship())->execute(['person_id' => $parent->id, 'related_person_id' => $child->id, 'type' => 'parent']);

    $this->actingAs($user)->postJson('/api/v1/genealogy/relationships/calculate', [
        'first_person_id' => $parent->id,
        'second_person_id' => $child->id,
    ])->assertOk()->assertJsonPath('data.relationship', 'parent');

    app(TeamContext::class)->set($team->id);

    Livewire::test(Liberu\Genealogy\Relationships\Livewire\RelationshipCalculator::class)
        ->set('firstPersonId', $parent->id)
        ->set('secondPersonId', $child->id)
        ->call('calculate')
        ->assertSet('result.relationship', 'parent');
});
