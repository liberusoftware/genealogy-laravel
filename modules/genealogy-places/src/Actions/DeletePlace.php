<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Places\Events\PlaceDeleted;
use Liberu\Genealogy\Places\Models\Place;

final class DeletePlace
{
    public function execute(Place $place): void
    {
        if ((string) $place->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The place must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $place->delete());
        event(new PlaceDeleted($place));
    }
}
