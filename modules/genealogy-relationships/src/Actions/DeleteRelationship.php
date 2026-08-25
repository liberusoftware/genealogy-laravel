<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Relationships\Events\RelationshipDeleted;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class DeleteRelationship
{
    public function execute(Relationship $relationship): void
    {
        if ((string) $relationship->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The relationship must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $relationship->delete());
        event(new RelationshipDeleted($relationship));
    }
}
