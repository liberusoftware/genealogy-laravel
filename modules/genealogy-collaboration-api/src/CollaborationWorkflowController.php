<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Liberu\Genealogy\Collaboration\Actions\AcceptCollaborationInvitation;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Actions\DeleteCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Actions\InviteCollaborationMember;
use Liberu\Genealogy\Collaboration\Actions\RevokeCollaborationInvitation;
use Liberu\Genealogy\Collaboration\Actions\SetCollaborationMembershipRole;
use Liberu\Genealogy\Collaboration\Actions\ToggleCollaborationWatch;
use Liberu\Genealogy\Collaboration\Actions\UpdateCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Models\CollaborationAttribution;
use Liberu\Genealogy\Collaboration\Models\CollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Models\CollaborationInvitation;
use Liberu\Genealogy\Collaboration\Models\CollaborationMembership;
use Liberu\Genealogy\Collaboration\Models\CollaborationWatch;

final class CollaborationWorkflowController
{
    public function invitations(Request $request): JsonResponse
    {
        $values = $request->validate(['page' => ['sometimes', 'array'], 'page.size' => ['sometimes', 'integer', 'between:1,100'], 'status' => ['sometimes', 'in:pending,accepted,revoked,expired']]);
        $records = CollaborationInvitation::query()->when(isset($values['status']), fn ($query) => $query->where('status', $values['status']))->latest()->paginate($values['page']['size'] ?? 25);

        return $this->collection($records->getCollection()->map(fn (CollaborationInvitation $record): array => $this->invitation($record))->all(), $records);
    }

    public function invite(Request $request, InviteCollaborationMember $invite): JsonResponse
    {
        $values = $request->validate(['space_id' => ['nullable', 'uuid'], 'email' => ['required', 'email', 'max:255'], 'role' => ['sometimes', 'in:viewer,contributor,reviewer,editor,owner'], 'expires_at' => ['nullable', 'date', 'after:now']]);
        $values['invited_by'] = $request->user()->getAuthIdentifier();

        return response()->json(['data' => $this->invitation($invite->execute($values))], 201);
    }

    public function accept(string $record, AcceptCollaborationInvitation $accept): JsonResponse
    {
        $invitation = CollaborationInvitation::query()->withoutGlobalScopes()->findOrFail($record);

        return response()->json(['data' => $this->membership($accept->execute($invitation, request()->user()))]);
    }

    public function revoke(CollaborationInvitation $record, RevokeCollaborationInvitation $revoke): JsonResponse
    {
        return response()->json(['data' => $this->invitation($revoke->execute($record))]);
    }

    public function memberships(Request $request): JsonResponse
    {
        $values = $request->validate(['page' => ['sometimes', 'array'], 'page.size' => ['sometimes', 'integer', 'between:1,100'], 'space_id' => ['sometimes', 'uuid'], 'user_id' => ['sometimes', 'integer']]);
        $records = CollaborationMembership::query()->when(isset($values['space_id']), fn ($query) => $query->where('space_id', $values['space_id']))->when(isset($values['user_id']), fn ($query) => $query->where('user_id', $values['user_id']))->latest()->paginate($values['page']['size'] ?? 25);

        return $this->collection($records->getCollection()->map(fn (CollaborationMembership $record): array => $this->membership($record))->all(), $records);
    }

    public function updateMembership(Request $request, CollaborationMembership $record, SetCollaborationMembershipRole $setRole): JsonResponse
    {
        $values = $request->validate(['role' => ['required', 'in:viewer,contributor,reviewer,editor,owner']]);

        return response()->json(['data' => $this->membership($setRole->execute($record, $values['role']))]);
    }

    public function discussions(Request $request): JsonResponse
    {
        $values = $request->validate(['page' => ['sometimes', 'array'], 'page.size' => ['sometimes', 'integer', 'between:1,100'], 'space_id' => ['sometimes', 'uuid'], 'proposal_id' => ['sometimes', 'uuid'], 'status' => ['sometimes', 'in:open,resolved,archived']]);
        $records = CollaborationDiscussion::query()->when(isset($values['space_id']), fn ($query) => $query->where('space_id', $values['space_id']))->when(isset($values['proposal_id']), fn ($query) => $query->where('proposal_id', $values['proposal_id']))->when(isset($values['status']), fn ($query) => $query->where('status', $values['status']))->latest()->paginate($values['page']['size'] ?? 25);

        return $this->collection($records->getCollection()->map(fn (CollaborationDiscussion $record): array => $this->discussion($record))->all(), $records);
    }

