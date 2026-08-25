<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaSegment;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateDnaSegment
{
    public function execute(DnaSegment $segment, array $attributes): DnaSegment
    {
        if ((string) $segment->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA segment must belong to the active team.');
        }

        $values = Arr::only($attributes, ['chromosome', 'start_position', 'end_position', 'centimorgans', 'snps', 'side', 'metadata']);
        $start = (int) ($values['start_position'] ?? $segment->start_position);
        $end = (int) ($values['end_position'] ?? $segment->end_position);

        if ($start >= $end) {
            throw ValidationException::withMessages(['end_position' => 'The segment end must be after its start.']);
        }

        DB::transaction(fn (): bool => $segment->update($values));

        return $segment->refresh();
    }
}
