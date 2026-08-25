<?php

declare(strict_types=1);

it('keeps the filament adapter as an independent package', function (): void {
    expect('liberusoftware/module-genealogy-evidence-filament')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-genealogy-evidence')->toStartWith('liberusoftware/module-');
});
