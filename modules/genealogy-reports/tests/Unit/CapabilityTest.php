<?php

declare(strict_types=1);

use Liberu\Genealogy\Reports\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-reports', 'Genealogy Reports', ['genealogy.reports', 'genealogy.reports.lifecycle']);

    expect($capability->name)->toBe('genealogy-reports')
        ->and($capability->supports('genealogy.reports'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
