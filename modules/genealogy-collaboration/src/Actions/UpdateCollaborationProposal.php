<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateCollaborationProposal
{
    public function execute(CollaborationProposal $proposal, array $attributes): CollaborationProposal
    {
        $this->assertTeam($proposal);
        $proposal->fill(Arr::only($attributes, ['title', 'description', 'metadata']));
        $proposal->title = trim((string) $proposal->title);
        if ($proposal->title === '') {
            throw new InvalidArgumentException('A proposal title is required.');
        }

        DB::transaction(fn (): bool => $proposal->save());

        return $proposal->refresh();
    }

    private function assertTeam(CollaborationProposal $proposal): void
    {
        if ((string) $proposal->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The proposal must belong to the active team.');
        }
    }
}
