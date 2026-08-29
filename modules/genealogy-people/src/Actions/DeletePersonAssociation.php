<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\PersonAssociation;

final class DeletePersonAssociation
{
    public function execute(PersonAssociation $association): void
    {
        if ((string) $association->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The association belongs to another team.');
        }
        $association->delete();
    }
}
