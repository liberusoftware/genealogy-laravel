<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Actions;

use Illuminate\Support\Facades\DB;
use Liberu\Platform\BusinessWorkflowReconciliation\Enums\LifecycleStatus;
use Liberu\Platform\BusinessWorkflowReconciliation\Events\ReconciliationCaseTransitioned;
use Liberu\Platform\BusinessWorkflowReconciliation\Exceptions\InvalidLifecycleTransition;
use Liberu\Platform\BusinessWorkflowReconciliation\Models\ReconciliationCase;

final class TransitionReconciliationCase
{
    public function execute(ReconciliationCase $record, LifecycleStatus $to): ReconciliationCase
    {
        $from = LifecycleStatus::from((string) $record->status);
        if (! in_array($to, $from->allowedTransitions(), true)) {
            throw InvalidLifecycleTransition::between($from->value, $to->value);
        }

        DB::transaction(function () use ($record, $from, $to): void {
            $record->status = $to->value;
            $record->save();
            event(new ReconciliationCaseTransitioned((string) $record->getKey(), (string) $record->tenant_id, $from->value, $to->value));
        });

        return $record->refresh();
    }
}
