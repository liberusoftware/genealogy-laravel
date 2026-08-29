<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Events;

use Liberu\Genealogy\Media\Models\MediaLink;

final class MediaLinkCreated
{
    public function __construct(public MediaLink $link) {}
}
