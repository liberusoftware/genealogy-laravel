<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Actions;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdatePersonSupportingRecord
{
    public function execute(Model $record, array $attributes): Model
    {
        if ((string) $record->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The record must belong to the active team.');
        }

        $record->fill(array_intersect_key($attributes, array_flip($record->getFillable())))->save();

        return $record->refresh();
    }
}
