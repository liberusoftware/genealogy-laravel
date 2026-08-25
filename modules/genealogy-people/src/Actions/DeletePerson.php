<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Events\PersonDeleted;
use Liberu\Genealogy\People\Models\Person;

final class DeletePerson
{
    public function execute(Person $person): void
    {
        if ((string) $person->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The person must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $person->delete());
        event(new PersonDeleted($person));
    }
}
