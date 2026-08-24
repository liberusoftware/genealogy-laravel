<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Filament\Resources\ReconciliationCaseResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Platform\BusinessWorkflowReconciliation\Filament\Resources\ReconciliationCaseResource;

final class CreateReconciliationCase extends CreateRecord
{
    protected static string $resource = ReconciliationCaseResource::class;
}
