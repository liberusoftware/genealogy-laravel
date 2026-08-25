<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Research\Actions\CreateResearchEntry;
use Liberu\Genealogy\Research\Actions\CreateResearchProject;
use Liberu\Genealogy\Research\Actions\DeleteResearchEntry;
use Liberu\Genealogy\Research\Actions\UpdateResearchEntry;
use Liberu\Genealogy\Research\Events\ResearchEntryCreated;
use Liberu\Genealogy\Research\Events\ResearchEntryDeleted;
use Liberu\Genealogy\Research\Events\ResearchEntryUpdated;
use Liberu\Genealogy\Research\Livewire\ResearchEntryList;
use Liberu\Genealogy\Research\Models\ResearchEntry;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('keeps research entries inside their active project tenant and supports lifecycle updates', function (): void {
    Event::fake();
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $project = (new CreateResearchProject())->execute(['name' => 'Parish research', 'status' => 'active']);
    $entry = (new CreateResearchEntry())->execute(['research_project_id' => $project->id, 'kind' => 'negative_search', 'title' => 'No baptism found']);
    (new UpdateResearchEntry())->execute($entry, ['status' => 'completed', 'body' => 'Checked the 1840 register.']);

    expect($entry->refresh()->status)->toBe('completed')
        ->and($entry->body)->toContain('1840')
        ->and(ResearchEntry::KINDS)->toHaveCount(7);
    Event::assertDispatched(ResearchEntryCreated::class);
    Event::assertDispatched(ResearchEntryUpdated::class);

    (new DeleteResearchEntry())->execute($entry);
    Event::assertDispatched(ResearchEntryDeleted::class);
});

it('rejects unsupported research entry kinds', function (): void {
    expect(fn () => (new CreateResearchEntry())->execute(['research_project_id' => 'missing', 'kind' => 'unknown', 'title' => 'Invalid']))
        ->toThrow(InvalidArgumentException::class);
});

it('filters research queues by kind, status, and overdue work through the API', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $project = (new CreateResearchProject())->execute(['name' => 'Research queue', 'status' => 'active']);
    (new CreateResearchEntry())->execute(['research_project_id' => $project->id, 'kind' => 'task', 'title' => 'Overdue task', 'due_date' => now()->subDay()->toDateString()]);
    (new CreateResearchEntry())->execute(['research_project_id' => $project->id, 'kind' => 'finding', 'title' => 'Completed finding', 'status' => 'completed']);

    $this->actingAs($user)->getJson('/api/v1/genealogy/research/'.$project->id.'/entries?kind=task&overdue=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.attributes.kind', 'task');
});

it('completes research entries through the tenant-scoped Livewire list', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $project = (new CreateResearchProject())->execute(['name' => 'Livewire research', 'status' => 'active']);
    $entry = (new CreateResearchEntry())->execute([
        'research_project_id' => $project->id,
        'kind' => 'task',
        'title' => 'Review correspondence',
    ]);

    Livewire::actingAs($user)
        ->test(ResearchEntryList::class)
        ->set('projectId', (string) $project->id)
        ->call('complete', (string) $entry->id)
        ->assertDispatched('research-entry-completed');

    expect($entry->fresh()->status)->toBe('completed')
        ->and($entry->fresh()->completed_at)->not->toBeNull();
});
