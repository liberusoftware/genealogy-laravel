<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Filament\Resources\InsightSnapshotResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Platform\ExecutiveInsights\Filament\Resources\InsightSnapshotResource;

final class CreateInsightSnapshot extends CreateRecord
{
    protected static string $resource = InsightSnapshotResource::class;
}
