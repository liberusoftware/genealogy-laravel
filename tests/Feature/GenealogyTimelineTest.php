<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Timeline\Actions\CreateTimelineEvent;
use Liberu\Genealogy\Timeline\Actions\UpdateTimelineEvent;
use Liberu\Genealogy\Timeline\Queries\ChronologicalTimeline;
use Liberu\Genealogy\Timeline\Queries\ConflictingTimelineEvents;

uses(RefreshDatabase::class);

it('supports historical context and conflict events in chronological navigation', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $private = (new CreateTimelineEvent())->execute(['kind' => 'conflict', 'name' => 'Conflicting date', 'date_start' => '1900-01-01', 'date_end' => '1901-01-01', 'is_private' => true]);
    $public = (new CreateTimelineEvent())->execute(['kind' => 'historical_context', 'name' => 'Historical context', 'event_date' => '1899-01-01']);

    $visible = (new ChronologicalTimeline())->execute();
    expect(array_column($visible, 'id'))->not->toContain($private->id)
        ->and(array_column($visible, 'id'))->toContain($public->id);
    (new UpdateTimelineEvent())->execute($private, ['confidence' => 80]);
    expect($private->refresh()->confidence)->toBe(80);
});

it('rejects invalid timeline date ranges', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);

    expect(fn () => (new CreateTimelineEvent())->execute(['name' => 'Invalid', 'date_start' => '1901-01-01', 'date_end' => '1900-01-01']))
        ->toThrow(ValidationException::class);
});

it('groups conflicting evidence and exposes the conflict view through the API', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    (new CreateTimelineEvent())->execute(['name' => 'Birth certificate date', 'event_date' => '1900-01-01', 'conflict_group' => 'birth-date']);
    (new CreateTimelineEvent())->execute(['name' => 'Family bible date', 'event_date' => '1900-01-02', 'conflict_group' => 'birth-date']);
    (new CreateTimelineEvent())->execute(['name' => 'Private conflicting date', 'event_date' => '1900-01-03', 'conflict_group' => 'private-date', 'is_private' => true]);
    (new CreateTimelineEvent())->execute(['name' => 'Private conflicting date 2', 'event_date' => '1900-01-04', 'conflict_group' => 'private-date', 'is_private' => true]);

    expect((new ConflictingTimelineEvents())->execute())->toHaveCount(1)
        ->and((new ConflictingTimelineEvents())->execute()[0]['events'])->toHaveCount(2);
    expect((new ConflictingTimelineEvents())->execute(null, true))->toHaveCount(2);

    $this->actingAs($user)->getJson('/api/v1/genealogy/timeline/conflicts')
        ->assertOk()->assertJsonCount(1, 'data');
});
