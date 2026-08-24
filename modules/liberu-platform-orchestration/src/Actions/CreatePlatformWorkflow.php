<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Actions;

use Illuminate\Support\Arr;
use Liberu\Platform\PlatformOrchestration\Models\PlatformWorkflow;

final class CreatePlatformWorkflow
{
    public function execute(array $attributes): PlatformWorkflow
    {
        return PlatformWorkflow::query()->create(Arr::only($attributes, ['tenant_id', 'idempotency_key', 'name', 'status', 'metadata']));
    }
}
