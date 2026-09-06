<?php

use App\Filament\App\Pages\WorkspaceSetup;
use App\Models\TeamIntegration;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app'));
});

it('creates a workspace and completes onboarding for a new user', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(WorkspaceSetup::class)
        ->fillForm([
            'name' => $user->name,
            'team_name' => 'The Lovelace family',
            'locale' => 'en',
            'timezone' => 'UTC',
            'api_key_enabled' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->onboarding_completed_at)->not->toBeNull()
        ->and($user->fresh()->current_team_id)->not->toBeNull()
        ->and(Team::query()->where('name', 'The Lovelace family')->exists())->toBeTrue();
});

it('stores the project integration encrypted and can disable it', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->switchTeam($team);

    Livewire::actingAs($user)
        ->test(WorkspaceSetup::class)
        ->fillForm([
            'name' => $user->name,
            'team_name' => $team->name,
            'locale' => 'en',
            'timezone' => 'UTC',
            'api_key_enabled' => true,
            'provider' => 'FamilySearch',
            'api_key' => 'secret-api-key',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $integration = TeamIntegration::query()->whereBelongsTo($team)->firstOrFail();

    expect($integration->credentials)->toBe(['api_key' => 'secret-api-key'])
        ->and($integration->getRawOriginal('credentials'))->not->toContain('secret-api-key');

    Livewire::actingAs($user)
        ->test(WorkspaceSetup::class)
        ->fillForm([
            'name' => $user->name,
            'team_name' => $team->name,
            'locale' => 'en',
            'timezone' => 'UTC',
            'api_key_enabled' => false,
        ])
        ->call('save');

    expect($integration->fresh()->enabled)->toBeFalse();
});
