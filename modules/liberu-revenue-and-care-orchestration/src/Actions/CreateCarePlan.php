<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Actions;

use Illuminate\Support\Arr;
use Liberu\Platform\RevenueAndCareOrchestration\Models\CarePlan;

final class CreateCarePlan
{
    public function execute(array $attributes): CarePlan
    {
        return CarePlan::query()->create(Arr::only($attributes, ['tenant_id', 'idempotency_key', 'name', 'status', 'metadata']));
    }
}
