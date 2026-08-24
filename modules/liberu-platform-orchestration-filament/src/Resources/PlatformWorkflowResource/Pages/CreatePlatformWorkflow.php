<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Filament\Resources\PlatformWorkflowResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Platform\PlatformOrchestration\Filament\Resources\PlatformWorkflowResource;

final class CreatePlatformWorkflow extends CreateRecord
{
    protected static string $resource = PlatformWorkflowResource::class;
}
