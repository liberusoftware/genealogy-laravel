<?php

declare(strict_types=1);

it('keeps the livewire adapter as an independent package', function (): void {
    expect('liberusoftware/module-genealogy-core-livewire')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-genealogy-core')->toStartWith('liberusoftware/module-');
});
