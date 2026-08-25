<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class CreateDiscoveryMatch
{
    public function execute(array $attributes): DiscoveryMatch
    {
        $values = Arr::only($attributes, ['kind', 'name', 'subject_id', 'related_id', 'confidence', 'rationale', 'source_type', 'detected_at', 'reviewed_at', 'status', 'metadata']);

        if (isset($values['kind']) && ! in_array($values['kind'], DiscoveryMatch::KINDS, true)) {
            throw ValidationException::withMessages(['kind' => 'The selected discovery kind is invalid.']);
        }
        if (isset($values['status']) && ! in_array($values['status'], DiscoveryMatch::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'The selected discovery status is invalid.']);
        }
        if (isset($values['confidence']) && ($values['confidence'] < 0 || $values['confidence'] > 100)) {
            throw ValidationException::withMessages(['confidence' => 'Confidence must be between 0 and 100.']);
        }
        if (DiscoveryMatch::query()->getModel()->getConnection()->getSchemaBuilder()->hasColumn('genealogy_discovery_matches', 'team_id')) {
            $values['team_id'] = app(TeamContext::class)->require();
        }

        return DiscoveryMatch::query()->create($values);
    }
}
