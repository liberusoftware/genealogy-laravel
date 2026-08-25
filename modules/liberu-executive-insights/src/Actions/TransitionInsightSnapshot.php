<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Actions;

use Illuminate\Support\Facades\DB;
use Liberu\Platform\ExecutiveInsights\Enums\LifecycleStatus;
use Liberu\Platform\ExecutiveInsights\Events\InsightSnapshotTransitioned;
use Liberu\Platform\ExecutiveInsights\Exceptions\InvalidLifecycleTransition;
use Liberu\Platform\ExecutiveInsights\Models\InsightSnapshot;

final class TransitionInsightSnapshot
{
    public function execute(InsightSnapshot $record, LifecycleStatus $to): InsightSnapshot
    {
        $from = LifecycleStatus::from((string) $record->status);
        if (! in_array($to, $from->allowedTransitions(), true)) {
            throw InvalidLifecycleTransition::between($from->value, $to->value);
        }
        DB::transaction(function () use ($record, $from, $to): void {
            $record->status = $to->value;
            $record->save();
            event(new InsightSnapshotTransitioned((string) $record->getKey(), (string) $record->tenant_id, $from->value, $to->value));
        });

        return $record->refresh();
    }
}
