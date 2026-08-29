<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Evidence\Models\CitationLink;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateCitationLink
{
    public function execute(CitationLink $link, array $attributes): CitationLink
    {
        if ((string) $link->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The citation link must belong to the active team.');
        }
        $values = Arr::only($attributes, ['group', 'page', 'quality', 'text', 'metadata']);
        if (isset($values['group']) && ! in_array($values['group'], CitationLink::GROUPS, true)) {
            throw new InvalidArgumentException('The citation link group is not supported.');
        }
        if (isset($values['group']) && $values['group'] !== $link->group && CitationLink::query()
            ->where('team_id', $link->team_id)
            ->where('citation_id', $link->citation_id)
            ->where('subject_person_id', $link->subject_person_id)
            ->where('group', $values['group'])
            ->where($link->getKeyName(), '!=', $link->getKey())
            ->exists()) {
            throw new InvalidArgumentException('The citation link already exists for this citation, person, and group.');
        }
        $link->update($values);

        return $link->refresh();
    }
}
