<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreateMergeCandidate;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Actions\CreatePersonAssociation;
use Liberu\Genealogy\People\Actions\CreatePersonIdentity;
use Liberu\Genealogy\People\Actions\CreatePersonLifeEvent;
use Liberu\Genealogy\People\Actions\CreatePersonName;
use Liberu\Genealogy\People\Actions\DeletePerson;
use Liberu\Genealogy\People\Actions\DeletePersonAssociation;
use Liberu\Genealogy\People\Actions\MergePersons;
use Liberu\Genealogy\People\Actions\RemovePersonAttribute;
use Liberu\Genealogy\People\Actions\ReviewMergeCandidate;
use Liberu\Genealogy\People\Actions\SetPersonLifeStatus;
use Liberu\Genealogy\People\Actions\UpdatePerson;
use Liberu\Genealogy\People\Actions\UpdatePersonAssociation;
use Liberu\Genealogy\People\Actions\UpdatePersonAttributes;
use Liberu\Genealogy\People\Events\MergeCandidateReviewed;
use Liberu\Genealogy\People\Events\PersonAttributesUpdated;
use Liberu\Genealogy\People\Events\PersonDeleted;
use Liberu\Genealogy\People\Events\PersonMerged;
use Liberu\Genealogy\People\Events\PersonUpdated;
use Liberu\Genealogy\People\Models\MergeCandidate;
use Liberu\Genealogy\People\Models\PersonAssociation;
use Liberu\Genealogy\People\Models\PersonIdentity;
use Liberu\Genealogy\People\Models\PersonLifeEvent;
use Liberu\Genealogy\People\Models\PersonName;

uses(RefreshDatabase::class);

it('owns names, identities, life events, and merge candidates inside the active team', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $person = (new CreatePerson())->execute(['given_name' => 'Ada', 'family_name' => 'Lovelace']);
    (new CreatePersonName())->execute(['person_id' => $person->id, 'type' => 'married', 'family_name' => 'King']);
    (new CreatePersonIdentity())->execute(['person_id' => $person->id, 'type' => 'archive', 'value' => 'A-1']);
    (new CreatePersonLifeEvent())->execute(['person_id' => $person->id, 'type' => 'birth', 'date' => '1815-12-10']);
    $other = (new CreatePerson())->execute(['given_name' => 'Charles']);
    (new CreateMergeCandidate())->execute(['person_id' => $person->id, 'candidate_person_id' => $other->id, 'score' => 0.91]);

    expect($person->names)->toHaveCount(1)
        ->and($person->identities)->toHaveCount(1)
        ->and($person->lifeEvents)->toHaveCount(1)
        ->and(MergeCandidate::query()->where('person_id', $person->id)->exists())->toBeTrue();
});

it('does not allow a person to be created without a team context', function (): void {
    expect(fn () => (new CreatePerson())->execute(['given_name' => 'Guest']))
        ->toThrow(LogicException::class);
});

it('rejects supporting records that reference another team', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    app(TeamContext::class)->set($team->id);
    $local = (new CreatePerson())->execute(['given_name' => 'Local']);

    $remoteOwner = User::factory()->create();
    $remoteTeam = Team::factory()->create(['user_id' => $remoteOwner->id]);
    app(TeamContext::class)->set($remoteTeam->id);
    $remote = (new CreatePerson())->execute(['given_name' => 'Remote']);
    app(TeamContext::class)->set($team->id);

    expect(fn () => (new CreatePersonName())->execute(['person_id' => $remote->id, 'family_name' => 'Leak']))
        ->toThrow(InvalidArgumentException::class, 'active team')
        ->and(fn () => (new CreatePersonIdentity())->execute(['person_id' => $remote->id, 'type' => 'archive', 'value' => 'R-1']))
        ->toThrow(InvalidArgumentException::class, 'active team')
        ->and(fn () => (new CreatePersonLifeEvent())->execute(['person_id' => $remote->id, 'type' => 'birth']))
        ->toThrow(InvalidArgumentException::class, 'active team')
        ->and(fn () => (new CreateMergeCandidate())->execute(['person_id' => $local->id, 'candidate_person_id' => $remote->id]))
        ->toThrow(InvalidArgumentException::class, 'active team');
});

