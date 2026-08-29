<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\TreeViewer\Queries\TreeGraph;

uses(RefreshDatabase::class);

it('builds bounded pedigree, descendant, fan and chart graph views', function (): void {
    $team = Team::factory()->create();
    app(TeamContext::class)->set($team->id);
    $parent = Person::query()->create(['given_name' => 'Parent', 'death_date' => '1980-01-01']);
    $root = Person::query()->create(['given_name' => 'Root', 'death_date' => '2000-01-01']);
    $child = Person::query()->create(['given_name' => 'Child', 'death_date' => '2020-01-01']);
    $create = new CreateRelationship();
    $create->execute(['person_id' => $parent->id, 'related_person_id' => $root->id, 'type' => 'parent']);
    $create->execute(['person_id' => $root->id, 'related_person_id' => $child->id, 'type' => 'parent']);

    $graph = new TreeGraph();
    $pedigree = $graph->for($root, 2, true, 'pedigree');
    $descendants = $graph->for($root, 2, true, 'descendants');
    $chart = $graph->for($root, 2, true, 'chart');

    expect($pedigree['view'])->toBe('pedigree')
        ->and($pedigree['ancestors'])->toHaveCount(1)
        ->and($pedigree['descendants'])->toBeEmpty()
        ->and($descendants['ancestors'])->toBeEmpty()
        ->and($descendants['descendants'])->toHaveCount(1)
        ->and($chart['nodes'])->toHaveCount(3)
        ->and($chart['edges'])->toHaveCount(2)
        ->and($chart['navigation']['available_views'])->toContain('fan');
});

it('reports when a large graph reaches its explicit node limit', function (): void {
    $team = Team::factory()->create();
    app(TeamContext::class)->set($team->id);
    $root = Person::query()->create(['given_name' => 'Root', 'death_date' => '2000-01-01']);
    $create = new CreateRelationship();
    for ($index = 0; $index < 101; $index++) {
        $child = Person::query()->create(['given_name' => 'Child '.$index, 'death_date' => '2020-01-01']);
        $create->execute(['person_id' => $root->id, 'related_person_id' => $child->id, 'type' => 'parent']);
    }

    $graph = (new TreeGraph())->for($root, 1, true, 'descendants', false, 100);

    expect($graph['navigation']['max_nodes'])->toBe(100)
        ->and($graph['navigation']['truncated'])->toBeTrue()
        ->and($graph['descendants'])->toHaveCount(100);
});

it('keeps sibling expansion bounded and deduplicated', function (): void {
    $team = Team::factory()->create();
    app(TeamContext::class)->set($team->id);
    $parent = Person::query()->create(['given_name' => 'Parent', 'death_date' => '1980-01-01']);
    $root = Person::query()->create(['given_name' => 'Root', 'death_date' => '2000-01-01']);
    $create = new CreateRelationship();
    $create->execute(['person_id' => $parent->id, 'related_person_id' => $root->id, 'type' => 'parent']);

    for ($index = 0; $index < 105; $index++) {
        $sibling = Person::query()->create(['given_name' => 'Sibling '.$index, 'death_date' => '2000-01-01']);
        $create->execute(['person_id' => $parent->id, 'related_person_id' => $sibling->id, 'type' => 'parent']);
    }

    $graph = (new TreeGraph())->for($root, 1, true, 'pedigree', true, 100);
    $nodeIds = collect($graph['nodes'])->pluck('id');

    expect($graph['siblings'])->toHaveCount(100)
        ->and($graph['nodes'])->toHaveCount(100)
        ->and($nodeIds->unique())->toHaveCount(100);
});

it('includes bounded partner nodes in every graph view', function (): void {
    $team = Team::factory()->create();
    app(TeamContext::class)->set($team->id);
    $root = Person::query()->create(['given_name' => 'Root', 'death_date' => '2000-01-01']);
    $partner = Person::query()->create(['given_name' => 'Partner', 'death_date' => '2001-01-01']);

    (new CreateRelationship())->execute([
        'person_id' => $root->id,
        'related_person_id' => $partner->id,
        'type' => 'partner',
    ]);

    $graph = (new TreeGraph())->for($root, 0, true, 'chart');

    expect($graph['partners'])->toHaveCount(1)
        ->and($graph['partners'][0]['person']['name'])->toBe('Partner')
        ->and($graph['edges'][0]['direction'])->toBe('partner')
        ->and(collect($graph['nodes'])->pluck('id'))->toContain((string) $partner->id);
});
