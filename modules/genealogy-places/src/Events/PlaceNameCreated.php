<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Events;

use Liberu\Genealogy\Places\Models\PlaceName;

final class PlaceNameCreated
{
    public function __construct(public PlaceName $placeName) {}
}
