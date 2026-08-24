<?php

declare(strict_types=1);

it('keeps the livewire adapter as an independent package', function (): void {
    expect('liberusoftware/module-liberu-platform-orchestration-livewire')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-liberu-platform-orchestration')->toStartWith('liberusoftware/module-');
});
