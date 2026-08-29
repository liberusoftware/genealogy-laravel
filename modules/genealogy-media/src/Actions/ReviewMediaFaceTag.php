<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\Contracts\PersonReferenceResolver;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Media\Models\MediaFaceTag;

final class ReviewMediaFaceTag
{
    public function __construct(private readonly ?PersonReferenceResolver $personReference = null) {}

    public function execute(MediaFaceTag $tag, string $status, ?string $personId = null, ?string $actorId = null): MediaFaceTag
    {
        if (! in_array($status, ['confirmed', 'rejected'], true)) {
            throw new InvalidArgumentException('A face tag review must be confirmed or rejected.');
        }

        $teamId = app(TeamContext::class)->require();
        if ((string) $tag->team_id !== $teamId) {
            throw new InvalidArgumentException('The face tag must belong to the active team.');
        }

        if ($personId !== null && $this->personReference !== null && ! $this->personReference->existsForTeam($personId, $teamId)) {
            throw new InvalidArgumentException('The tagged person must belong to the active team.');
        }

        $values = ['status' => $status, 'person_id' => $status === 'confirmed' ? $personId : null, 'confirmed_by' => $actorId, 'confirmed_at' => now()];
        $tag->update(Arr::only($values, $tag->getFillable()));

        return $tag->refresh();
    }
}
