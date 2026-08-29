<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Media\Models\MediaFaceTag;

final class ReviewMediaFaceTag
{
    public function execute(MediaFaceTag $tag, string $status, ?string $personId = null, ?string $actorId = null): MediaFaceTag
    {
        if (! in_array($status, ['confirmed', 'rejected'], true)) {
            throw new InvalidArgumentException('A face tag review must be confirmed or rejected.');
        }

        if (app()->bound(TeamContext::class) && $tag->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The face tag must belong to the active team.');
        }

        $values = ['status' => $status, 'person_id' => $status === 'confirmed' ? $personId : null, 'confirmed_by' => $actorId, 'confirmed_at' => now()];
        $tag->update(Arr::only($values, $tag->getFillable()));

        return $tag->refresh();
    }
}
