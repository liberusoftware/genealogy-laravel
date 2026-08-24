<?php

namespace Tests\Unit;

use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceModelHierarchyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The user instance for the tests.
     *
     * @var \App\Models\User
     */
    protected User $user;

    /**
     * Set up the test environment.
     *
     * This method creates a user with a personal team and sets the
     * application context to act as that user within their team,
     * satisfying the BelongsToTenant global scope.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with a team and act as that user.
        $this->user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($this->user);
    }

    public function test_a_place_can_have_a_parent(): void
    {
        $parent = Place::factory()->create(['title' => 'USA']);
        $child = Place::factory()->create(['parent_id' => $parent->id, 'title' => 'California']);

        $this->assertNotNull($child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_a_place_can_have_children(): void
    {
        $parent = Place::factory()->create(['title' => 'USA']);
        $child1 = Place::factory()->create(['parent_id' => $parent->id, 'title' => 'California']);
        $child2 = Place::factory()->create(['parent_id' => $parent->id, 'title' => 'Texas']);

        $this->assertCount(2, $parent->children);
        $this->assertTrue($parent->children->contains($child1));
        $this->assertTrue($parent->children->contains($child2));
    }

    public function test_deleting_a_parent_place_sets_parent_id_on_children_to_null(): void
    {
        $parent = Place::factory()->create(['title' => 'USA']);
        $child = Place::factory()->create(['parent_id' => $parent->id, 'title' => 'California']);

        $parent->delete();

        $child->refresh();

        $this->assertNull($child->parent_id);
    }
}
