<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationDiscussion;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateCollaborationDiscussion
{
    public function execute(CollaborationDiscussion $discussion, array $attributes): CollaborationDiscussion
    {
        if ((string) $discussion->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The discussion must belong to the active team.');
        }
        $body = trim((string) ($attributes['body'] ?? $discussion->body));
        if ($body === '') {
            throw new InvalidArgumentException('A discussion message is required.');
        }
        $status = $attributes['status'] ?? $discussion->status;
        if (! in_array($status, CollaborationDiscussion::STATUSES, true)) {
            throw new InvalidArgumentException('The collaboration discussion status is invalid.');
        }

        DB::transaction(fn (): bool => $discussion->update(['body' => $body, 'status' => $status, 'metadata' => $attributes['metadata'] ?? $discussion->metadata]));

        $discussion = $discussion->refresh();
        app(RecordCollaborationAttribution::class)->execute('discussion', (string) $discussion->getKey(), 'updated');

        return $discussion;
    }
}