it('reviews merge candidates through an explicit lifecycle action', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'One']);
    $candidate = (new CreatePerson())->execute(['given_name' => 'Two']);
    $merge = (new CreateMergeCandidate())->execute([
        'person_id' => $person->id,
        'candidate_person_id' => $candidate->id,
        'score' => 0.8,
    ]);
    (new CreatePersonName())->execute(['person_id' => $candidate->id, 'given_name' => 'Two', 'family_name' => 'Alternate']);
    (new CreatePersonIdentity())->execute(['person_id' => $candidate->id, 'type' => 'archive', 'value' => 'T-2']);

    (new ReviewMergeCandidate())->execute($merge, 'accepted', 'Same family record.');

    expect($merge->fresh()->status)->toBe('accepted')
        ->and($merge->fresh()->reviewed_at)->not->toBeNull()
        ->and($candidate->fresh()->trashed())->toBeTrue()
        ->and($person->names()->where('family_name', 'Alternate')->exists())->toBeTrue()
        ->and($person->identities()->where('value', 'T-2')->exists())->toBeTrue();
    Event::assertDispatched(MergeCandidateReviewed::class);
    Event::assertDispatched(PersonMerged::class, fn (PersonMerged $event): bool => $event->primary->is($person) && $event->duplicateId === (string) $candidate->id);
});

it('repoints associations in both directions when people are merged', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $primary = (new CreatePerson())->execute(['given_name' => 'Primary']);
    $duplicate = (new CreatePerson())->execute(['given_name' => 'Duplicate']);
    $other = (new CreatePerson())->execute(['given_name' => 'Other']);
    (new CreatePersonAssociation())->execute(['person_id' => $duplicate->id, 'associated_person_id' => $other->id, 'relationship' => 'witness']);
    (new CreatePersonAssociation())->execute(['person_id' => $other->id, 'associated_person_id' => $duplicate->id, 'relationship' => 'relative']);

    (new MergePersons())->execute($primary, $duplicate);

    expect($primary->fresh()->associations()->where('associated_person_id', $other->id)->exists())->toBeTrue()
        ->and($other->fresh()->associatedWith()->where('person_id', $primary->id)->exists())->toBeTrue()
        ->and($duplicate->fresh()->trashed())->toBeTrue();
});

it('publishes person update and deletion events after transactional mutations', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'Before']);

    (new UpdatePerson())->execute($person, ['given_name' => 'After']);
    Event::assertDispatched(PersonUpdated::class);

    (new DeletePerson())->execute($person);
    Event::assertDispatched(PersonDeleted::class);
    expect($person->fresh()->trashed())->toBeTrue();
});

it('manages person attributes through an explicit tenant-safe lifecycle', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'Ada', 'attributes' => ['occupation' => 'mathematician']]);

    (new UpdatePersonAttributes())->execute($person, ['occupation' => 'writer', 'language' => 'English']);
    expect($person->fresh()->attributes)->toBe(['occupation' => 'writer', 'language' => 'English']);

    (new UpdatePersonAttributes())->execute($person, ['language' => 'French'], true);
    expect($person->fresh()->attributes)->toBe(['language' => 'French']);

    (new RemovePersonAttribute())->execute($person, 'language');
    expect($person->fresh()->attributes)->toBe([]);
    Event::assertDispatched(PersonAttributesUpdated::class, 3);
});

it('transitions living and deceased status through a tenant-safe action', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'Ada', 'birth_date' => '1815-12-10']);

    (new SetPersonLifeStatus())->execute($person, 'deceased', '1852-11-27');
    expect($person->fresh()->isDeceased())->toBeTrue();
    (new SetPersonLifeStatus())->execute($person, 'living');
    expect($person->fresh()->isLiving())->toBeTrue();
    expect(fn () => (new SetPersonLifeStatus())->execute($person, 'deceased', '1800-01-01'))
        ->toThrow(InvalidArgumentException::class, 'precede');
    Event::assertDispatched(PersonUpdated::class, 2);
});

