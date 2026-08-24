<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource;

final class EditCarePlan extends EditRecord
{
    protected static string $resource = CarePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