    public function createDiscussion(Request $request, CreateCollaborationDiscussion $create): JsonResponse
    {
        $values = $request->validate(['space_id' => ['nullable', 'uuid'], 'proposal_id' => ['nullable', 'uuid'], 'body' => ['required', 'string', 'max:50000'], 'status' => ['sometimes', 'in:open,resolved,archived'], 'metadata' => ['nullable', 'array']]);
        $values['author_id'] = $request->user()->getAuthIdentifier();

        return response()->json(['data' => $this->discussion($create->execute($values))], 201);
    }

    public function updateDiscussion(Request $request, CollaborationDiscussion $record, UpdateCollaborationDiscussion $update): JsonResponse
    {
        return response()->json(['data' => $this->discussion($update->execute($record, $request->validate(['body' => ['sometimes', 'string', 'max:50000'], 'status' => ['sometimes', 'in:open,resolved,archived'], 'metadata' => ['nullable', 'array']])))]);
    }

    public function deleteDiscussion(CollaborationDiscussion $record, DeleteCollaborationDiscussion $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    public function watches(Request $request): JsonResponse
    {
        $records = CollaborationWatch::query()->where('user_id', $request->user()->getAuthIdentifier())->latest()->get();

        return response()->json(['data' => $records->map(fn (CollaborationWatch $record): array => $this->watch($record))->all()]);
    }

    public function toggleWatch(Request $request, ToggleCollaborationWatch $toggle): JsonResponse
    {
        $values = $request->validate(['watchable_type' => ['required', 'string', 'regex:/^[A-Za-z0-9_.\\-]+$/'], 'watchable_id' => ['required', 'string', 'max:255']]);
        $watch = $toggle->execute($values['watchable_type'], $values['watchable_id'], $request->user()->getAuthIdentifier());

        return response()->json(['data' => $watch === null ? null : $this->watch($watch)]);
    }

    public function attributions(Request $request): JsonResponse
    {
        $values = $request->validate(['page' => ['sometimes', 'array'], 'page.size' => ['sometimes', 'integer', 'between:1,100'], 'attributable_type' => ['sometimes', 'string'], 'attributable_id' => ['sometimes', 'string']]);
        $records = CollaborationAttribution::query()->when(isset($values['attributable_type']), fn ($query) => $query->where('attributable_type', $values['attributable_type']))->when(isset($values['attributable_id']), fn ($query) => $query->where('attributable_id', $values['attributable_id']))->latest('created_at')->paginate($values['page']['size'] ?? 25);

        return $this->collection($records->getCollection()->map(fn (CollaborationAttribution $record): array => $this->attribution($record))->all(), $records);
    }

    private function collection(array $data, mixed $records): JsonResponse
    {
        return response()->json(['data' => array_values($data), 'meta' => ['current_page' => $records->currentPage(), 'per_page' => $records->perPage(), 'total' => $records->total()]]);
    }

    /** @return array<string, mixed> */
    private function invitation(CollaborationInvitation $record): array
    {
        return $this->resource($record, ['email' => $record->email, 'space_id' => $record->space_id, 'role' => $record->role, 'status' => $record->status, 'invited_by' => $record->invited_by, 'expires_at' => $record->expires_at?->toISOString(), 'accepted_at' => $record->accepted_at?->toISOString()]);
    }

    /** @return array<string, mixed> */
    private function membership(CollaborationMembership $record): array
    {
        return $this->resource($record, ['space_id' => $record->space_id, 'user_id' => $record->user_id, 'role' => $record->role, 'status' => $record->status, 'joined_at' => $record->joined_at?->toISOString()]);
    }

    /** @return array<string, mixed> */
    private function discussion(CollaborationDiscussion $record): array
    {
        return $this->resource($record, ['space_id' => $record->space_id, 'proposal_id' => $record->proposal_id, 'author_id' => $record->author_id, 'body' => $record->body, 'status' => $record->status, 'metadata' => $record->metadata]);
    }

    /** @return array<string, mixed> */
    private function watch(CollaborationWatch $record): array
    {
        return $this->resource($record, ['user_id' => $record->user_id, 'watchable_type' => $record->watchable_type, 'watchable_id' => $record->watchable_id]);
    }

    /** @return array<string, mixed> */
    private function attribution(CollaborationAttribution $record): array
    {
        return $this->resource($record, ['actor_id' => $record->actor_id, 'attributable_type' => $record->attributable_type, 'attributable_id' => $record->attributable_id, 'action' => $record->action, 'metadata' => $record->metadata, 'created_at' => $record->created_at?->toISOString()]);
    }

    /** @param array<string, mixed> $attributes */
    private function resource(object $record, array $attributes): array
    {
        return ['id' => $record->getKey(), 'type' => 'genealogy-collaboration-'.Str::kebab(str_replace('Collaboration', '', class_basename($record))), 'attributes' => $attributes + ['team_id' => $record->team_id, 'created_at' => $record->created_at?->toISOString(), 'updated_at' => $record->updated_at?->toISOString()]];
    }
}
