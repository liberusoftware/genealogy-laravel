<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Events;

use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;

final class DiscoveryMatchReviewed
{
    public function __construct(public readonly DiscoveryMatch $match) {}
}
