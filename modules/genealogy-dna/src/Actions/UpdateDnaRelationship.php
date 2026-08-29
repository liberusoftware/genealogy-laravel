<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaRelationship;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateDnaRelationship
{
    public function execute(DnaRelationship $relationship, array $attributes): DnaRelationship
    {
        if ((string) $relationship->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA relationship must belong to the active team.');
        }
        $values = Arr::only($attributes, ['relationship_type', 'confidence', 'status', 'rationale', 'metadata']);
        if (isset($values['confidence']) && ($values['confidence'] < 0 || $values['confidence'] > 100)) {
            throw new InvalidArgumentException('DNA relationship confidence must be between 0 and 100.');
        }
        if (isset($values['status']) && ! in_array($values['status'], DnaRelationship::STATUSES, true)) {
            throw new InvalidArgumentException('The DNA relationship status is invalid.');
        }
        DB::transaction(fn (): bool => $relationship->update($values));

        return $relationship->refresh();
    }
}
