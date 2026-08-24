<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class InsightSnapshotTransitioned implements ShouldDispatchAfterCommit
{
    public function __construct(public string $recordId, public string $tenantId, public string $from, public string $to) {}
}