it('preserves resolved and unresolved person associations within the active team', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'Subject']);
    $associated = (new CreatePerson())->execute(['given_name' => 'Associated']);

    $resolved = (new CreatePersonAssociation())->execute([
        'person_id' => $person->id,
        'associated_person_id' => $associated->id,
        'relationship' => 'witness',
    ]);
    $unresolved = (new CreatePersonAssociation())->execute([
        'person_id' => $person->id,
        'associated_external_id' => '@I99@',
        'relationship' => 'informant',
    ]);

    expect($resolved->isResolved())->toBeTrue()
        ->and($unresolved->isResolved())->toBeFalse()
        ->and($person->fresh()->associations)->toHaveCount(2)
        ->and($associated->fresh()->associatedWith)->toHaveCount(1);

    (new UpdatePersonAssociation())->execute($unresolved, ['associated_person_id' => $associated->id]);
    expect($unresolved->fresh()->associated_external_id)->toBeNull()
        ->and($unresolved->fresh()->isResolved())->toBeTrue();

    (new DeletePersonAssociation())->execute($resolved);
    expect(PersonAssociation::withTrashed()->find($resolved->id)?->trashed())->toBeTrue();
});

it('exposes merge-candidate review through the authenticated API', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'API one']);
    $candidate = (new CreatePerson())->execute(['given_name' => 'API two']);
    $merge = (new CreateMergeCandidate())->execute([
        'person_id' => $person->id,
        'candidate_person_id' => $candidate->id,
    ]);
    app(TeamContext::class)->clear();

    $this->actingAs($user)
        ->postJson('/api/v1/genealogy/people/'.$person->getKey().'/merge-candidates/'.$merge->getKey().'/review', [
            'status' => 'rejected',
            'reason' => 'Insufficient evidence.',
        ])
        ->assertOk()
        ->assertJsonPath('data.type', 'genealogy-merge-candidate')
        ->assertJsonPath('data.attributes.status', 'rejected');
});

it('exposes every people supporting capability through tenant-scoped API resources', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'Ada']);
    $candidate = (new CreatePerson())->execute(['given_name' => 'Ada alternate']);

    $this->actingAs($user)->postJson("/api/v1/genealogy/people/{$person->id}/names", [
        'given_name' => 'Augusta', 'family_name' => 'King',
    ])->assertCreated()->assertJsonPath('data.type', 'genealogy-person-names');
    $this->actingAs($user)->postJson("/api/v1/genealogy/people/{$person->id}/identities", [
        'type' => 'archive', 'value' => 'A-42',
    ])->assertCreated()->assertJsonPath('data.type', 'genealogy-person-identities');
    $this->actingAs($user)->postJson("/api/v1/genealogy/people/{$person->id}/life-events", [
        'type' => 'birth', 'date' => '1815-12-10',
    ])->assertCreated()->assertJsonPath('data.type', 'genealogy-person-life-events');
    $this->actingAs($user)->postJson("/api/v1/genealogy/people/{$person->id}/merge-candidates", [
        'candidate_person_id' => $candidate->id,
    ])->assertCreated()->assertJsonPath('data.type', 'genealogy-person-merge-candidates');
    $this->actingAs($user)->patchJson("/api/v1/genealogy/people/{$person->id}/attributes", [
        'attributes' => ['occupation' => 'mathematician'],
    ])->assertOk()->assertJsonPath('data.attributes.attributes.occupation', 'mathematician');
    $this->actingAs($user)->deleteJson("/api/v1/genealogy/people/{$person->id}/attributes/occupation")
        ->assertOk()->assertJsonPath('data.attributes.attributes', []);
    $this->actingAs($user)->patchJson("/api/v1/genealogy/people/{$person->id}/life-status", [
        'status' => 'deceased', 'death_date' => '1900-01-01',
    ])->assertOk()->assertJsonPath('data.attributes.life_status', 'deceased');

    $association = $this->actingAs($user)->postJson("/api/v1/genealogy/people/{$person->id}/associations", [
        'associated_person_id' => $candidate->id,
        'relationship' => 'witness',
    ])->assertCreated()->assertJsonPath('data.type', 'genealogy-person-association');
    $associationId = $association->json('data.id');
    $this->actingAs($user)->patchJson("/api/v1/genealogy/people/{$person->id}/associations/{$associationId}", [
        'relationship' => 'informant',
    ])->assertOk()->assertJsonPath('data.attributes.relationship', 'informant');
    $this->actingAs($user)->deleteJson("/api/v1/genealogy/people/{$person->id}/associations/{$associationId}")
        ->assertNoContent();

    app(TeamContext::class)->set($team->id);
    expect(PersonName::query()->where('person_id', $person->id)->count())->toBe(1)
        ->and(PersonIdentity::query()->where('person_id', $person->id)->count())->toBe(1)
        ->and(PersonLifeEvent::query()->where('person_id', $person->id)->count())->toBe(1);
});
