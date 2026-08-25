<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeletePersonSupportingRecord
{
    public function execute(Model $record): void
    {
        if ((string) $record->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The record must belong to the active team.');
        }

        $record->delete();
    }
}
