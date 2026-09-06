<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;

uses(RefreshDatabase::class);

it('renders the family story dashboard with useful next actions', function (): void {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->switchTeam($team);

    $this->actingAs($user)
        ->get(Filament::getPanel('app')->getUrl())
        ->assertSuccessful()
        ->assertSee('Start with one person, then follow the clues.')
        ->assertSee('Add a person')
        ->assertSee('Import records')
        ->assertSee('Invite family');
});
