<?php

declare(strict_types=1);

use Liberu\Genealogy\ImportExport\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-import-export', 'Genealogy Import Export', ['genealogy.import-export', 'genealogy.import-export.lifecycle']);

    expect($capability->name)->toBe('genealogy-import-export')
        ->and($capability->supports('genealogy.import-export'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
