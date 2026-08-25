<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Events\CollaborationProposalCreated;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class CreateCollaborationProposal
{
    public function execute(array $attributes): CollaborationProposal
    {
        $values = Arr::only($attributes, ['proposer_id', 'title', 'description', 'metadata']);
        $values['title'] = trim((string) ($values['title'] ?? ''));
        if ($values['title'] === '') {
            throw new InvalidArgumentException('A proposal title is required.');
        }

        $values['team_id'] = app(TeamContext::class)->require();
        $proposal = DB::transaction(fn (): CollaborationProposal => CollaborationProposal::query()->create($values));
        event(new CollaborationProposalCreated($proposal));
        app(RecordCollaborationAttribution::class)->execute('proposal', (string) $proposal->getKey(), 'created', [], $values['proposer_id'] ?? null);

        return $proposal;
    }
}
