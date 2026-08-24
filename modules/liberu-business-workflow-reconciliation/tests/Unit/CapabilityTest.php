<?php

declare(strict_types=1);

use Liberu\Platform\BusinessWorkflowReconciliation\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('liberu-business-workflow-reconciliation', 'Liberu Business Workflow Reconciliation', ['liberu.business-workflow-reconciliation', 'liberu.business-workflow-reconciliation.lifecycle']);

    expect($capability->name)->toBe('liberu-business-workflow-reconciliation')
        ->and($capability->supports('liberu.business-workflow-reconciliation'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
