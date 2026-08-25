<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteEvidenceEntity
{
    public function execute(Model $entity): void
    {
        if ((string) $entity->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The evidence entity must belong to the active team.');
        }

        DB::transaction(fn (): mixed => $entity->delete());
    }
}
