<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreateMergeCandidate;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Actions\CreatePersonIdentity;
use Liberu\Genealogy\People\Actions\CreatePersonLifeEvent;
use Liberu\Genealogy\People\Actions\CreatePersonName;
use Liberu\Genealogy\People\Actions\DeletePerson;
use Liberu\Genealogy\People\Actions\ReviewMergeCandidate;
use Liberu\Genealogy\People\Actions\UpdatePerson;
use Liberu\Genealogy\People\Events\MergeCandidateReviewed;
use Liberu\Genealogy\People\Events\PersonDeleted;
use Liberu\Genealogy\People\Events\PersonUpdated;
use Liberu\Genealogy\People\Models\MergeCandidate;
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

    (new ReviewMergeCandidate())->execute($merge, 'accepted', 'Same family record.');

    expect($merge->fresh()->status)->toBe('accepted')
        ->and($merge->fresh()->reviewed_at)->not->toBeNull();
    Event::assertDispatched(MergeCandidateReviewed::class);
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

    app(TeamContext::class)->set($team->id);
    expect(PersonName::query()->where('person_id', $person->id)->count())->toBe(1)
        ->and(PersonIdentity::query()->where('person_id', $person->id)->count())->toBe(1)
        ->and(PersonLifeEvent::query()->where('person_id', $person->id)->count())->toBe(1);
});
