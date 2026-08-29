<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Models\CitationLink;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteCitationLink
{
    public function execute(CitationLink $link): void
    {
        if ((string) $link->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The citation link must belong to the active team.');
        }
        $link->delete();
    }
}
