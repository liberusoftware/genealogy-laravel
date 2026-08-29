<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteEvidenceRecord
{
    public function execute(EvidenceRecord $record): void
    {
        if ((string) $record->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The evidence record must belong to the active team.');
        }
        $record->delete();
    }
}
