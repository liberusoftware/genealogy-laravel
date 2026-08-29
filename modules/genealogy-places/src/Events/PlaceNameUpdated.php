<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Events;

use Liberu\Genealogy\Places\Models\PlaceName;

final class PlaceNameUpdated
{
    public function __construct(public PlaceName $placeName) {}
}
