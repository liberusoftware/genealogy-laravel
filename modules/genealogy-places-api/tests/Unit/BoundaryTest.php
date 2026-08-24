<?php

declare(strict_types=1);

it('keeps the api adapter as an independent package', function (): void {
    expect('liberusoftware/module-genealogy-places-api')->toStartWith('liberusoftware/module-')
        ->and('liberusoftware/module-genealogy-places')->toStartWith('liberusoftware/module-');
});
