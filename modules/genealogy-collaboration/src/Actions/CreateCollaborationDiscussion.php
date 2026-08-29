<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationDiscussion;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class CreateCollaborationDiscussion
{
    public function execute(array $attributes): CollaborationDiscussion
    {
        $body = trim((string) ($attributes['body'] ?? ''));
        if ($body === '') {
            throw new InvalidArgumentException('A discussion message is required.');
        }

        $values = Arr::only($attributes, ['space_id', 'proposal_id', 'author_id', 'status', 'metadata']);
        $values['body'] = $body;
        $values['team_id'] = app(TeamContext::class)->require();

        $discussion = DB::transaction(fn (): CollaborationDiscussion => CollaborationDiscussion::query()->create($values));
        app(RecordCollaborationAttribution::class)->execute('discussion', (string) $discussion->getKey(), 'created', [], $values['author_id'] ?? null);

        return $discussion;
    }
}
