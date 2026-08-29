<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationDiscussion;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteCollaborationDiscussion
{
    public function execute(CollaborationDiscussion $discussion): void
    {
        if ((string) $discussion->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The discussion must belong to the active team.');
        }

        $discussion->delete();
        app(RecordCollaborationAttribution::class)->execute('discussion', (string) $discussion->getKey(), 'deleted');
    }
}
