<?php

declare(strict_types=1);

it('keeps the filament adapter as an independent package', function (): void {
    expect('liberusoftware/module-genealogy-reports-filament')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-genealogy-reports')->toStartWith('liberusoftware/module-');
});
