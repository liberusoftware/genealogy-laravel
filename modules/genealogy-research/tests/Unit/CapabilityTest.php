<?php

declare(strict_types=1);

use Liberu\Genealogy\Research\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-research', 'Genealogy Research', ['genealogy.research', 'genealogy.research.lifecycle']);

    expect($capability->name)->toBe('genealogy-research')
        ->and($capability->supports('genealogy.research'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
