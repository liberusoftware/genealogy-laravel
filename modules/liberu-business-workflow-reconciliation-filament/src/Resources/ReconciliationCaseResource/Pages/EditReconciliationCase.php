<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Filament\Resources\ReconciliationCaseResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Platform\BusinessWorkflowReconciliation\Filament\Resources\ReconciliationCaseResource;

final class EditReconciliationCase extends EditRecord
{
    protected static string $resource = ReconciliationCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
