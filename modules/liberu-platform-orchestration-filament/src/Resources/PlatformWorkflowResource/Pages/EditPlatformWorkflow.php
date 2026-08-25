<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Filament\Resources\PlatformWorkflowResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Platform\PlatformOrchestration\Filament\Resources\PlatformWorkflowResource;

final class EditPlatformWorkflow extends EditRecord
{
    protected static string $resource = PlatformWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
