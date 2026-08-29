<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;
use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;
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
        $this->assertReferenceBelongsToTeam(CollaborationSpace::class, $values['space_id'] ?? null, 'space');
        $this->assertReferenceBelongsToTeam(CollaborationProposal::class, $values['proposal_id'] ?? null, 'proposal');

        $discussion = DB::transaction(fn (): CollaborationDiscussion => CollaborationDiscussion::query()->create($values));
        app(RecordCollaborationAttribution::class)->execute('discussion', (string) $discussion->getKey(), 'created', [], $values['author_id'] ?? null);

        return $discussion;
    }

    /** @param class-string<Model> $modelClass */
    private function assertReferenceBelongsToTeam(string $modelClass, mixed $id, string $label): void
    {
        if ($id !== null && ! $modelClass::query()->whereKey($id)->exists()) {
            throw new InvalidArgumentException("The collaboration {$label} must belong to the active team.");
        }
    }
}
