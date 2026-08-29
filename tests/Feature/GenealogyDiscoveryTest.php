<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\Discovery\Actions\CreateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\DeleteDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\ScanDuplicateCandidates;
use Liberu\Genealogy\Discovery\Actions\UpdateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchDeleted;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchReviewed;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchUpdated;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\Discovery\Queries\DiscoverySearch;
use Liberu\Genealogy\Discovery\Queries\RelationshipPath;
use Liberu\Genealogy\Evidence\Actions\CreateCitation;
use Liberu\Genealogy\Evidence\Actions\CreateEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\CreateSource;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('reviews discovery matches through a tenant-safe domain transition and API', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);
    $match = app(CreateDiscoveryMatch::class)->execute(['name' => 'Parish register hint', 'kind' => 'hint', 'status' => 'draft', 'confidence' => 82]);

    $this->actingAs($user)->postJson('/api/v1/genealogy/discovery/'.$match->getKey().'/review', ['status' => 'completed'])
        ->assertOk()->assertJsonPath('data.attributes.status', 'completed');
    $this->actingAs($user)->postJson('/api/v1/genealogy/discovery/'.$match->getKey().'/review', ['status' => 'invalid'])
        ->assertUnprocessable()->assertJsonValidationErrors(['status']);

    Event::assertDispatched(DiscoveryMatchReviewed::class);
});

it('reviews discovery matches through the tenant-safe Livewire control', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);
    $match = app(CreateDiscoveryMatch::class)->execute(['name' => 'Possible duplicate', 'kind' => 'duplicate']);

    Livewire::actingAs($user)->test('genealogy-discovery-list')->call('review', $match->getKey(), 'dismissed');

    expect(DiscoveryMatch::query()->findOrFail($match->getKey())->status)->toBe('dismissed');
});

it('validates discovery status filters at the Livewire boundary', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    Livewire::actingAs($user)
        ->test('genealogy-discovery-list')
        ->set('status', 'unsupported')
        ->assertHasErrors(['status']);
});

it('keeps discovery updates and deletion behind domain lifecycle actions', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $match = app(CreateDiscoveryMatch::class)->execute(['name' => 'Initial hint', 'kind' => 'hint']);

    $updated = app(UpdateDiscoveryMatch::class)->execute($match, ['name' => 'Updated hint', 'confidence' => 91]);
    app(DeleteDiscoveryMatch::class)->execute($updated);

    expect(DiscoveryMatch::query()->withTrashed()->find($match->getKey())->name)->toBe('Updated hint')
        ->and(DiscoveryMatch::query()->find($match->getKey()))->toBeNull();
    Event::assertDispatched(DiscoveryMatchUpdated::class);
    Event::assertDispatched(DiscoveryMatchDeleted::class);
});

it('requires and normalizes discovery match names on every mutation boundary', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    expect(fn () => app(CreateDiscoveryMatch::class)->execute(['name' => '   ']))
        ->toThrow(ValidationException::class);

    $match = app(CreateDiscoveryMatch::class)->execute(['name' => '  Initial hint  ']);
    expect($match->name)->toBe('Initial hint');

    $updated = app(UpdateDiscoveryMatch::class)->execute($match, ['name' => '  Updated hint  ']);
    expect($updated->name)->toBe('Updated hint');
});

it('persists tenant-scoped duplicate scans once and exposes them through API and Livewire', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);
    app(CreatePerson::class)->execute(['given_name' => 'Ada', 'family_name' => 'Lovelace', 'birth_date' => '1815-12-10']);
    app(CreatePerson::class)->execute(['given_name' => 'Ada', 'family_name' => 'Lovelace', 'birth_date' => '1815-12-10']);

    $first = app(ScanDuplicateCandidates::class)->execute();
    $second = app(ScanDuplicateCandidates::class)->execute();

    expect($first['created'])->toBe(1)
        ->and($second['created'])->toBe(0)
        ->and($second['existing'])->toBe(1)
        ->and(DiscoveryMatch::query()->where('kind', 'duplicate')->count())->toBe(1);

    app(TeamContext::class)->clear();
    $this->actingAs($user)->postJson('/api/v1/genealogy/discovery/duplicates/scan', ['threshold' => 0.7])
        ->assertCreated()->assertJsonPath('data.created', 0);

    app(TeamContext::class)->set($team->id);
    Livewire::actingAs($user)->test('genealogy-discovery-list')->call('scanDuplicates')->assertDispatched('genealogy-discovery-duplicates-scanned');
});

it('does not traverse living or private intermediate people in public relationship paths', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $from = app(CreatePerson::class)->execute(['given_name' => 'From', 'death_date' => '1950-01-01', 'is_public' => true]);
    $private = app(CreatePerson::class)->execute(['given_name' => 'Private', 'death_date' => '1960-01-01', 'is_public' => false]);
    $to = app(CreatePerson::class)->execute(['given_name' => 'To', 'death_date' => '1970-01-01', 'is_public' => true]);
    app(CreateRelationship::class)->execute(['person_id' => $from->id, 'related_person_id' => $private->id, 'type' => 'parent']);
    app(CreateRelationship::class)->execute(['person_id' => $private->id, 'related_person_id' => $to->id, 'type' => 'parent']);

    $path = app(RelationshipPath::class)->execute($from->id, $to->id, 6, true);

    expect($path['found'])->toBeFalse()->and($path['nodes'])->toBeEmpty();
});

it('searches normalized evidence sources while retaining legacy evidence records', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $source = app(CreateSource::class)->execute([
        'name' => 'Lancashire Parish Registers',
        'description' => 'Digitized parish records',
        'record_type' => 'parish register',
    ]);
    app(CreateCitation::class)->execute([
        'source_id' => $source->getKey(),
        'title' => 'Baptisms 1815',
        'text' => 'Lovelace family entry',
    ]);
    app(CreateEvidenceRecord::class)->execute([
        'name' => 'Legacy census index',
        'kind' => 'source',
        'citation' => 'Census 1851',
    ]);

    $results = app(DiscoverySearch::class)->execute('Lovelace');
    $sourceNames = collect($results['sources'])->pluck('name')->all();

    expect($sourceNames)->toContain('Lancashire Parish Registers');

    $legacyResults = app(DiscoverySearch::class)->execute('Census 1851');
    expect(collect($legacyResults['sources'])->pluck('name')->all())->toContain('Legacy census index');
});

it('excludes living people from public-only discovery searches', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    app(CreatePerson::class)->execute(['given_name' => 'Public Living', 'is_public' => true]);
    app(CreatePerson::class)->execute(['given_name' => 'Public Ancestor', 'death_date' => '1970-01-01', 'is_public' => true]);

    $results = app(DiscoverySearch::class)->execute('Public', ['public_only' => true]);

    expect(collect($results['people'])->pluck('name')->all())
        ->toContain('Public Ancestor')
        ->not->toContain('Public Living');
});
