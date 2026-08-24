<?php

declare(strict_types=1);

use Liberu\Platform\RevenueAndCareOrchestration\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('liberu-revenue-and-care-orchestration', 'Liberu Revenue And Care Orchestration', ['liberu.revenue-and-care-orchestration', 'liberu.revenue-and-care-orchestration.lifecycle']);

    expect($capability->name)->toBe('liberu-revenue-and-care-orchestration')
        ->and($capability->supports('liberu.revenue-and-care-orchestration'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
