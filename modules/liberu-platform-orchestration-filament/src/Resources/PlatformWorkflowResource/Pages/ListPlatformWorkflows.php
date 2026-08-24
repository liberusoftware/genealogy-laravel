<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Filament\Resources\PlatformWorkflowResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Platform\PlatformOrchestration\Filament\Resources\PlatformWorkflowResource;

final class ListPlatformWorkflows extends ListRecords
{
    protected static string $resource = PlatformWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
