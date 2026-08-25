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
