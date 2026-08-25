<?php

declare(strict_types=1);

it('keeps the api adapter as an independent package', function (): void {
    expect('liberusoftware/module-liberu-revenue-and-care-orchestration-api')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-liberu-revenue-and-care-orchestration')->toStartWith('liberusoftware/module-');
});
