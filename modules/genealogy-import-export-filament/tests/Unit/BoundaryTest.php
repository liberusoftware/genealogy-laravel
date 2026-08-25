<?php

declare(strict_types=1);

it('keeps the filament adapter as an independent package', function (): void {
    expect('liberusoftware/module-genealogy-import-export-filament')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-genealogy-import-export')->toStartWith('liberusoftware/module-');
});
