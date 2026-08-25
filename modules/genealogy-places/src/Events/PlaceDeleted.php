<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Events;

use Liberu\Genealogy\Places\Models\Place;

final class PlaceDeleted
{
    public bool $afterCommit = true;

    public function __construct(public Place $place) {}
}
