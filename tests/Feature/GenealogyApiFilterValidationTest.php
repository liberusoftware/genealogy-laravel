<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;

uses(RefreshDatabase::class);

it('rejects unsupported enum values on genealogy collection filters', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->getKey());

    $this->actingAs($user)->getJson('/api/v1/genealogy/discovery?kind=unsupported')
        ->assertUnprocessable()->assertJsonValidationErrors(['kind']);
    $this->actingAs($user)->getJson('/api/v1/genealogy/media?kind=unsupported')
        ->assertUnprocessable()->assertJsonValidationErrors(['kind']);
    $this->actingAs($user)->getJson('/api/v1/genealogy/reports?type=unsupported')
        ->assertUnprocessable()->assertJsonValidationErrors(['type']);
    $this->actingAs($user)->getJson('/api/v1/genealogy/timeline?kind=unsupported')
        ->assertUnprocessable()->assertJsonValidationErrors(['kind']);
    $this->actingAs($user)->getJson('/api/v1/genealogy/research?status=unsupported')
        ->assertUnprocessable()->assertJsonValidationErrors(['status']);
});
