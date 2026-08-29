<?php

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\Actions\CreateTree;
use Liberu\Genealogy\GenealogyCore\Actions\SetTreeOwner;
use Liberu\Genealogy\GenealogyCore\Actions\SetTreeVisibility;
use Liberu\Genealogy\GenealogyCore\Actions\UpdateTree;
use Liberu\Genealogy\GenealogyCore\Events\TreeCreated;
use Liberu\Genealogy\GenealogyCore\Events\TreeUpdated;
use Liberu\Genealogy\GenealogyCore\Models\Tree;
use Liberu\Genealogy\GenealogyCore\Policies\TreePolicy;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;

uses(RefreshDatabase::class);

it('creates private trees with team and owner context and supports legacy privacy scopes', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $tree = (new CreateTree())->execute(['name' => 'Private tree', 'user_id' => $user->id]);

    expect($tree->exists)->toBeTrue()
        ->and($tree->team_id)->toBe((string) $team->id)
        ->and($tree->is_public)->toBeFalse()
        ->and(Tree::private()->whereKey($tree)->exists())->toBeTrue()
        ->and((new TreePolicy())->manage($user, $tree))->toBeTrue()
        ->and((new TreePolicy())->view(null, $tree))->toBeFalse();
});

it('allows public reads and updates only by the owner', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    app(TeamContext::class)->set($team->id);
    $tree = (new CreateTree())->execute(['name' => 'Shared tree', 'is_public' => true, 'user_id' => $owner->id]);

    $updated = (new UpdateTree())->execute($tree, ['status' => 'active', 'description' => 'Reviewed']);

    expect(Tree::public()->whereKey($updated)->value('status'))->toBe('active')
        ->and((new TreePolicy())->view(null, $updated))->toBeTrue()
        ->and((new TreePolicy())->manage(User::factory()->make(), $updated))->toBeFalse();
});

it('changes visibility through the dedicated privacy action and emits the update event', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $tree = (new CreateTree())->execute(['name' => 'Privacy action', 'user_id' => $user->id]);

    $updated = (new SetTreeVisibility())->execute($tree, true);

    expect($updated->is_public)->toBeTrue()
        ->and((new TreePolicy())->view(null, $updated))->toBeTrue();
    Event::assertDispatched(TreeUpdated::class);
});

it('assigns and clears tree ownership only for active team members', function (): void {
    Event::fake();
    $owner = User::factory()->create();
    $successor = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $team->users()->attach($successor->id, ['role' => 'member', 'status' => 'active']);
    app(TeamContext::class)->set($team->id);
    $tree = (new CreateTree())->execute(['name' => 'Owned tree', 'user_id' => $owner->id]);

    (new SetTreeOwner())->execute($tree, $successor->id);
    expect($tree->fresh()->user_id)->toBe($successor->id);
    (new SetTreeOwner())->execute($tree, null);
    expect($tree->fresh()->user_id)->toBeNull();
    Event::assertDispatched(TreeUpdated::class, 2);
});

it('allows ownership to be restored to the team owner', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $team->users()->attach($member->id, ['role' => 'member', 'status' => 'active']);
    app(TeamContext::class)->set($team->id);
    $tree = (new CreateTree())->execute(['name' => 'Owner restoration', 'user_id' => $member->id]);

    expect((new SetTreeOwner())->execute($tree, $owner->id)->user_id)->toBe($owner->id);
});

it('keeps identifiers unique per team, stores terminology, and emits lifecycle events', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $tree = (new CreateTree())->execute([
        'name' => 'Identified tree',
        'identifier' => '  primary  ',
        'terminology' => ['ancestor' => 'forebear'],
        'user_id' => $user->id,
    ]);
    (new UpdateTree())->execute($tree, ['status' => 'active']);

    expect($tree->identifier)->toBe('primary')
        ->and($tree->terminology)->toBe(['ancestor' => 'forebear']);
    Event::assertDispatched(TreeCreated::class);
    Event::assertDispatched(TreeUpdated::class);

    expect(fn () => (new CreateTree())->execute([
        'name' => 'Duplicate identifier',
        'identifier' => 'primary',
        'user_id' => $user->id,
    ]))->toThrow(UniqueConstraintViolationException::class);
});

it('rejects invalid lifecycle values before persistence', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    expect(fn () => (new CreateTree())->execute(['name' => 'Tree', 'status' => 'unknown']))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects an explicit tree owner outside the active team', function (): void {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    app(TeamContext::class)->set($team->id);

    expect(fn () => (new CreateTree())->execute(['name' => 'Unsafe owner', 'user_id' => $otherUser->id]))
        ->toThrow(InvalidArgumentException::class, 'active member');
});

