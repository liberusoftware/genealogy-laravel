<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Actions;

use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Places\Events\PlaceNameDeleted;
use Liberu\Genealogy\Places\Models\PlaceName;

final class DeletePlaceName
{
    public function execute(PlaceName $placeName): void
    {
        if ((string) $placeName->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The place name must belong to the active team.');
        }
        $id = (string) $placeName->getKey();
        $placeId = (string) $placeName->place_id;
        $placeName->getConnection()->transaction(function () use ($placeName): void {
            $placeName->delete();
        });

        if (app()->bound('events')) {
            event(new PlaceNameDeleted($id, $placeId));
        }
    }
}
