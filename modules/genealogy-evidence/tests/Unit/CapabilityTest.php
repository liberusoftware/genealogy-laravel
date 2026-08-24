<?php

declare(strict_types=1);

use Liberu\Genealogy\Evidence\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-evidence', 'Genealogy Evidence', ['genealogy.evidence', 'genealogy.evidence.lifecycle']);

    expect($capability->name)->toBe('genealogy-evidence')
        ->and($capability->supports('genealogy.evidence'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
