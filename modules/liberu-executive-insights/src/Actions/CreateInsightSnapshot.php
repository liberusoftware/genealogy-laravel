<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Actions;

use Illuminate\Support\Arr;
use Liberu\Platform\ExecutiveInsights\Models\InsightSnapshot;

final class CreateInsightSnapshot
{
    public function execute(array $attributes): InsightSnapshot
    {
        return InsightSnapshot::query()->create(Arr::only($attributes, ['tenant_id', 'idempotency_key', 'name', 'status', 'metadata']));
    }
}
