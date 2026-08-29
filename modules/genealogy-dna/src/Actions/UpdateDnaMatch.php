<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateDnaMatch
{
    public function execute(DnaMatch $match, array $attributes): DnaMatch
    {
        if ((string) $match->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA match must belong to the active team.');
        }
        $values = Arr::only($attributes, ['display_name', 'predicted_relationship', 'confidence', 'status', 'is_private', 'notes', 'metadata']);
        if (isset($values['confidence']) && ($values['confidence'] < 0 || $values['confidence'] > 100)) {
            throw new InvalidArgumentException('DNA match confidence must be between 0 and 100.');
        }
        if (isset($values['status']) && ! in_array($values['status'], DnaMatch::STATUSES, true)) {
            throw new InvalidArgumentException('The selected DNA match status is invalid.');
        }
        DB::transaction(fn (): bool => $match->update($values));

        return $match->refresh();
    }
}
