<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Actions;

use Illuminate\Support\Facades\DB;
use Liberu\Platform\RevenueAndCareOrchestration\Enums\LifecycleStatus;
use Liberu\Platform\RevenueAndCareOrchestration\Events\CarePlanTransitioned;
use Liberu\Platform\RevenueAndCareOrchestration\Exceptions\InvalidLifecycleTransition;
use Liberu\Platform\RevenueAndCareOrchestration\Models\CarePlan;

final class TransitionCarePlan
{
    public function execute(CarePlan $record, LifecycleStatus $to): CarePlan
    {
        $from = LifecycleStatus::from((string) $record->status);
        if (! in_array($to, $from->allowedTransitions(), true)) {
            throw InvalidLifecycleTransition::between($from->value, $to->value);
        }
        DB::transaction(function () use ($record, $from, $to): void {
            $record->status = $to->value;
            $record->save();
            event(new CarePlanTransitioned((string) $record->getKey(), (string) $record->tenant_id, $from->value, $to->value));
        });

        return $record->refresh();
    }
}
