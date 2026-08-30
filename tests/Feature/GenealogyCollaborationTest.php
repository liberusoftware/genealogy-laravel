<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\Collaboration\Actions\AcceptCollaborationInvitation;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationProposal;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationSpace;
use Liberu\Genealogy\Collaboration\Actions\DeleteCollaborationSpace;
use Liberu\Genealogy\Collaboration\Actions\InviteCollaborationMember;
use Liberu\Genealogy\Collaboration\Actions\RecordCollaborationAttribution;
use Liberu\Genealogy\Collaboration\Actions\ReviewCollaborationProposal;
use Liberu\Genealogy\Collaboration\Actions\ToggleCollaborationWatch;
use Liberu\Genealogy\Collaboration\Actions\UpdateCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Actions\UpdateCollaborationSpace;
use Liberu\Genealogy\Collaboration\Events\CollaborationProposalCreated;
use Liberu\Genealogy\Collaboration\Events\CollaborationProposalReviewed;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationInvitationResource;
use Liberu\Genealogy\Collaboration\Models\CollaborationInvitation;
use Liberu\Genealogy\Collaboration\Models\CollaborationMembership;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;
use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;
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

it('serializes collaboration spaces through an explicit API resource', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);

    $response = $this->actingAs($user)->postJson('/api/v1/genealogy/collaboration', [
        'name' => 'Archive review',
        'status' => 'active',
        'metadata' => ['source' => 'api'],
    ])->assertCreated()
        ->assertJsonPath('data.type', 'genealogy-collaboration-space')
        ->assertJsonPath('data.attributes.name', 'Archive review')
        ->assertJsonPath('data.attributes.status', 'active');

    $this->actingAs($user)->getJson('/api/v1/genealogy/collaboration')
        ->assertOk()
        ->assertJsonPath('data.0.type', 'genealogy-collaboration-space')
        ->assertJsonMissingPath('data.0.team_id');

    $this->actingAs($user)->postJson('/api/v1/genealogy/collaboration', [
        'name' => 'Invalid space',
        'status' => 'unsupported',
    ])->assertUnprocessable()->assertJsonValidationErrors(['status']);

    expect($response->json('data.attributes.metadata'))->toBe(['source' => 'api']);
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

    Livewire::actingAs($user)
        ->test('genealogy-collaboration-proposal-list')
        ->set('status', 'unsupported')
        ->assertHasErrors(['status']);

    expect(CollaborationProposal::query()->count())->toBe(1);
});

it('forbids guests from collaboration list surfaces', function (): void {
    Livewire::test('genealogy-collaboration-list')->assertForbidden();
    Livewire::test('genealogy-collaboration-proposal-list')->assertForbidden();
    Livewire::test('genealogy-collaboration-watch-list')->assertForbidden();
});

it('supports tenant-scoped collaboration invitations, roles, discussions, watches, and attribution', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    app(TeamContext::class)->set($team->id);

    $invitation = app(InviteCollaborationMember::class)->execute([
        'email' => $member->email,
        'role' => 'reviewer',
        'invited_by' => $owner->id,
    ]);
    $membership = app(AcceptCollaborationInvitation::class)->execute($invitation, $member);
    $discussion = app(CreateCollaborationDiscussion::class)->execute([
        'body' => 'Please review the attached source before approval.',
        'author_id' => $member->id,
    ]);
    $watch = app(ToggleCollaborationWatch::class)->execute('discussion', (string) $discussion->getKey(), $member->id);
    $attribution = app(RecordCollaborationAttribution::class)->execute('discussion', (string) $discussion->getKey(), 'created', [], $member->id);

    expect($invitation->refresh()->status)->toBe('accepted')
        ->and($membership)->toBeInstanceOf(CollaborationMembership::class)
        ->and($membership->role)->toBe('reviewer')
        ->and($watch)->not->toBeNull()
        ->and($attribution->action)->toBe('created')
        ->and(CollaborationInvitation::query()->count())->toBe(1);
});

