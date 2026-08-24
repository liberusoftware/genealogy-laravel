<?php

declare(strict_types=1);

it('keeps the livewire adapter as an independent package', function (): void {
    expect('liberusoftware/module-genealogy-places-livewire')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-genealogy-places')->toStartWith('liberusoftware/module-');
});
