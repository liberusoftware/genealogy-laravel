<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Models\Citation;
use Liberu\Genealogy\Evidence\Models\CitationLink;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\Person;

final class CreateCitationLink
{
    public function execute(array $attributes): CitationLink
    {
        $teamId = app(TeamContext::class)->require();
        $values = Arr::only($attributes, ['citation_id', 'subject_person_id', 'group', 'page', 'quality', 'text', 'metadata']);
        $citation = Citation::query()->where('team_id', $teamId)->findOrFail($values['citation_id'] ?? '');
        Person::query()->where('team_id', $teamId)->findOrFail($values['subject_person_id'] ?? '');
        $values['group'] = $values['group'] ?? 'indi';
        if (! in_array($values['group'], CitationLink::GROUPS, true)) {
            throw new InvalidArgumentException('The citation link group is not supported.');
        }
        if (CitationLink::query()
            ->where('team_id', $teamId)
            ->where('citation_id', $citation->getKey())
            ->where('subject_person_id', $values['subject_person_id'])
            ->where('group', $values['group'])
            ->exists()) {
            throw new InvalidArgumentException('The citation link already exists for this citation, person, and group.');
        }
        $values['team_id'] = $teamId;
        $values['citation_id'] = $citation->getKey();

        return CitationLink::query()->create($values);
    }
}