it('rejects cross-team collaboration space and proposal references', function (): void {
    $localOwner = User::factory()->create();
    $remoteOwner = User::factory()->create();
    $localTeam = Team::factory()->create(['user_id' => $localOwner->id]);
    $remoteTeam = Team::factory()->create(['user_id' => $remoteOwner->id]);

    app(TeamContext::class)->set($remoteTeam->id);
    $remoteSpace = app(CreateCollaborationSpace::class)->execute(['name' => 'Remote space']);
    $remoteProposal = app(CreateCollaborationProposal::class)->execute(['title' => 'Remote proposal', 'proposer_id' => $remoteOwner->id]);

    app(TeamContext::class)->set($localTeam->id);
    expect(fn () => app(InviteCollaborationMember::class)->execute([
        'email' => $localOwner->email,
        'space_id' => $remoteSpace->getKey(),
    ]))->toThrow(InvalidArgumentException::class, 'active team');
    expect(fn () => app(CreateCollaborationDiscussion::class)->execute([
        'body' => 'Cross-team reference',
        'space_id' => $remoteSpace->getKey(),
    ]))->toThrow(InvalidArgumentException::class, 'active team');
    expect(fn () => app(CreateCollaborationDiscussion::class)->execute([
        'body' => 'Cross-team proposal reference',
        'proposal_id' => $remoteProposal->getKey(),
    ]))->toThrow(InvalidArgumentException::class, 'active team');
});

it('rejects incomplete collaboration attribution records', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    expect(fn () => app(RecordCollaborationAttribution::class)->execute('discussion', 'record-1', '  '))
        ->toThrow(InvalidArgumentException::class, 'required');
});

it('enforces discussion statuses through domain actions', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    expect(fn () => app(CreateCollaborationDiscussion::class)->execute([
        'body' => 'Invalid status',
        'status' => 'unsupported',
    ]))->toThrow(InvalidArgumentException::class, 'status is invalid');

    $discussion = app(CreateCollaborationDiscussion::class)->execute(['body' => 'Valid discussion']);
    expect(fn () => app(UpdateCollaborationDiscussion::class)->execute($discussion, [
        'status' => 'unsupported',
    ]))->toThrow(InvalidArgumentException::class, 'status is invalid');
});

it('does not expose direct invitation editing in the Filament adapter', function (): void {
    expect(CollaborationInvitationResource::getPages())->toHaveKeys(['index', 'create'])
        ->not->toHaveKey('edit');
});

it('validates collaboration Livewire review inputs', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->getKey());
    $proposal = app(CreateCollaborationProposal::class)->execute(['title' => 'Review me']);

    Livewire::actingAs($user)
        ->test('genealogy-collaboration-proposal-editor')
        ->set('proposalId', $proposal->getKey())
        ->call('review', 'unsupported')
        ->assertHasErrors(['status']);
});

it('runs invitation lifecycle mutations through the Livewire action boundary', function (): void {
    $owner = User::factory()->create();
    $invitee = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $owner->forceFill(['current_team_id' => $team->getKey()])->save();
    $invitee->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->getKey());

    Livewire::actingAs($owner)
        ->test('genealogy-collaboration-invitation-list')
        ->set('email', $invitee->email)
        ->set('role', 'reviewer')
        ->call('invite')
        ->assertDispatched('collaboration-invitation-created');

    Livewire::actingAs($owner)
        ->test('genealogy-collaboration-invitation-list')
        ->set('status', 'unsupported')
        ->assertHasErrors(['status']);

    $invitation = CollaborationInvitation::query()->firstOrFail();
    expect($invitation->email)->toBe(mb_strtolower($invitee->email))
        ->and($invitation->status)->toBe('pending');

    app(TeamContext::class)->set($team->getKey());
    Livewire::actingAs($owner)
        ->test('genealogy-collaboration-invitation-list')
        ->call('revoke', (string) $invitation->getKey())
        ->assertDispatched('collaboration-invitation-revoked');
    expect($invitation->refresh()->status)->toBe('revoked');

    app(TeamContext::class)->set($team->getKey());
    $pending = app(InviteCollaborationMember::class)->execute([
        'email' => $invitee->email,
        'role' => 'viewer',
    ]);
    app(TeamContext::class)->set($team->getKey());
    Livewire::actingAs($invitee)
        ->test('genealogy-collaboration-invitation-list')
        ->call('accept', (string) $pending->getKey())
        ->assertDispatched('collaboration-invitation-accepted');

    expect($pending->refresh()->status)->toBe('accepted');
});

