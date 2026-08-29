<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\Relationships\Actions\RecordRelationship;
use Liberu\Genealogy\TreeViewer\Actions\CreateTreeView;
use Liberu\Genealogy\TreeViewer\Queries\TreeGraph;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('builds bounded pedigree and descendant graphs without exposing living people publicly', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $root = (new CreatePerson())->execute(['given_name' => 'Deceased', 'death_date' => '1950-01-01']);
    $livingChild = (new CreatePerson())->execute(['given_name' => 'Living child']);
    (new RecordRelationship())->execute(['person_id' => $root->id, 'related_person_id' => $livingChild->id, 'type' => 'parent']);
    $tree = (new CreateTreeView())->execute(['name' => 'Public tree', 'root_person_id' => $root->id, 'is_public' => true, 'status' => 'active']);

    $graph = (new TreeGraph())->for($root, 3, false, 'descendants');
    expect($graph['view'])->toBe('descendants')->and($graph['descendants'])->toBeEmpty();

    $this->actingAs($user)->getJson("/api/v1/genealogy/tree-viewer/{$tree->id}/graph?include_living=1")
        ->assertOk()->assertJsonMissing(['name' => 'Living child']);
});

it('rejects public trees rooted at living people', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $living = (new CreatePerson())->execute(['given_name' => 'Living']);

    expect(fn () => (new CreateTreeView())->execute(['name' => 'Unsafe', 'root_person_id' => $living->id, 'is_public' => true]))
        ->toThrow(InvalidArgumentException::class);
});

it('allows guests to read public trees but not private trees through the api', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $root = (new CreatePerson())->execute(['given_name' => 'Public root', 'death_date' => '1950-01-01']);
    $public = (new CreateTreeView())->execute([
        'name' => 'Public tree',
        'root_person_id' => $root->id,
        'is_public' => true,
        'status' => 'active',
    ]);
    $private = (new CreateTreeView())->execute([
        'name' => 'Private tree',
        'root_person_id' => $root->id,
        'is_public' => false,
        'status' => 'active',
    ]);

    $this->getJson("/api/v1/genealogy/tree-viewer/{$public->id}")
        ->assertOk()
        ->assertJsonPath('data.type', 'genealogy-tree-viewer');
    $this->getJson("/api/v1/genealogy/tree-viewer/{$private->id}")
        ->assertNotFound();
});

it('navigates between graph nodes through the Livewire tree viewer', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $root = (new CreatePerson())->execute(['given_name' => 'Root', 'death_date' => '1950-01-01']);
    $child = (new CreatePerson())->execute(['given_name' => 'Child', 'death_date' => '1980-01-01']);
    (new RecordRelationship())->execute(['person_id' => $root->id, 'related_person_id' => $child->id, 'type' => 'parent']);

    Livewire::actingAs($user)
        ->test('genealogy-tree-viewer-graph')
        ->set('personId', (string) $root->id)
        ->call('loadGraph')
        ->call('navigateTo', (string) $child->id)
        ->assertSet('personId', (string) $child->id)
        ->assertSee('Child');
});

it('masks living people for unauthenticated Livewire viewers', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $living = (new CreatePerson())->execute(['given_name' => 'Private living', 'is_public' => true]);

    Livewire::test('genealogy-tree-viewer-graph')
        ->set('personId', (string) $living->id)
        ->set('includeLiving', true)
        ->call('loadGraph')
        ->assertSet('data.root.name', 'Living person')
        ->assertDontSee('Private living');
});

it('restores optional sibling expansion from the legacy tree builder', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $parent = (new CreatePerson())->execute(['given_name' => 'Parent', 'death_date' => '1950-01-01']);
    $root = (new CreatePerson())->execute(['given_name' => 'Root', 'death_date' => '1980-01-01']);
    $sibling = (new CreatePerson())->execute(['given_name' => 'Sibling', 'death_date' => '1985-01-01']);
    (new RecordRelationship())->execute(['person_id' => $parent->id, 'related_person_id' => $root->id, 'type' => 'parent']);
    (new RecordRelationship())->execute(['person_id' => $parent->id, 'related_person_id' => $sibling->id, 'type' => 'parent']);

    $graph = (new TreeGraph())->for($root, 3, true, 'chart', true);

    expect($graph['siblings'])->toHaveCount(1)
        ->and($graph['siblings'][0]['name'])->toBe('Sibling')
        ->and($graph['nodes'])->toContainEqual($graph['siblings'][0]);
});
