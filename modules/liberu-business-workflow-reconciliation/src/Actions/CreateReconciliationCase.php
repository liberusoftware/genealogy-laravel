<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Actions;

use Illuminate\Support\Arr;
use Liberu\Platform\BusinessWorkflowReconciliation\Models\ReconciliationCase;

final class CreateReconciliationCase
{
    public function execute(array $attributes): ReconciliationCase
    {
        return ReconciliationCase::query()->create(Arr::only($attributes, ['tenant_id', 'idempotency_key', 'name', 'status', 'metadata']));
    }
}
