<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Filament\Resources\InsightSnapshotResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Platform\ExecutiveInsights\Filament\Resources\InsightSnapshotResource;

final class EditInsightSnapshot extends EditRecord
{
    protected static string $resource = InsightSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
