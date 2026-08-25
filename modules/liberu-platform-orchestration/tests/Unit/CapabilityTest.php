<?php

declare(strict_types=1);

use Liberu\Platform\PlatformOrchestration\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('liberu-platform-orchestration', 'Liberu Platform Orchestration', ['liberu.platform-orchestration', 'liberu.platform-orchestration.lifecycle']);

    expect($capability->name)->toBe('liberu-platform-orchestration')
        ->and($capability->supports('liberu.platform-orchestration'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
