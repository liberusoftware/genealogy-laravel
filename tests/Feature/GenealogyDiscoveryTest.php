<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\Discovery\Actions\CreateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\DeleteDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\UpdateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchDeleted;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchReviewed;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchUpdated;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;
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
