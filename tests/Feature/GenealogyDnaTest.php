<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;

uses(RefreshDatabase::class);

it('exposes provider-neutral DNA segment analysis through the authenticated API', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);
    $kitA = ['1' => []];
    $kitB = ['1' => []];
    for ($index = 0; $index < 500; $index++) {
        $position = 1_000_000 + ($index * 50_000);
        $kitA['1'][(string) $position] = 'AG';
        $kitB['1'][(string) $position] = 'AA';
    }

    $this->actingAs($user)->postJson('/api/v1/genealogy/dna/matches/analyze', [
        'kit_a' => $kitA,
        'kit_b' => $kitB,
    ])->assertOk()
        ->assertJsonPath('data.shared_segments_count', 1)
        ->assertJsonPath('data.predicted_relationship', 'Distant Cousin');
});
