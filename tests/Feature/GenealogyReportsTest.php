<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\Reports\Actions\CreateGenealogyReport;
use Liberu\Genealogy\Reports\Actions\GenerateGenealogyReport;
use Liberu\Genealogy\Reports\Actions\UpdateGenealogyReport;
use Liberu\Genealogy\Reports\Livewire\GenealogyReportList;
use Liberu\Genealogy\Reports\Models\GenealogyReport;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('supports the issue-defined report types through domain actions', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $report = (new CreateGenealogyReport())->execute(['name' => 'Ancestor pedigree', 'type' => 'pedigree', 'status' => 'draft']);
    (new UpdateGenealogyReport())->execute($report, ['status' => 'completed', 'metadata' => ['rows' => 12]]);

    expect($report->refresh()->type)->toBe('pedigree')
        ->and($report->status)->toBe('completed')
        ->and($report->metadata['rows'])->toBe(12);
});

it('rejects unknown report types and statuses', function (): void {
    expect(fn () => (new CreateGenealogyReport())->execute(['name' => 'Invalid', 'type' => 'unknown']))
        ->toThrow(ValidationException::class);
    expect(GenealogyReport::TYPES)->toContain('family_group', 'pedigree', 'descendants', 'timeline', 'research', 'sources', 'chart');
});

it('runs a report through its tenant-safe generation lifecycle', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $report = (new CreateGenealogyReport())->execute(['name' => 'Descendants', 'type' => 'descendants']);

    $generated = (new GenerateGenealogyReport())->execute($report, ['root_person_id' => 'root', 'format' => 'json']);

    expect($generated->status)->toBe('completed')
        ->and($generated->metadata['generation']['format'])->toBe('json')
        ->and($generated->metadata['generation']['parameters']['root_person_id'])->toBe('root');
});

it('generates structured and exportable report output from the active team graph', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $parent = app(CreatePerson::class)->execute(['given_name' => 'Parent']);
    $child = app(CreatePerson::class)->execute(['given_name' => 'Child']);
    app(CreateRelationship::class)->execute(['person_id' => $parent->id, 'related_person_id' => $child->id, 'type' => 'parent']);
    $report = (new CreateGenealogyReport())->execute(['name' => 'Pedigree', 'type' => 'pedigree']);

    $generated = (new GenerateGenealogyReport())->execute($report, ['root_person_id' => $child->id, 'format' => 'gedcom']);

    expect($generated->generated_output['format'])->toBe('gedcom')
        ->and($generated->generated_output['rows'])->toBe(3)
        ->and($generated->generated_output['content'])->toContain('0 HEAD')
        ->and($generated->generated_output['content'])->toContain('Parent');
});

it('limits pedigree and descendant reports to their requested direction', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $parent = app(CreatePerson::class)->execute(['given_name' => 'Parent']);
    $root = app(CreatePerson::class)->execute(['given_name' => 'Root']);
    $child = app(CreatePerson::class)->execute(['given_name' => 'Child']);
    app(CreateRelationship::class)->execute(['person_id' => $parent->id, 'related_person_id' => $root->id, 'type' => 'parent']);
    app(CreateRelationship::class)->execute(['person_id' => $root->id, 'related_person_id' => $child->id, 'type' => 'parent']);

    $pedigree = (new CreateGenealogyReport())->execute(['name' => 'Pedigree', 'type' => 'pedigree']);
    $descendants = (new CreateGenealogyReport())->execute(['name' => 'Descendants', 'type' => 'descendants']);
    (new GenerateGenealogyReport())->execute($pedigree, ['root_person_id' => $root->id]);
    (new GenerateGenealogyReport())->execute($descendants, ['root_person_id' => $root->id]);

    $pedigreeIds = collect($pedigree->fresh()->generated_output['content']['people'])->pluck('id')->all();
    $descendantIds = collect($descendants->fresh()->generated_output['content']['people'])->pluck('id')->all();

    expect($pedigreeIds)->toContain($parent->id)->not->toContain($child->id)
        ->and($descendantIds)->toContain($child->id)->not->toContain($parent->id);
});

it('validates report generation inputs through the Livewire presentation surface', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $report = (new CreateGenealogyReport())->execute(['name' => 'Livewire report', 'type' => 'sources']);

    Livewire::actingAs($user)
        ->test(GenealogyReportList::class)
        ->set('format', 'invalid')
        ->call('generate', (string) $report->getKey())
        ->assertHasErrors(['format']);

    Livewire::actingAs($user)
        ->test(GenealogyReportList::class)
        ->set('format', 'csv')
        ->call('generate', (string) $report->getKey())
        ->assertDispatched('genealogy-report-generated');

    expect($report->fresh()->generated_output['format'])->toBe('csv');
});
