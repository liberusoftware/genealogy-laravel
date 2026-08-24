<?php

declare(strict_types=1);

it('keeps the api adapter as an independent package', function (): void {
    expect('liberusoftware/module-genealogy-discovery-api')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-genealogy-discovery')->toStartWith('liberusoftware/module-');
});
