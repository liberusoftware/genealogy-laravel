<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource;

final class CreateCarePlan extends CreateRecord
{
    protected static string $resource = CarePlanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant !== null, 403);

        $data['tenant_id'] = (string) $tenant->getKey();

        return $data;
    }
}
