<?php

declare(strict_types=1);

it('keeps the livewire adapter as an independent package', function (): void {
    expect('liberusoftware/module-genealogy-media-livewire')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-genealogy-media')->toStartWith('liberusoftware/module-');
});
