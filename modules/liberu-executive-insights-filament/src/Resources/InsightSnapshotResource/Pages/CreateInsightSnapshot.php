<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Filament\Resources\InsightSnapshotResource\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Liberu\Platform\ExecutiveInsights\Filament\Resources\InsightSnapshotResource;

final class CreateInsightSnapshot extends CreateRecord
{
    protected static string $resource = InsightSnapshotResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant !== null, 403);

        $data['tenant_id'] = (string) $tenant->getKey();

        return $data;
    }
}