it('exposes collaboration workflow operations through the authenticated API', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $owner->forceFill(['current_team_id' => $team->getKey()])->save();
    $member->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);

    $invitation = $this->actingAs($owner)->postJson('/api/v1/genealogy/collaboration/invitations', [
        'email' => $member->email,
        'role' => 'editor',
    ])->assertCreated()->assertJsonPath('data.type', 'genealogy-collaboration-invitation');
    $invitationId = $invitation->json('data.id');

    $this->actingAs($member)->postJson("/api/v1/genealogy/collaboration/invitations/{$invitationId}/accept")
        ->assertOk()->assertJsonPath('data.attributes.role', 'editor');
    $discussion = $this->actingAs($owner)->postJson('/api/v1/genealogy/collaboration/discussions', [
        'body' => 'A review note',
    ])->assertCreated()->assertJsonPath('data.type', 'genealogy-collaboration-discussion');
    $discussionId = $discussion->json('data.id');
    $this->actingAs($owner)->postJson('/api/v1/genealogy/collaboration/watches/toggle', [
        'watchable_type' => 'discussion', 'watchable_id' => $discussionId,
    ])->assertOk()->assertJsonPath('data.attributes.watchable_id', $discussionId);
    $this->actingAs($owner)->getJson('/api/v1/genealogy/collaboration/memberships')->assertOk()->assertJsonCount(1, 'data');
    $this->actingAs($owner)->getJson('/api/v1/genealogy/collaboration/discussions')->assertOk()->assertJsonCount(1, 'data');
    $this->actingAs($owner)->getJson('/api/v1/genealogy/collaboration/watches')->assertOk()->assertJsonCount(1, 'data');
});

it('keeps collaboration space CRUD mutations behind tenant-safe actions', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $space = app(CreateCollaborationSpace::class)->execute(['name' => 'Original space', 'status' => 'draft']);

    $updated = app(UpdateCollaborationSpace::class)->execute($space, ['name' => 'Updated space', 'status' => 'active']);
    app(DeleteCollaborationSpace::class)->execute($updated);

    expect($space->refresh()->name)->toBe('Updated space')
        ->and(CollaborationSpace::query()->find($space->getKey()))->toBeNull();
});

it('validates and normalizes collaboration spaces at both mutation boundaries', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    expect(fn () => app(CreateCollaborationSpace::class)->execute(['name' => '   ']))
        ->toThrow(InvalidArgumentException::class, 'name is required');

    $space = app(CreateCollaborationSpace::class)->execute(['name' => '  Research space  ']);
    expect($space->name)->toBe('Research space');

    expect(fn () => app(CreateCollaborationSpace::class)->execute(['name' => 'Space', 'status' => 'invalid']))
        ->toThrow(InvalidArgumentException::class, 'status is invalid');
    expect(fn () => app(UpdateCollaborationSpace::class)->execute($space, ['status' => 'invalid']))
        ->toThrow(InvalidArgumentException::class, 'status is invalid');

    expect(app(UpdateCollaborationSpace::class)->execute($space, ['name' => '  Updated space  '])->name)
        ->toBe('Updated space');
});
