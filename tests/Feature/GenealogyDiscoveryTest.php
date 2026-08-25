<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\Discovery\Actions\CreateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\DeleteDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\ScanDuplicateCandidates;
use Liberu\Genealogy\Discovery\Actions\UpdateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchDeleted;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchReviewed;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchUpdated;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
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
