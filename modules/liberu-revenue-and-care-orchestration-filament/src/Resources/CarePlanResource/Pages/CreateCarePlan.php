<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Platform\RevenueAndCareOrchestration\Filament\Resources\CarePlanResource;

final class CreateCarePlan extends CreateRecord
{
    protected static string $resource = CarePlanResource::class;
}
