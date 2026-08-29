<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationProposal;
use Liberu\Genealogy\Collaboration\Actions\DeleteCollaborationProposal;
use Liberu\Genealogy\Collaboration\Actions\ReviewCollaborationProposal;
use Liberu\Genealogy\Collaboration\Actions\UpdateCollaborationProposal;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;

final class CollaborationProposalController
{
    public function index(Request $request): JsonResponse
    {
        $values = $request->validate([
            'page' => ['sometimes', 'array'],
            'page.size' => ['sometimes', 'integer', 'between:1,100'],
            'status' => ['sometimes', 'in:'.implode(',', CollaborationProposal::STATUSES)],
            'pending_review' => ['sometimes', 'boolean'],
        ]);
        $records = CollaborationProposal::query()
            ->when(isset($values['status']), fn ($query) => $query->where('status', $values['status']))
            ->when(($values['pending_review'] ?? false), fn ($query) => $query->pendingReview())
            ->latest()
            ->paginate($values['page']['size'] ?? 25);

        return response()->json([
            'data' => $records->getCollection()->map(fn (CollaborationProposal $proposal): array => $this->resource($proposal))->values()->all(),
            'meta' => ['current_page' => $records->currentPage(), 'per_page' => $records->perPage(), 'total' => $records->total()],
        ]);
    }

    public function store(Request $request, CreateCollaborationProposal $create): JsonResponse
    {
        $values = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'metadata' => ['nullable', 'array'],
        ]);
        $values['proposer_id'] = $request->user()->getAuthIdentifier();

        return response()->json(['data' => $this->resource($create->execute($values))], 201);
    }

    public function show(CollaborationProposal $record): JsonResponse
    {
        return response()->json(['data' => $this->resource($record)]);
    }

    public function update(Request $request, CollaborationProposal $record, UpdateCollaborationProposal $update): JsonResponse
    {
        return response()->json(['data' => $this->resource($update->execute($record, $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:50000'],
            'metadata' => ['nullable', 'array'],
        ])))]);
    }

    public function review(Request $request, CollaborationProposal $record, ReviewCollaborationProposal $review): JsonResponse
    {
        $values = $request->validate(['status' => ['required', 'in:in_review,approved,rejected']]);

        return response()->json(['data' => $this->resource($review->execute($record, $values['status'], $request->user()->getAuthIdentifier()))]);
    }

    public function destroy(CollaborationProposal $record, DeleteCollaborationProposal $delete): JsonResponse
    {
        $delete->execute($record);

        return response()->json(status: 204);
    }

    /** @return array<string, mixed> */
    private function resource(CollaborationProposal $proposal): array
    {
        return ['id' => $proposal->getKey(), 'type' => 'genealogy-collaboration-proposal', 'attributes' => [
            'title' => $proposal->title,
            'description' => $proposal->description,
            'status' => $proposal->status,
            'proposer_id' => $proposal->proposer_id,
            'reviewer_id' => $proposal->reviewer_id,
            'reviewed_at' => $proposal->reviewed_at?->toISOString(),
            'metadata' => $proposal->metadata,
            'created_at' => $proposal->created_at?->toISOString(),
            'updated_at' => $proposal->updated_at?->toISOString(),
        ]];
    }
}
