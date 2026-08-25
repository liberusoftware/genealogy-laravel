<?php

declare(strict_types=1);

it('keeps the livewire adapter as an independent package', function (): void {
    expect('liberusoftware/module-liberu-executive-insights-livewire')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-liberu-executive-insights')->toStartWith('liberusoftware/module-');
});
