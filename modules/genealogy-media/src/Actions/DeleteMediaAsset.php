<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Media\Events\MediaAssetDeleted;
use Liberu\Genealogy\Media\Models\MediaAsset;

final class DeleteMediaAsset
{
    public function execute(MediaAsset $asset): void
    {
        if ((string) $asset->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The media asset must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $asset->delete());
        event(new MediaAssetDeleted($asset));
    }
}
