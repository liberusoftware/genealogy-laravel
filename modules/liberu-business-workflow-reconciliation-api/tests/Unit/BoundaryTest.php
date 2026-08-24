<?php

declare(strict_types=1);

it('keeps the api adapter as an independent package', function (): void {
    expect('liberusoftware/module-liberu-business-workflow-reconciliation-api')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-liberu-business-workflow-reconciliation')->toStartWith('liberusoftware/module-');
});
