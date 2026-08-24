<?php

declare(strict_types=1);

use Liberu\Genealogy\Dna\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-dna', 'Genealogy Dna', ['genealogy.dna', 'genealogy.dna.lifecycle']);

    expect($capability->name)->toBe('genealogy-dna')
        ->and($capability->supports('genealogy.dna'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
