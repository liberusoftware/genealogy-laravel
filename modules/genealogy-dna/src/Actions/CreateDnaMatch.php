<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class CreateDnaMatch
{
    public function execute(array $attributes): DnaMatch
    {
        $teamId = app(TeamContext::class)->require();
        $values = Arr::only($attributes, ['kit_id', 'external_id', 'display_name', 'predicted_relationship', 'confidence', 'total_cm', 'shared_segments', 'status', 'is_private', 'notes', 'metadata']);
        if (isset($values['confidence']) && ($values['confidence'] < 0 || $values['confidence'] > 100)) {
            throw ValidationException::withMessages(['confidence' => 'Confidence must be between 0 and 100.']);
        }
        if (isset($values['total_cm']) && $values['total_cm'] < 0) {
            throw ValidationException::withMessages(['total_cm' => 'Shared centimorgans cannot be negative.']);
        }

        $values['team_id'] = $teamId;

        return DnaMatch::query()->firstOrCreate(['kit_id' => $values['kit_id'], 'external_id' => $values['external_id']], $values);
    }
}
