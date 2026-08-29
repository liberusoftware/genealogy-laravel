<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Liberu\Genealogy\Discovery\Events\DiscoveryMatchUpdated;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateDiscoveryMatch
{
    public function execute(DiscoveryMatch $match, array $attributes): DiscoveryMatch
    {
        $this->assertTeam($match);
        $values = Arr::only($attributes, ['kind', 'name', 'subject_id', 'related_id', 'confidence', 'rationale', 'source_type', 'detected_at', 'reviewed_at', 'status', 'metadata']);
        $this->validate($values + $match->toArray());

        DB::transaction(fn (): bool => $match->update($values));
        $match = $match->refresh();
        event(new DiscoveryMatchUpdated($match));

        return $match;
    }

    /** @param array<string, mixed> $values */
    private function validate(array $values): void
    {
        if (isset($values['kind']) && ! in_array($values['kind'], DiscoveryMatch::KINDS, true)) {
            throw ValidationException::withMessages(['kind' => 'The selected discovery kind is invalid.']);
        }
        if (isset($values['status']) && ! in_array($values['status'], DiscoveryMatch::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'The selected discovery status is invalid.']);
        }
        if (isset($values['confidence']) && ($values['confidence'] < 0 || $values['confidence'] > 100)) {
            throw ValidationException::withMessages(['confidence' => 'Confidence must be between 0 and 100.']);
        }
        if (trim((string) ($values['name'] ?? '')) === '') {
            throw ValidationException::withMessages(['name' => 'A discovery match name is required.']);
        }
    }

    private function assertTeam(DiscoveryMatch $match): void
    {
        $schema = $match->getConnection()->getSchemaBuilder();
        if ($schema->hasColumn($match->getTable(), 'team_id') && (string) $match->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The discovery match must belong to the active team.');
        }
    }
}
