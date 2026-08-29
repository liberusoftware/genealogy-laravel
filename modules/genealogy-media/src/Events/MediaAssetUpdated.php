<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Events;

use Liberu\Genealogy\Media\Models\MediaAsset;

final class MediaAssetUpdated
{
    public function __construct(public MediaAsset $asset) {}
}
