<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Filament\Resources\ReconciliationCaseResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Platform\BusinessWorkflowReconciliation\Filament\Resources\ReconciliationCaseResource;

final class ListReconciliationCases extends ListRecords
{
    protected static string $resource = ReconciliationCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
