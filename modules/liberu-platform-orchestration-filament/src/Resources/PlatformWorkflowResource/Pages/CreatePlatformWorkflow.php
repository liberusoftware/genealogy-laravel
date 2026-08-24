<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Filament\Resources\PlatformWorkflowResource\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Liberu\Platform\PlatformOrchestration\Filament\Resources\PlatformWorkflowResource;

final class CreatePlatformWorkflow extends CreateRecord
{
    protected static string $resource = PlatformWorkflowResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant !== null, 403);

        $data['tenant_id'] = (string) $tenant->getKey();

        return $data;
    }
}
