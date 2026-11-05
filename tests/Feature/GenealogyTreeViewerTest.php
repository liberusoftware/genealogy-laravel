<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Actions\RecordRelationship;
use Liberu\Genealogy\TreeViewer\Actions\CreateTreeView;
use Liberu\Genealogy\TreeViewer\Actions\UpdateTreeView;
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

it('rejects private tree roots outside the active team', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    app(TeamContext::class)->set($team->id);
    $local = (new CreatePerson())->execute(['given_name' => 'Local']);
    $tree = (new CreateTreeView())->execute(['name' => 'Private tree', 'root_person_id' => $local->id]);

    $otherTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($otherTeam->id);
    $remote = (new CreatePerson())->execute(['given_name' => 'Remote']);
    app(TeamContext::class)->set($team->id);

    expect(fn () => (new CreateTreeView())->execute(['name' => 'Unsafe private tree', 'root_person_id' => $remote->id]))
        ->toThrow(InvalidArgumentException::class, 'active team');
    expect(fn () => (new UpdateTreeView())->execute($tree, ['root_person_id' => $remote->id]))
        ->toThrow(InvalidArgumentException::class, 'active team');
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

it('validates graph view changes at the Livewire boundary', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    Livewire::actingAs($user)
        ->test('genealogy-tree-viewer-graph')
        ->call('setView', 'unsupported')
        ->assertHasErrors(['view']);
});

it('validates tree viewer lifecycle status across domain and Livewire boundaries', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $tree = (new CreateTreeView())->execute(['name' => 'Completed tree', 'status' => 'completed']);
    expect($tree->status)->toBe('completed');

    expect(fn () => (new UpdateTreeView())->execute($tree, ['status' => 'unsupported']))
        ->toThrow(InvalidArgumentException::class);

    Livewire::actingAs($user)
        ->test('genealogy-tree-viewer-list')
        ->set('status', 'unsupported')
        ->assertHasErrors(['status']);
});

it('forbids guests from listing private tree viewer records through Livewire', function (): void {
    Livewire::test('genealogy-tree-viewer-list')->assertForbidden();
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

it('finds people by full name within the active team', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    (new CreatePerson())->execute(['given_name' => 'Ada', 'family_name' => 'Lovelace']);
    $otherTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($otherTeam->id);
    (new CreatePerson())->execute(['given_name' => 'Ada', 'family_name' => 'Lovelace', 'display_name' => 'Hidden match']);
    app(TeamContext::class)->set($team->id);

    Livewire::actingAs($user)->test('genealogy-tree-viewer-graph')
        ->set('search', ' Ada Lovelace ')
        ->assertSee('Ada Lovelace')
        ->assertDontSee('Hidden match');
});

it('loads an initial person and returns to them after exploring relatives', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $root = (new CreatePerson())->execute(['given_name' => 'Starting person']);
    $child = (new CreatePerson())->execute(['given_name' => 'Child']);

    Livewire::actingAs($user)->test('genealogy-tree-viewer-graph', ['personId' => (string) $root->id])
        ->assertSet('data.root.id', (string) $root->id)
        ->call('navigateTo', (string) $child->id)
        ->assertSee('Back to starting person')
        ->call('returnToStart')
        ->assertSet('data.root.id', (string) $root->id);
});

it('adds a private relative with the correct relationship direction', function (string $type): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $root = (new CreatePerson())->execute(['given_name' => 'Root']);

    Livewire::actingAs($user)->test('genealogy-tree-viewer-graph', ['personId' => (string) $root->id])
        ->set('relativeType', $type)
        ->set('givenName', 'New relative')
        ->set('birthDate', '1950-01-01')
        ->call('addRelative')
        ->assertHasNoErrors()
        ->assertSet('givenName', '')
        ->assertSee('Relative added.');

    $relative = Person::query()->where('given_name', 'New relative')->sole();
    expect($relative->is_public)->toBeFalse();
    $this->assertDatabaseHas('genealogy_relationships', [
        'person_id' => $type === 'parent' ? $relative->id : $root->id,
        'related_person_id' => $type === 'parent' ? $root->id : $relative->id,
        'type' => $type === 'partner' ? 'partner' : 'parent',
        'team_id' => $team->id,
    ]);
})->with(['parent', 'child', 'partner']);

it('keeps invalid relative dates from creating a person', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $root = (new CreatePerson())->execute(['given_name' => 'Root']);

    Livewire::actingAs($user)->test('genealogy-tree-viewer-graph', ['personId' => (string) $root->id])
        ->set('givenName', 'Invalid relative')
        ->set('birthDate', '1950-01-01')
        ->set('deathDate', '1940-01-01')
        ->call('addRelative')
        ->assertHasErrors(['deathDate']);

    $this->assertDatabaseMissing('genealogy_people', ['given_name' => 'Invalid relative']);
});

it('forbids guests from creating relatives and does not offer name search', function (): void {
    Livewire::test('genealogy-tree-viewer-graph')
        ->set('search', 'Private')
        ->assertViewHas('people', fn ($people): bool => $people->isEmpty())
        ->assertDontSee('Find a person')
        ->call('addRelative')
        ->assertForbidden();
});

it('cannot add a relative to someone outside the active team', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $otherTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($otherTeam->id);
    $remote = (new CreatePerson())->execute(['given_name' => 'Remote']);
    app(TeamContext::class)->set($team->id);

    Livewire::actingAs($user)->test('genealogy-tree-viewer-graph')
        ->set('personId', (string) $remote->id)
        ->set('givenName', 'Forbidden relative')
        ->call('addRelative')
        ->assertNotFound();

    $this->assertDatabaseMissing('genealogy_people', ['given_name' => 'Forbidden relative']);
});

it('renders the family explorer on the saved tree page', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->id])->save();
    app(TeamContext::class)->set($team->id);
    $root = (new CreatePerson())->execute(['given_name' => 'Saved tree root']);
    $tree = (new CreateTreeView())->execute(['name' => 'Our family', 'root_person_id' => $root->id]);
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('app'));
    Filament::setTenant($team);

    Livewire::actingAs($user)
        ->test(Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages\TreeGraph::class, ['record' => (string) $tree->id])
        ->assertSeeLivewire('genealogy-tree-viewer-graph')
        ->assertSee('Saved tree root');
});
