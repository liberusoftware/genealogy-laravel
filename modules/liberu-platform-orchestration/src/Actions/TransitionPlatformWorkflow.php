<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Actions;

use Illuminate\Support\Facades\DB;
use Liberu\Platform\PlatformOrchestration\Enums\LifecycleStatus;
use Liberu\Platform\PlatformOrchestration\Events\PlatformWorkflowTransitioned;
use Liberu\Platform\PlatformOrchestration\Exceptions\InvalidLifecycleTransition;
use Liberu\Platform\PlatformOrchestration\Models\PlatformWorkflow;

final class TransitionPlatformWorkflow
{
    public function execute(PlatformWorkflow $record, LifecycleStatus $to): PlatformWorkflow
    {
        $from = LifecycleStatus::from((string) $record->status);
        if (! in_array($to, $from->allowedTransitions(), true)) {
            throw InvalidLifecycleTransition::between($from->value, $to->value);
        }
        DB::transaction(function () use ($record, $from, $to): void {
            $record->status = $to->value;
            $record->save();
            event(new PlatformWorkflowTransitioned((string) $record->getKey(), (string) $record->tenant_id, $from->value, $to->value));
        });

        return $record->refresh();
    }
}
