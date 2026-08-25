<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource;

final class ListCarePlans extends ListRecords
{
    protected static string $resource = CarePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