it('rejects empty identifiers during tree updates', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $tree = (new CreateTree())->execute(['name' => 'Identified tree', 'identifier' => 'primary']);

    expect(fn () => (new UpdateTree())->execute($tree, ['identifier' => '   ']))
        ->toThrow(InvalidArgumentException::class, 'identifier cannot be empty');
    expect($tree->fresh()->identifier)->toBe('primary');
});

it('rejects a root person from another team', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    app(TeamContext::class)->set($team->id);
    $person = app(CreatePerson::class)->execute(['given_name' => 'Private']);

    $otherTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($otherTeam->id);

    expect(fn () => app(CreateTree::class)->execute(['name' => 'Invalid root', 'root_person_id' => $person->id]))
        ->toThrow(InvalidArgumentException::class, 'active team');
});

it('restores tree statistics across the canonical parent graph', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $grandparent = app(CreatePerson::class)->execute(['given_name' => 'Grandparent']);
    $root = app(CreatePerson::class)->execute(['given_name' => 'Root']);
    $child = app(CreatePerson::class)->execute(['given_name' => 'Child']);
    $grandchild = app(CreatePerson::class)->execute(['given_name' => 'Grandchild']);
    app(CreateRelationship::class)->execute(['person_id' => $grandparent->id, 'related_person_id' => $root->id, 'type' => 'parent']);
    app(CreateRelationship::class)->execute(['person_id' => $root->id, 'related_person_id' => $child->id, 'type' => 'parent']);
    app(CreateRelationship::class)->execute(['person_id' => $child->id, 'related_person_id' => $grandchild->id, 'type' => 'parent']);

    $tree = app(CreateTree::class)->execute(['name' => 'Statistics', 'root_person_id' => $root->id]);

    expect($tree->getStats())->toBe([
        'total_people' => 4,
        'total_ancestors' => 1,
        'total_descendants' => 2,
        'total_generations' => 2,
    ]);
});

it('keeps configurable owner and root person relationships available', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $person = app(CreatePerson::class)->execute(['given_name' => 'Root']);

    $tree = app(CreateTree::class)->execute([
        'name' => 'Legacy relationships',
        'root_person_id' => $person->id,
        'user_id' => $user->id,
    ]);

    expect($tree->rootPerson->is($person))->toBeTrue()
        ->and($tree->user->is($user))->toBeTrue();
});

it('does not expose private trees to a guest but permits public trees without tenant context', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $private = (new CreateTree())->execute(['name' => 'Private', 'user_id' => $user->id]);
    $public = (new CreateTree())->execute(['name' => 'Public', 'is_public' => true, 'user_id' => $user->id]);
    app(TeamContext::class)->clear();

    expect(Tree::query()->whereKey($private)->exists())->toBeFalse()
        ->and(Tree::query()->whereKey($public)->exists())->toBeTrue();
});

it('exposes the core API at its documented resource path', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    (new CreateTree())->execute(['name' => 'API tree', 'is_public' => true, 'user_id' => $user->id]);
    app(TeamContext::class)->clear();

    $this->getJson('/api/v1/genealogy/genealogy-core/')
        ->assertOk()
        ->assertJsonPath('data.0.type', 'genealogy-core-tree')
        ->assertJsonPath('data.0.attributes.name', 'API tree');
});

it('exposes the team-safe tree ownership API transition', function (): void {
    $owner = User::factory()->create();
    $successor = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $team->users()->attach($successor->id, ['role' => 'member', 'status' => 'active']);
    $owner->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);
    $tree = (new CreateTree())->execute(['name' => 'Ownership API', 'user_id' => $owner->id]);
    app(TeamContext::class)->clear();

    $this->actingAs($owner)->patchJson('/api/v1/genealogy/genealogy-core/'.$tree->getKey().'/owner', [
        'user_id' => $successor->id,
    ])->assertOk()->assertJsonPath('data.attributes.owner_id', $successor->id);
});

it('bounds and filters the core tree collection using the documented query shape', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    app(CreateTree::class)->execute(['name' => 'Filtered tree', 'status' => 'active', 'is_public' => true, 'user_id' => $user->id]);
    app(CreateTree::class)->execute(['name' => 'Other tree', 'status' => 'draft', 'is_public' => true, 'user_id' => $user->id]);
    app(TeamContext::class)->clear();

    $this->getJson('/api/v1/genealogy/genealogy-core/?page%5Bsize%5D=1&status=active&search=Filtered')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.attributes.name', 'Filtered tree')
        ->assertJsonPath('meta.per_page', 1);

    $this->getJson('/api/v1/genealogy/genealogy-core/?page%5Bsize%5D=101')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['page.size']);
});
