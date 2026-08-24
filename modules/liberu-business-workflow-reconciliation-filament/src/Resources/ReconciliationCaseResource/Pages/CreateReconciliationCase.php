<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Filament\Resources\ReconciliationCaseResource\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Liberu\Platform\BusinessWorkflowReconciliation\Filament\Resources\ReconciliationCaseResource;

final class CreateReconciliationCase extends CreateRecord
{
    protected static string $resource = ReconciliationCaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant !== null, 403);

        $data['tenant_id'] = (string) $tenant->getKey();

        return $data;
    }
}
