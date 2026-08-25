<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationProposal;
use Liberu\Genealogy\Collaboration\Actions\ReviewCollaborationProposal;
use Liberu\Genealogy\Collaboration\Events\CollaborationProposalCreated;
use Liberu\Genealogy\Collaboration\Events\CollaborationProposalReviewed;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('keeps collaboration proposals tenant-scoped and reviewable through domain actions', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $proposal = app(CreateCollaborationProposal::class)->execute([
        'title' => 'Correct a parent relationship',
        'description' => 'Review the parish register evidence before applying the change.',
        'proposer_id' => $user->id,
    ]);
    app(ReviewCollaborationProposal::class)->execute($proposal, 'approved', $user->id);

    expect($proposal->refresh()->status)->toBe('approved')
        ->and($proposal->reviewer_id)->toBe($user->id)
        ->and($proposal->reviewed_at)->not->toBeNull();
    Event::assertDispatched(CollaborationProposalCreated::class);
    Event::assertDispatched(CollaborationProposalReviewed::class);
});

it('exposes proposal creation, review, filtering, and bounded pagination through the API', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);

    $response = $this->actingAs($user)->postJson('/api/v1/genealogy/collaboration/proposals', [
        'title' => 'Review source attribution',
    ])->assertCreated()->assertJsonPath('data.type', 'genealogy-collaboration-proposal');
    $proposalId = $response->json('data.id');

    $this->actingAs($user)->postJson("/api/v1/genealogy/collaboration/proposals/{$proposalId}/review", [
        'status' => 'approved',
    ])->assertOk()->assertJsonPath('data.attributes.status', 'approved');

    $this->actingAs($user)->getJson('/api/v1/genealogy/collaboration/proposals?pending_review=1')
        ->assertOk()->assertJsonCount(0, 'data');
    $this->actingAs($user)->getJson('/api/v1/genealogy/collaboration/proposals?page%5Bsize%5D=101')
        ->assertUnprocessable()->assertJsonValidationErrors(['page.size']);
});

it('filters proposals through the Livewire list', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);
    app(CreateCollaborationProposal::class)->execute(['title' => 'Visible branch proposal']);

    Livewire::actingAs($user)
        ->test('genealogy-collaboration-proposal-list')
        ->set('search', 'Visible')
        ->assertSee('Visible branch proposal');

    expect(CollaborationProposal::query()->count())->toBe(1);
});
