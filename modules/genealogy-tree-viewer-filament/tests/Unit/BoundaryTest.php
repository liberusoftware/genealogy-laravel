<?php

declare(strict_types=1);

it('keeps the filament adapter as an independent package', function (): void {
    expect('liberusoftware/module-genealogy-tree-viewer-filament')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-genealogy-tree-viewer')->toStartWith('liberusoftware/module-');
});
