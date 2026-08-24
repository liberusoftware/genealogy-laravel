<?php

declare(strict_types=1);

use Liberu\Platform\ExecutiveInsights\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('liberu-executive-insights', 'Liberu Executive Insights', ['liberu.executive-insights', 'liberu.executive-insights.lifecycle']);

    expect($capability->name)->toBe('liberu-executive-insights')
        ->and($capability->supports('liberu.executive-insights'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
