<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Events;

final class PlaceNameDeleted
{
    public function __construct(public string $placeNameId, public string $placeId) {}
}
