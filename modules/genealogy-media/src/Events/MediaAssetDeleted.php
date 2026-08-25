<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Events;

use Liberu\Genealogy\Media\Models\MediaAsset;

final class MediaAssetDeleted
{
    public bool $afterCommit = true;

    public function __construct(public MediaAsset $asset) {}
}
