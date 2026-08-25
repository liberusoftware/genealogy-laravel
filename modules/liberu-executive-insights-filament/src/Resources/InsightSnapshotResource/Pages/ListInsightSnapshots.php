<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Filament\Resources\InsightSnapshotResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Platform\ExecutiveInsights\Filament\Resources\InsightSnapshotResource;

final class ListInsightSnapshots extends ListRecords
{
    protected static string $resource = InsightSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
