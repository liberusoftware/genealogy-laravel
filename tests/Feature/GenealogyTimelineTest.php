<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
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

it('normalizes timeline event names on create and update', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);

    $event = (new CreateTimelineEvent())->execute(['name' => '  Birth record  ']);
    expect($event->name)->toBe('Birth record');

    $updated = (new UpdateTimelineEvent())->execute($event, ['name' => '  Updated birth record  ']);
    expect($updated->name)->toBe('Updated birth record');
});

it('rejects invalid timeline date ranges', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);

    expect(fn () => (new CreateTimelineEvent())->execute(['name' => 'Invalid', 'date_start' => '1901-01-01', 'date_end' => '1900-01-01']))
        ->toThrow(ValidationException::class);
});

it('rejects a timeline subject person from another team', function (): void {
    $firstTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($firstTeam->id);
    $person = app(CreatePerson::class)->execute(['given_name' => 'Other team']);

    $secondTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($secondTeam->id);

    expect(fn () => app(CreateTimelineEvent::class)->execute([
        'name' => 'Private event',
        'subject_person_id' => $person->getKey(),
    ]))->toThrow(InvalidArgumentException::class, 'subject person');
});

it('includes partial and open-ended dates in bounded timeline ranges', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $startOnly = (new CreateTimelineEvent())->execute(['name' => 'Starts in range', 'date_start' => '1950-01-01']);
    $endOnly = (new CreateTimelineEvent())->execute(['name' => 'Ends in range', 'date_end' => '1951-01-01']);

    $events = (new ChronologicalTimeline())->execute(from: '1949-01-01', to: '1952-01-01');
    $eventIds = array_column($events, 'id');

    expect($eventIds)->toContain($startOnly->id)->toContain($endOnly->id);
});

it('includes open-ended events that overlap a bounded range', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $startedBeforeRange = (new CreateTimelineEvent())->execute(['name' => 'Started before range', 'date_start' => '1900-01-01']);
    $endsAfterRange = (new CreateTimelineEvent())->execute(['name' => 'Ends after range', 'date_end' => '2100-01-01']);

    $eventIds = array_column((new ChronologicalTimeline())->execute(from: '1950-01-01', to: '2050-01-01'), 'id');

    expect($eventIds)->toContain($startedBeforeRange->id)->toContain($endsAfterRange->id);
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
